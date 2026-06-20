<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MercadoPagoService;
use App\Services\OrderNotifier;
use Illuminate\Http\Request;

class MercadoPagoWebhookController extends Controller
{
    public function __construct(
        protected MercadoPagoService $mercadoPago,
        protected OrderNotifier $notifier,
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
                    $wasPaid = $order->status === 'pago';

                    $order->update([
                        'payment_method' => 'mercadopago',
                        'payment_status' => $status,
                        'payment_id' => (string) $paymentId,
                        'status' => $status === 'approved' ? 'pago' : $order->status,
                    ]);

                    if ($status === 'approved' && ! $wasPaid) {
                        $this->notifier->confirmCustomer($order);
                    }
                }
            }
        }

        return response()->json(['ok' => true]);
    }
}
