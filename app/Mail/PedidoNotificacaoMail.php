<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\EmailConfig;
use App\Support\LojaEmailConfig;
use App\Support\MidiaPublica;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoNotificacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $evento,
        public ?string $statusAnterior = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $smtp = EmailConfig::smtp();

        $from = null;
        if (filled($smtp['from_address'] ?? null)) {
            $from = new Address($smtp['from_address'], (string) ($smtp['from_name'] ?? config('app.name')));
        }

        return new Envelope(
            from: $from,
            subject: LojaEmailConfig::assuntoRenderizado($this->evento, $this->order, $this->statusAnterior),
        );
    }

    public function content(): Content
    {
        $template = LojaEmailConfig::template($this->evento);

        return new Content(
            view: 'emails.pedido.configuravel',
            with: [
                'corpoHtml' => LojaEmailConfig::conteudoRenderizado($this->evento, $this->order, $this->statusAnterior),
                'imagemUrl' => MidiaPublica::srcEmail(LojaEmailConfig::imagemReferencia($this->evento)),
                'produtosImagens' => $this->imagensProdutosEmail(),
                'botaoTexto' => trim((string) ($template['botao_texto'] ?? '')),
                'botaoUrl' => trim((string) ($template['botao_url'] ?? '')),
                'appName' => (string) config('app.name'),
            ],
        );
    }

    /**
     * @return array<int, array{url: string, nome: string, quantidade: int}>
     */
    protected function imagensProdutosEmail(): array
    {
        if (! LojaEmailConfig::enviarImagemProduto($this->evento)) {
            return [];
        }

        $this->order->loadMissing(['items.product']);
        $imagens = [];

        foreach ($this->order->items as $item) {
            $product = $item->product;
            if ($product === null || blank($product->image)) {
                continue;
            }

            $url = MidiaPublica::srcEmail((string) $product->image);
            if ($url === '') {
                continue;
            }

            $imagens[] = [
                'url' => $url,
                'nome' => (string) $item->product_name,
                'quantidade' => (int) $item->quantity,
            ];
        }

        return $imagens;
    }
}
