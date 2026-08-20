<?php

namespace App\Services;

use App\Models\Order;
use App\Support\LojaRetiradaConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MercadoPagoService
{
    protected ?string $accessToken;

    protected ?string $publicKey;

    protected bool $sandbox;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token') ?: null;
        $this->publicKey = config('services.mercadopago.public_key') ?: null;
        $this->sandbox = (bool) config('services.mercadopago.sandbox', false);
    }

    public function isConfigured(): bool
    {
        return filled($this->accessToken) && filled($this->publicKey);
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function publicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * Checkout Transparente via API Orders (recomendado pelo MP).
     * Payment Brick → POST /v1/orders
     *
     * @param  array<string, mixed>  $formData
     * @return array{
     *   ok: bool,
     *   order?: array<string, mixed>,
     *   status?: string,
     *   status_detail?: string,
     *   payment_id?: string|null,
     *   mp_order_id?: string|null,
     *   pix?: array<string, mixed>|null,
     *   error?: string,
     *   status_code?: int
     * }
     */
    public function createOrder(Order $order, array $formData): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'error' => 'Mercado Pago não configurado.'];
        }

        $amount = number_format((float) $order->total, 2, '.', '');
        $payer = $this->normalizePayer(
            is_array($formData['payer'] ?? null) ? $formData['payer'] : [],
            $order
        );

        if (empty($payer['email'])) {
            return ['ok' => false, 'error' => 'E-mail do pagador é obrigatório.'];
        }

        // Sandbox exige e-mail @testuser.com (mantém o e-mail real só no pedido local).
        if ($this->sandbox) {
            $payer['email'] = $this->sandboxPayerEmail((string) $payer['email']);
        }

        $methodId = (string) ($formData['payment_method_id'] ?? '');
        if ($methodId === '') {
            return ['ok' => false, 'error' => 'Método de pagamento não informado.'];
        }

        $methodType = $this->resolvePaymentMethodType($methodId, $formData);
        $isPix = $methodId === 'pix' || $methodType === 'bank_transfer';

        $paymentMethod = [
            'id' => $methodId,
            'type' => $methodType,
        ];

        if (! empty($formData['token'])) {
            $paymentMethod['token'] = $formData['token'];
            $paymentMethod['installments'] = max(1, (int) ($formData['installments'] ?? 1));
        }

        if (! $isPix) {
            $statement = (string) config('services.mercadopago.statement_descriptor', 'CONVICTOS');
            $statement = mb_substr(preg_replace('/[^A-Za-z0-9 ]/', '', $statement) ?: 'CONVICTOS', 0, 13);
            $paymentMethod['statement_descriptor'] = $statement;
        }

        $payment = [
            'amount' => $amount,
            'payment_method' => $paymentMethod,
        ];

        if ($isPix) {
            $payment['expiration_time'] = 'P1D';
        }

        $payload = [
            'type' => 'online',
            'processing_mode' => 'automatic',
            'capture_mode' => 'automatic',
            'total_amount' => $amount,
            'external_reference' => $order->reference,
            'description' => 'Pedido '.$order->reference.' — Loja Convictos',
            'payer' => $payer,
            'items' => $order->items->map(fn ($item) => [
                'title' => mb_substr($item->product_name.($item->size ? " ({$item->size})" : ''), 0, 150),
                'description' => mb_substr((string) $item->product_name, 0, 150),
                'quantity' => (int) $item->quantity,
                'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
                'external_code' => (string) ($item->product_id ?? $item->id),
            ])->values()->all(),
            'transactions' => [
                'payments' => [$payment],
            ],
        ];

        if ($isPix || $methodType === 'ticket') {
            $payload['shipment'] = ['address' => $this->shipmentAddress()];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->acceptJson()
                ->withHeaders([
                    'X-Idempotency-Key' => (string) Str::uuid(),
                ])
                ->timeout(40)
                ->post('https://api.mercadopago.com/v1/orders', $payload);

            if ($response->failed()) {
                Log::error('MercadoPago order failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'order' => $order->reference,
                ]);

                $message = data_get($response->json(), 'message')
                    ?? data_get($response->json(), 'errors.0.message')
                    ?? data_get($response->json(), 'cause.0.description')
                    ?? 'Não foi possível processar o pagamento.';

                return [
                    'ok' => false,
                    'error' => is_string($message) ? $message : 'Não foi possível processar o pagamento.',
                    'status_code' => $response->status(),
                ];
            }

            /** @var array<string, mixed> $mpOrder */
            $mpOrder = $response->json();

            return array_merge(['ok' => true, 'order' => $mpOrder], $this->normalizeOrderResult($mpOrder));
        } catch (\Throwable $e) {
            Log::error('MercadoPago order exception', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'Erro ao comunicar com o Mercado Pago.'];
        }
    }

    /**
     * Consulta uma order da API Orders.
     *
     * @return array<string, mixed>|null
     */
    public function getOrder(string $orderId): ?array
    {
        if (! filled($this->accessToken)) {
            return null;
        }

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->timeout(30)
            ->get('https://api.mercadopago.com/v1/orders/'.$orderId);

        if (! $response->successful()) {
            Log::warning('MercadoPago getOrder failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Consulta um pagamento legado (API Payments) — mantido para webhooks antigos.
     *
     * @return array<string, mixed>|null
     */
    public function getPayment(string $paymentId): ?array
    {
        if (! filled($this->accessToken)) {
            return null;
        }

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->timeout(30)
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            Log::warning('MercadoPago getPayment failed', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Normaliza resposta da API Orders para o fluxo do checkout.
     *
     * @param  array<string, mixed>  $mpOrder
     * @return array{
     *   status: string,
     *   status_detail: string,
     *   payment_id: string|null,
     *   mp_order_id: string|null,
     *   pix: array<string, mixed>|null
     * }
     */
    public function normalizeOrderResult(array $mpOrder): array
    {
        $payments = data_get($mpOrder, 'transactions.payments');
        if (is_array($payments) && array_is_list($payments)) {
            $payment = $payments[0] ?? [];
        } elseif (is_array($payments)) {
            $payment = $payments;
        } else {
            $payment = [];
        }

        $orderStatus = (string) ($mpOrder['status'] ?? '');
        $orderDetail = (string) ($mpOrder['status_detail'] ?? '');
        $paymentStatus = (string) ($payment['status'] ?? $orderStatus);
        $paymentDetail = (string) ($payment['status_detail'] ?? $orderDetail);

        $status = match (true) {
            $orderStatus === 'processed' && in_array($paymentDetail, ['accredited', 'partially_refunded'], true) => 'approved',
            $orderStatus === 'processed' => 'approved',
            in_array($orderStatus, ['failed', 'cancelled', 'canceled', 'expired'], true) => 'rejected',
            in_array($paymentStatus, ['failed', 'cancelled', 'canceled', 'rejected'], true) => 'rejected',
            str_contains($paymentDetail, 'rejected') || str_contains($orderDetail, 'rejected') => 'rejected',
            $orderStatus === 'action_required' => 'pending',
            $orderStatus === 'processing' => 'pending',
            default => 'pending',
        };

        $method = is_array($payment['payment_method'] ?? null) ? $payment['payment_method'] : [];
        $pix = null;
        if (! empty($method['qr_code']) || ! empty($method['qr_code_base64']) || ! empty($method['ticket_url'])) {
            $pix = [
                'qr_code' => $method['qr_code'] ?? null,
                'qr_code_base64' => $method['qr_code_base64'] ?? null,
                'ticket_url' => $method['ticket_url'] ?? null,
            ];
        }

        return [
            'status' => $status,
            'status_detail' => $paymentDetail !== '' ? $paymentDetail : $orderDetail,
            'payment_id' => isset($payment['id']) ? (string) $payment['id'] : null,
            'mp_order_id' => isset($mpOrder['id']) ? (string) $mpOrder['id'] : null,
            'pix' => $pix,
        ];
    }

    /**
     * Busca detalhes da venda no Mercado Pago (sem dados sensíveis de cartão).
     *
     * @return array<string, mixed>|null
     */
    public function fetchSaleDetails(Order $order): ?array
    {
        $paymentId = (string) ($order->payment_id ?? '');

        if ($paymentId === '') {
            return null;
        }

        if (str_starts_with(strtoupper($paymentId), 'ORD')) {
            $mpOrder = $this->getOrder($paymentId);

            return $mpOrder ? $this->sanitizeOrderForReport($mpOrder) : null;
        }

        // PAY… da API Orders ou ID numérico da API Payments.
        $payment = $this->getPayment($paymentId);
        if ($payment) {
            return $this->sanitizePaymentForReport($payment);
        }

        // Se só temos PAY…, tenta achar a order pelo external_reference (pedido local).
        if (str_starts_with(strtoupper($paymentId), 'PAY') && filled($order->reference)) {
            // Sem endpoint de search simples: retorna null; a UI usa dados locais.
            return null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $mpOrder
     * @return array<string, mixed>
     */
    public function sanitizeOrderForReport(array $mpOrder): array
    {
        $normalized = $this->normalizeOrderResult($mpOrder);
        $payment = data_get($mpOrder, 'transactions.payments.0', []);
        if (! is_array($payment)) {
            $payment = [];
        }
        $method = is_array($payment['payment_method'] ?? null) ? $payment['payment_method'] : [];

        return [
            'source' => 'orders',
            'mp_order_id' => $mpOrder['id'] ?? null,
            'payment_id' => $payment['id'] ?? $normalized['payment_id'],
            'external_reference' => $mpOrder['external_reference'] ?? null,
            'status' => $normalized['status'],
            'status_detail' => $normalized['status_detail'],
            'mp_status' => $mpOrder['status'] ?? null,
            'mp_status_detail' => $mpOrder['status_detail'] ?? null,
            'total_amount' => $mpOrder['total_amount'] ?? $payment['amount'] ?? null,
            'total_paid_amount' => $mpOrder['total_paid_amount'] ?? null,
            'currency' => $mpOrder['currency'] ?? 'BRL',
            'payment_method_id' => $method['id'] ?? null,
            'payment_type' => $method['type'] ?? null,
            'installments' => $method['installments'] ?? null,
            'created_date' => $mpOrder['created_date'] ?? $mpOrder['date_created'] ?? null,
            'last_updated_date' => $mpOrder['last_updated_date'] ?? null,
            'live_mode' => $mpOrder['live_mode'] ?? null,
            'description' => $mpOrder['description'] ?? null,
            'payer_email' => data_get($mpOrder, 'payer.email'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payment
     * @return array<string, mixed>
     */
    public function sanitizePaymentForReport(array $payment): array
    {
        $feeDetails = collect($payment['fee_details'] ?? [])
            ->map(fn ($fee) => [
                'type' => $fee['type'] ?? null,
                'amount' => $fee['amount'] ?? null,
                'fee_payer' => $fee['fee_payer'] ?? null,
            ])
            ->values()
            ->all();

        return [
            'source' => 'payments',
            'mp_order_id' => null,
            'payment_id' => isset($payment['id']) ? (string) $payment['id'] : null,
            'external_reference' => $payment['external_reference'] ?? null,
            'status' => $payment['status'] ?? null,
            'status_detail' => $payment['status_detail'] ?? null,
            'mp_status' => $payment['status'] ?? null,
            'mp_status_detail' => $payment['status_detail'] ?? null,
            'total_amount' => $payment['transaction_amount'] ?? null,
            'total_paid_amount' => $payment['transaction_details']['total_paid_amount'] ?? $payment['transaction_amount'] ?? null,
            'net_received_amount' => $payment['transaction_details']['net_received_amount'] ?? null,
            'currency' => $payment['currency_id'] ?? 'BRL',
            'payment_method_id' => $payment['payment_method_id'] ?? null,
            'payment_type' => $payment['payment_type_id'] ?? null,
            'installments' => $payment['installments'] ?? null,
            'created_date' => $payment['date_created'] ?? null,
            'date_approved' => $payment['date_approved'] ?? null,
            'money_release_date' => $payment['money_release_date'] ?? null,
            'live_mode' => $payment['live_mode'] ?? null,
            'description' => $payment['description'] ?? null,
            'payer_email' => data_get($payment, 'payer.email'),
            'fee_details' => $feeDetails,
        ];
    }

    /**
     * Valida assinatura do webhook (quando MP_WEBHOOK_SECRET estiver configurado).
     * Para topic order, o data.id deve entrar em minúsculas no manifest.
     */
    public function validarAssinaturaWebhook(?string $xSignature, ?string $xRequestId, ?string $dataId): bool
    {
        $secret = (string) config('services.mercadopago.webhook_secret', '');

        if ($secret === '' || ! filled($xSignature)) {
            return true;
        }

        if (! filled($dataId)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $xSignature) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key && $value) {
                $parts[$key] = $value;
            }
        }

        $ts = $parts['ts'] ?? null;
        $hash = $parts['v1'] ?? null;

        if (! $ts || ! $hash) {
            return false;
        }

        $idForManifest = str_starts_with(strtoupper($dataId), 'ORD')
            ? strtolower($dataId)
            : $dataId;

        $manifest = "id:{$idForManifest};request-id:{$xRequestId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expected, $hash);
    }

    /**
     * Converte e-mail real para o formato exigido no sandbox do Mercado Pago.
     */
    protected function sandboxPayerEmail(string $email): string
    {
        $email = strtolower(trim($email));

        if (str_ends_with($email, '@testuser.com')) {
            return $email;
        }

        $local = Str::before($email, '@');
        $local = preg_replace('/[^a-z0-9._-]/', '', $local) ?: 'buyer';

        return $local.'@testuser.com';
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    protected function resolvePaymentMethodType(string $methodId, array $formData): string
    {
        $selected = (string) ($formData['selected_payment_method'] ?? $formData['payment_type_id'] ?? '');

        if ($methodId === 'pix') {
            return 'bank_transfer';
        }

        if (in_array($methodId, ['bolbradesco', 'boleto', 'pec', 'paycash'], true)) {
            return 'ticket';
        }

        if (in_array($selected, ['credit_card', 'debit_card', 'ticket', 'bank_transfer'], true)) {
            return $selected;
        }

        if (! empty($formData['token'])) {
            return 'credit_card';
        }

        return 'bank_transfer';
    }

    /**
     * Endereço de retirada — exigido pela API Orders para Pix/boleto.
     *
     * @return array<string, string>
     */
    protected function shipmentAddress(): array
    {
        return [
            'zip_code' => '72800-000',
            'street_name' => 'Av. Alfredo Nasser',
            'street_number' => '321',
            'neighborhood' => 'Vila Juracy',
            'city' => 'Luziânia',
            'state' => 'GO',
            'complement' => LojaRetiradaConfig::local(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payer
     * @return array<string, mixed>
     */
    protected function normalizePayer(array $payer, Order $order): array
    {
        $payer['email'] = $payer['email'] ?? $order->customer_email;

        $entityType = $payer['entity_type'] ?? $payer['entityType'] ?? 'individual';
        unset($payer['entityType']);
        if (! in_array($entityType, ['individual', 'association'], true)) {
            $entityType = 'individual';
        }
        $payer['entity_type'] = $entityType;

        if (! empty($order->customer_name) && empty($payer['first_name'])) {
            $parts = preg_split('/\s+/', trim($order->customer_name), 2) ?: [];
            $payer['first_name'] = $parts[0] ?? $order->customer_name;
            if (! empty($parts[1])) {
                $payer['last_name'] = $parts[1];
            }
        }

        if (! empty($payer['identification']) && is_array($payer['identification'])) {
            $type = strtoupper((string) ($payer['identification']['type'] ?? ''));
            $number = preg_replace('/\D+/', '', (string) ($payer['identification']['number'] ?? '')) ?: '';
            if ($type !== '' && $number !== '') {
                $payer['identification'] = [
                    'type' => $type,
                    'number' => $number,
                ];
            } else {
                unset($payer['identification']);
            }
        }

        $phone = $this->payerPhone($order->customer_phone);
        if ($phone && empty($payer['phone'])) {
            $payer['phone'] = $phone;
        }

        return array_filter($payer, function ($value) {
            if (is_array($value)) {
                return $value !== [];
            }

            return $value !== null && $value !== '';
        });
    }

    /**
     * @return array{area_code: string, number: string}|null
     */
    protected function payerPhone(?string $phone): ?array
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if (strlen($digits) < 10) {
            return null;
        }

        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            $digits = substr($digits, 2);
        }

        return [
            'area_code' => substr($digits, 0, 2),
            'number' => substr($digits, 2),
        ];
    }
}
