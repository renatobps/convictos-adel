<?php

namespace App\Mail;

use App\Models\Inscricao;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InscricaoRecebida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inscricao $inscricao)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recebemos seu cadastro — Convictos UM 2027',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inscricao.recebida',
        );
    }
}
