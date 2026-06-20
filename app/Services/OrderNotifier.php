<?php

namespace App\Services;

use App\Mail\PedidoAdmin;
use App\Mail\PedidoRecebido;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderNotifier
{
    /**
     * Notifica o administrador sobre um novo pedido.
     */
    public function notifyAdmin(Order $order): void
    {
        $admin = config('services.loja.email_admin');

        if (! $admin) {
            return;
        }

        $this->safe(fn () => Mail::to($admin)->send(new PedidoAdmin($order->loadMissing('items'))));
    }

    /**
     * Envia a confirmação para o cliente (uma única vez).
     */
    public function confirmCustomer(Order $order): void
    {
        $this->safe(fn () => Mail::to($order->customer_email)->send(new PedidoRecebido($order->loadMissing('items'))));
    }

    protected function safe(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail de pedido', ['message' => $e->getMessage()]);
        }
    }
}
