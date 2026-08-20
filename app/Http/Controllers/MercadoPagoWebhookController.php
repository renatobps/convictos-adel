<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(
        protected MercadoPagoService $mercadoPago,
    ) {}

    public function handle(Request $request)
    {
        $type = $request->input('type')
            ?? $request->input('topic')
            ?? $request->query('type')
            ?? $request->query('topic');

        $resourceId = $request->input('data.id')
            ?? $request->input('data_id')
            ?? $request->query('data.id')
            ?? $request->query('id');

        // Formato resource legado: "https://api.mercadopago.com/v1/payments/123"
        if (! $resourceId && is_string($request->input('resource'))) {
            if (preg_match('#/payments/(\d+)#', $request->input('resource'), $m)) {
                $resourceId = $m[1];
                $type = $type ?: 'payment';
            } elseif (preg_match('#/orders/([^/?]+)#', $request->input('resource'), $m)) {
                $resourceId = $m[1];
                $type = $type ?: 'order';
            }
        }

        $action = (string) $request->input('action', '');
        if ($resourceId && (str_starts_with($action, 'order.') || $type === 'order')) {
            $type = 'order';
        } elseif ($resourceId && (str_starts_with($action, 'payment.') || $type === 'payment')) {
            $type = 'payment';
        }

        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        if (filled($xSignature)
            && ! $this->mercadoPago->validarAssinaturaWebhook($xSignature, $xRequestId, $resourceId ? (string) $resourceId : null)
        ) {
            Log::warning('MercadoPago webhook assinatura inválida', [
                'resource_id' => $resourceId,
                'type' => $type,
            ]);

            return response()->json(['ok' => false, 'error' => 'invalid signature'], 401);
        }

        if ($type === 'order' && $resourceId) {
            $mpOrder = $this->mercadoPago->getOrder((string) $resourceId);

            if ($mpOrder && ! empty($mpOrder['external_reference'])) {
                $this->aplicarStatusPedido(
                    (string) $mpOrder['external_reference'],
                    $this->mercadoPago->normalizeOrderResult($mpOrder)
                );
            }

            return response()->json(['ok' => true]);
        }

        if ($type === 'payment' && $resourceId) {
            $payment = $this->mercadoPago->getPayment((string) $resourceId);

            if ($payment && ! empty($payment['external_reference'])) {
                $status = (string) ($payment['status'] ?? 'pending');
                $this->aplicarStatusPedido((string) $payment['external_reference'], [
                    'status' => $status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending'),
                    'payment_id' => (string) $resourceId,
                    'mp_order_id' => null,
                    'status_detail' => (string) ($payment['status_detail'] ?? ''),
                    'pix' => null,
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * @param  array{status: string, payment_id?: string|null, mp_order_id?: string|null, status_detail?: string, pix?: mixed}  $normalized
     */
    protected function aplicarStatusPedido(string $reference, array $normalized): void
    {
        $order = Order::where('reference', $reference)->first();
        if (! $order) {
            return;
        }

        $status = (string) ($normalized['status'] ?? 'pending');
        $paymentId = $normalized['mp_order_id'] ?? $normalized['payment_id'] ?? $order->payment_id;

        $updates = [
            'payment_method' => 'mercadopago',
            'payment_status' => $status,
            'payment_id' => $paymentId ? (string) $paymentId : $order->payment_id,
        ];

        if ($status === 'approved' && ! $order->pagamentoConfirmado()) {
            $updates['status'] = Order::statusPosPagamento();
        }

        $order->update($updates);
    }
}
