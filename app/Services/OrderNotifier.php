<?php

namespace App\Services;

use App\Mail\PedidoNotificacaoMail;
use App\Models\Order;
use App\Support\EmailConfig;
use App\Support\LojaEmailConfig;
use App\Support\LojaNotificacaoConfig;
use App\Support\LojaRetiradaConfig;
use App\Support\MidiaPublica;
use App\Support\NotificacaoHistorico;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderNotifier
{
    public function __construct(
        protected WhatsAppService $whatsApp,
    ) {
    }

    /**
     * Notifica o administrador sobre um novo pedido.
     */
    public function notifyAdmin(Order $order): void
    {
        $order->loadMissing(['items.product']);
        $evento = LojaNotificacaoConfig::ADMIN_NOVO_PEDIDO;

        $admin = config('services.loja.email_admin');
        if ($admin && LojaEmailConfig::templateAtivo($evento)) {
            $this->safe(fn () => $this->enviarEmail($admin, $order, $evento));
        }

        $this->enviarWhatsAppTemplate($order, $evento, (string) config('services.loja.whatsapp', ''));
    }

    /**
     * Notifica o cliente quando o status do pedido é alterado.
     */
    public function notifyStatusChanged(Order $order, string $statusAnterior): void
    {
        $order->loadMissing(['items.product']);

        $evento = LojaNotificacaoConfig::eventoPorStatus($order->status);
        if ($evento === null) {
            return;
        }

        if (LojaEmailConfig::templateAtivo($evento)) {
            $this->safe(fn () => $this->enviarEmail($order->customer_email, $order, $evento, $statusAnterior));
        }

        $this->enviarWhatsAppTemplate($order, $evento, (string) $order->customer_phone, $statusAnterior);
    }

    /**
     * Avisa o cliente assim que o pedido é registrado (antes do pagamento).
     */
    public function notifyCustomerCreated(Order $order): void
    {
        $order->loadMissing(['items.product']);
        $evento = LojaNotificacaoConfig::CLIENTE_PEDIDO_REGISTRADO;

        if (LojaEmailConfig::templateAtivo($evento)) {
            $this->safe(fn () => $this->enviarEmail($order->customer_email, $order, $evento));
        }

        $this->enviarWhatsAppTemplate($order, $evento, (string) $order->customer_phone);
    }

    protected function enviarEmail(string $destino, Order $order, string $evento, ?string $statusAnterior = null): void
    {
        EmailConfig::aplicarMailer();
        Mail::to($destino)->send(new PedidoNotificacaoMail($order, $evento, $statusAnterior));
    }

    protected function enviarWhatsAppTemplate(
        Order $order,
        string $evento,
        string $numeroRaw,
        ?string $statusAnterior = null,
    ): void {
        if ($numeroRaw === '' || ! $this->whatsApp->isConfigured()) {
            return;
        }

        $numero = $this->whatsApp->normalizarNumeroWhatsapp($numeroRaw);
        if ($numero === null) {
            return;
        }

        $mensagem = LojaNotificacaoConfig::mensagemRenderizada($evento, $order, $statusAnterior);
        $bannerUrl = LojaNotificacaoConfig::imagemUrl($evento);

        $this->safeWhatsapp($numero, $mensagem, function () use ($numero, $mensagem, $bannerUrl, $order, $evento): void {
            $this->whatsApp->enviarTexto($numero, $mensagem);

            if ($bannerUrl !== '' && MidiaPublica::urlAcessivelExternamente($bannerUrl)) {
                $this->whatsApp->enviarMidia(
                    $numero,
                    '',
                    MidiaPublica::urlAbsoluta($bannerUrl),
                    'convictos-loja',
                    'image',
                );
            }

            if (LojaNotificacaoConfig::enviarImagemProduto($evento)) {
                $this->enviarImagensProdutoWhatsapp($numero, $order);
            }

            if (LojaNotificacaoConfig::enviarLocalizacao($evento) && LojaRetiradaConfig::localizacaoConfigurada()) {
                $local = LojaRetiradaConfig::localizacao();
                $this->whatsApp->enviarLocalizacao(
                    $numero,
                    $local['name'],
                    $local['address'],
                    $local['latitude'],
                    $local['longitude'],
                );
            }

            NotificacaoHistorico::registrar($numero, $mensagem, 'enviada');
        });
    }

    protected function enviarImagensProdutoWhatsapp(string $numero, Order $order): void
    {
        $order->loadMissing(['items.product']);

        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product === null || blank($product->image)) {
                continue;
            }

            $caption = $item->product_name.' × '.$item->quantity;
            $path = MidiaPublica::caminhoLocal((string) $product->image);

            if ($path !== null) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                $this->whatsApp->tentarEnviarMidiaBase64(
                    $numero,
                    $caption,
                    (string) file_get_contents($path),
                    Str::slug($item->product_name).'.jpg',
                    'image',
                    $mime,
                );

                continue;
            }

            $url = MidiaPublica::urlPublica((string) $product->image);
            if ($url !== '' && MidiaPublica::urlAcessivelExternamente($url)) {
                $this->whatsApp->enviarMidia(
                    $numero,
                    $caption,
                    $url,
                    Str::slug($item->product_name).'.jpg',
                    'image',
                );
            }
        }
    }

    protected function safe(callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail de pedido', ['message' => $e->getMessage()]);
        }
    }

    protected function safeWhatsapp(string $numero, string $mensagem, callable $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar WhatsApp de pedido', [
                'numero' => $numero,
                'message' => $e->getMessage(),
            ]);
            NotificacaoHistorico::registrar($numero, $mensagem, 'erro');
        }
    }
}
