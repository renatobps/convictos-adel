<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(
        protected MercadoPagoService $mercadoPago,
    ) {
    }

    public function handle(Request $request)
    {
        $type = $request->input('type') ?? $request->query('type');
        $paymentId = $request->input('data.id') ?? $request->query('id');

        if ($type === 'payment' && $paymentId) {
            $payment = $this->mercadoPago->getPayment((string) $paymentId);

            if ($payment && ! empty($payment['external_reference'])) {
                $order = Order::where('reference', $payment['external_reference'])->first();

                if ($order) {
                    $status = $payment['status'] ?? null;

                    if ($status === 'approved' && ! $order->pagamentoConfirmado()) {
                        $order->update([
                            'payment_method' => 'mercadopago',
                            'payment_status' => $status,
                            'payment_id' => (string) $paymentId,
                            'status' => Order::statusPosPagamento(),
                        ]);
                    } else {
                        $order->update([
                            'payment_method' => 'mercadopago',
                            'payment_status' => $status,
                            'payment_id' => (string) $paymentId,
                        ]);
                    }
                }
            }
        }

        return response()->json(['ok' => true]);
    }
}
