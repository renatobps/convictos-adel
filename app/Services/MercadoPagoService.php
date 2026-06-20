<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    protected ?string $accessToken;

    protected bool $sandbox;

    public function __construct()
    {
        $this->accessToken = config('services.mercadopago.access_token');
        $this->sandbox = (bool) config('services.mercadopago.sandbox', true);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->accessToken);
    }

    /**
     * Cria uma preferência de pagamento e retorna a URL de checkout (init_point).
     */
    public function createPreference(Order $order): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $items = $order->items->map(fn ($item) => [
            'title' => $item->product_name . ($item->size ? " ({$item->size})" : ''),
            'quantity' => (int) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'currency_id' => 'BRL',
        ])->values()->all();

        $payload = [
            'items' => $items,
            'external_reference' => $order->reference,
            'payer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
            ],
            'back_urls' => [
                'success' => route('checkout.success'),
                'failure' => route('checkout.failure'),
                'pending' => route('checkout.pending'),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
            'statement_descriptor' => 'CONVICTOS',
        ];

        try {
            $response = Http::withToken($this->accessToken)
                ->acceptJson()
                ->post('https://api.mercadopago.com/checkout/preferences', $payload);

            if ($response->failed()) {
                Log::error('MercadoPago preference failed', ['body' => $response->body()]);

                return null;
            }

            $data = $response->json();

            $order->update([
                'payment_method' => 'mercadopago',
                'payment_status' => 'pending',
                'payment_id' => $data['id'] ?? null,
            ]);

            return $this->sandbox
                ? ($data['sandbox_init_point'] ?? $data['init_point'] ?? null)
                : ($data['init_point'] ?? null);
        } catch (\Throwable $e) {
            Log::error('MercadoPago exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Consulta um pagamento por ID na API do MercadoPago.
     */
    public function getPayment(string $paymentId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        return $response->successful() ? $response->json() : null;
    }
}
