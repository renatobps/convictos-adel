<?php

namespace App\Mail;

use App\Models\Inscricao;
use App\Services\ComprovanteService;
use App\Support\EmailConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InscricaoStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inscricao $inscricao,
        public string $tipo,
    ) {}

    public function envelope(): Envelope
    {
        $template = EmailConfig::template($this->tipo);
        $smtp = EmailConfig::smtp();

        $from = null;
        if (filled($smtp['from_address'] ?? null)) {
            $from = new Address($smtp['from_address'], (string) ($smtp['from_name'] ?? config('app.name')));
        }

        return new Envelope(
            from: $from,
            subject: EmailConfig::substituirPlaceholders((string) $template['assunto'], $this->inscricao),
        );
    }

    public function content(): Content
    {
        $template = EmailConfig::template($this->tipo);

        return new Content(
            view: 'emails.inscricao.configuravel',
            with: [
                'corpoHtml' => EmailConfig::substituirPlaceholders((string) $template['conteudo'], $this->inscricao),
                'imagemUrl' => EmailConfig::imagemUrl($this->tipo),
                'botaoTexto' => trim((string) ($template['botao_texto'] ?? '')),
                'botaoUrl' => trim((string) ($template['botao_url'] ?? '')),
                'appName' => (string) config('app.name'),
                'codigo' => (string) $this->inscricao->codigo,
                'qrUrl' => filled($this->inscricao->codigo) ? route('ingresso.qr', ['inscricao' => $this->inscricao->codigo]) : '',
                'ingressoUrl' => filled($this->inscricao->codigo) ? $this->inscricao->urlIngresso() : '',
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (blank($this->inscricao->codigo)) {
            return [];
        }

        $comprovante = app(ComprovanteService::class);

        return [
            Attachment::fromData(
                fn (): string => $comprovante->pdfBytes($this->inscricao),
                $comprovante->nomeArquivo($this->inscricao),
            )->withMime('application/pdf'),
        ];
    }
}
