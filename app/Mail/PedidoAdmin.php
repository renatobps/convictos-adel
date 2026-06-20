<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo pedido ' . $this->order->reference . ' (R$ ' . number_format((float) $this->order->total, 2, ',', '.') . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pedido.admin',
        );
    }
}
