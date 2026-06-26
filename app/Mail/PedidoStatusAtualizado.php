<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoStatusAtualizado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $statusAnterior,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pedido '.$this->order->reference.' — '.$this->order->status_label,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pedido.status-atualizado',
        );
    }
}
