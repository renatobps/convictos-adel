<?php

namespace App\Services;

use App\Models\Inscricao;
use App\Support\NotificacaoHistorico;
use App\Support\NotificacaoPosInscricaoConfig;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function enviarPosInscricao(Inscricao $inscricao): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $numero = $this->normalizarNumeroWhatsapp((string) $inscricao->whatsapp);
        if ($numero === null) {
            return;
        }

        $mensagem = $this->substituirPlaceholders(
            NotificacaoPosInscricaoConfig::mensagemPosInscricao(),
            $inscricao
        );

        $mensagem = $this->anexarComprovante($mensagem, $inscricao);

        if (filled($inscricao->codigo)) {
            if ($this->enviarComprovanteMidias($numero, $inscricao, $mensagem)) {
                NotificacaoHistorico::registrar($numero, $mensagem, 'enviada');

                return;
            }

            Log::warning('Falha no envio do comprovante (QR/PDF) pós-inscrição.', [
                'inscricao_id' => $inscricao->id,
            ]);
        }

        $imagemUrl = NotificacaoPosInscricaoConfig::imagemPosInscricaoUrl();
        if ($imagemUrl !== '') {
            $ok = $this->enviarMidia($numero, $mensagem, $imagemUrl, 'convictos', 'image');
            if ($ok) {
                NotificacaoHistorico::registrar($numero, $mensagem, 'enviada');

                return;
            }

            Log::warning('Fallback para texto após falha no envio de mídia pós-inscrição.', [
                'inscricao_id' => $inscricao->id,
            ]);
        }

        $ok = $this->enviarTexto($numero, $mensagem);
        NotificacaoHistorico::registrar($numero, $mensagem, $ok ? 'enviada' : 'erro');
    }

    /**
     * Reenvia o comprovante (QR Code + PDF) para o WhatsApp do inscrito.
     *
     * @return array{ok: bool, erro: ?string}
     */
    public function reenviarComprovante(Inscricao $inscricao): array
    {
        if ($msg = $this->obterMensagemSeDesconectado()) {
            return ['ok' => false, 'erro' => $msg];
        }

        $numero = $this->normalizarNumeroWhatsapp((string) $inscricao->whatsapp);
        if ($numero === null) {
            return ['ok' => false, 'erro' => 'Número de WhatsApp inválido.'];
        }

        if (blank($inscricao->codigo)) {
            return ['ok' => false, 'erro' => 'Inscrição sem código de ingresso.'];
        }

        $mensagem = $this->anexarComprovante(
            $this->substituirPlaceholders(NotificacaoPosInscricaoConfig::mensagemPosInscricao(), $inscricao),
            $inscricao
        );

        if ($this->enviarComprovanteMidias($numero, $inscricao, $mensagem)) {
            NotificacaoHistorico::registrar($numero, $mensagem, 'enviada');

            return ['ok' => true, 'erro' => null];
        }

        return ['ok' => false, 'erro' => $this->obterUltimoErro() ?: 'Falha ao enviar comprovante.'];
    }

    /**
     * Envia o comprovante (QR Code em imagem + PDF em documento) via base64.
     */
    private function enviarComprovanteMidias(string $numero, Inscricao $inscricao, string $mensagem): bool
    {
        $enviou = false;

        try {
            $qrBytes = app(QrCodeService::class)->pngBytes($inscricao->qrConteudo(), 320);
            if ($this->tentarEnviarMidiaBase64($numero, $mensagem, $qrBytes, 'comprovante-'.$inscricao->codigo.'.png', 'image', 'image/png')['ok']) {
                $enviou = true;
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao gerar/enviar QR do comprovante.', ['message' => $e->getMessage()]);
        }

        try {
            $pdfBytes = app(ComprovanteService::class)->pdfBytes($inscricao);
            if ($this->tentarEnviarMidiaBase64($numero, 'Comprovante de inscrição — '.$inscricao->codigo, $pdfBytes, 'comprovante-'.$inscricao->codigo.'.pdf', 'document', 'application/pdf')['ok']) {
                $enviou = true;
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao gerar/enviar PDF do comprovante.', ['message' => $e->getMessage()]);
        }

        return $enviou;
    }

    /**
     * Acrescenta o bloco de comprovante (código + link do ingresso) à mensagem,
     * caso o template ainda não inclua o código.
     */
    private function anexarComprovante(string $mensagem, Inscricao $inscricao): string
    {
        if (blank($inscricao->codigo)) {
            return $mensagem;
        }

        if (str_contains($mensagem, (string) $inscricao->codigo)) {
            return $mensagem;
        }

        return rtrim($mensagem)
            ."\n\n🎟️ *Comprovante de inscrição*"
            ."\n*Código:* {$inscricao->codigo}"
            ."\n*Ingresso digital:* ".$inscricao->urlIngresso();
    }

    public function enviarConfirmacao(Inscricao $inscricao): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $numero = $this->normalizarNumeroWhatsapp((string) $inscricao->whatsapp);
        if ($numero === null) {
            return;
        }

        $mensagem = $this->substituirPlaceholders(
            NotificacaoPosInscricaoConfig::mensagemConfirmada(),
            $inscricao
        );

        $ok = $this->enviarTexto($numero, $mensagem);
        NotificacaoHistorico::registrar($numero, $mensagem, $ok ? 'enviada' : 'erro');
    }

    private ?string $ultimoErro = null;

    public function obterUltimoErro(): ?string
    {
        return $this->ultimoErro;
    }

    public function obterMensagemSeDesconectado(): ?string
    {
        if (! $this->isConfigured()) {
            return 'WhatsApp não configurado. Verifique WHATSAPP_API_URL, WHATSAPP_API_KEY e WHATSAPP_INSTANCE_NAME no .env.';
        }

        $status = $this->obterStatusInstancia('status');
        if (! $this->instanciaConectada($status['status'] ?? null, $status['instanceInfo'] ?? null)) {
            return 'WhatsApp desconectado. Acesse Notificações → Configuração WPP, obtenha o QR Code e escaneie para reconectar.';
        }

        return null;
    }

    /**
     * @return array{ok: bool, erro: ?string}
     */
    public function tentarEnviarTexto(string $numero, string $mensagem): array
    {
        $this->ultimoErro = null;

        if ($msg = $this->obterMensagemSeDesconectado()) {
            $this->ultimoErro = $msg;

            return ['ok' => false, 'erro' => $msg];
        }

        $number = $this->normalizarNumeroWhatsapp($numero);
        if ($number === null) {
            $this->ultimoErro = 'Número inválido. Use DDD + número (11 dígitos), ex: 61993640457.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        $endpoint = $this->resolveEndpoint((string) $this->cfg('text_endpoint'));
        $response = $this->post($endpoint, [
            'number' => $number,
            'text' => $mensagem,
            'delay' => 500,
        ]);

        if ($response->successful()) {
            return ['ok' => true, 'erro' => null];
        }

        $this->ultimoErro = $this->extrairErroApi($response);

        Log::warning('Falha ao enviar texto via Evolution API.', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return ['ok' => false, 'erro' => $this->ultimoErro];
    }

    public function enviarTexto(string $numero, string $mensagem): bool
    {
        return $this->tentarEnviarTexto($numero, $mensagem)['ok'];
    }

    /**
     * Envia localização via Evolution API sendLocation.
     *
     * @return array{ok: bool, erro: ?string}
     */
    public function tentarEnviarLocalizacao(
        string $numero,
        string $name,
        string $address,
        float $latitude,
        float $longitude,
    ): array {
        $this->ultimoErro = null;

        if ($msg = $this->obterMensagemSeDesconectado()) {
            $this->ultimoErro = $msg;

            return ['ok' => false, 'erro' => $msg];
        }

        $number = $this->normalizarNumeroWhatsapp($numero);
        if ($number === null) {
            $this->ultimoErro = 'Número inválido. Use DDD + número (11 dígitos), ex: 61993640457.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        $endpoint = $this->resolveEndpoint((string) $this->cfg('location_endpoint'));
        $response = $this->post($endpoint, [
            'number' => $number,
            'name' => mb_substr(trim($name) !== '' ? $name : 'Local de retirada', 0, 120),
            'address' => mb_substr(trim($address) !== '' ? $address : 'Endereço não informado', 0, 240),
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        if ($response->successful()) {
            return ['ok' => true, 'erro' => null];
        }

        $this->ultimoErro = $this->extrairErroApi($response);

        Log::warning('Falha ao enviar localização via Evolution API.', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return ['ok' => false, 'erro' => $this->ultimoErro];
    }

    public function enviarLocalizacao(
        string $numero,
        string $name,
        string $address,
        float $latitude,
        float $longitude,
    ): bool {
        return $this->tentarEnviarLocalizacao($numero, $name, $address, $latitude, $longitude)['ok'];
    }

    /**
     * Envia enquete com botões clicáveis (Evolution API sendButtons).
     * WhatsApp permite no máximo 3 botões.
     *
     * @param  array<int, string|array<string, mixed>>  $opcoes
     * @return array{ok: bool, erro: ?string}
     */
    public function tentarEnviarEnqueteBotoes(
        string $numero,
        string $titulo,
        string $descricao,
        array $opcoes,
        ?int $enqueteId = null,
    ): array {
        $this->ultimoErro = null;

        if ($msg = $this->obterMensagemSeDesconectado()) {
            $this->ultimoErro = $msg;

            return ['ok' => false, 'erro' => $msg];
        }

        $number = $this->normalizarNumeroWhatsapp($numero);
        if ($number === null) {
            $this->ultimoErro = 'Número inválido. Use DDD + número (11 dígitos), ex: 61993640457.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        $botoes = [];
        foreach ($opcoes as $opcao) {
            $label = is_string($opcao)
                ? $opcao
                : (string) ($opcao['name'] ?? $opcao['label'] ?? $opcao['text'] ?? reset($opcao));
            $label = mb_substr(trim($label), 0, 20);

            if ($label === '') {
                continue;
            }

            $indice = count($botoes) + 1;

            $botoes[] = [
                'type' => 'reply',
                'displayText' => $label,
                'id' => $enqueteId !== null
                    ? "enq_{$enqueteId}_{$indice}"
                    : (string) $indice,
            ];
        }

        if (count($botoes) < 2) {
            $this->ultimoErro = 'Enquetes com botões requerem pelo menos 2 opções.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        if (count($botoes) > 3) {
            $botoes = array_slice($botoes, 0, 3);
        }

        $endpoint = $this->resolveEndpoint((string) $this->cfg('buttons_endpoint'));
        $payload = [
            'number' => $number,
            'title' => mb_substr(trim($titulo) !== '' ? $titulo : 'Enquete', 0, 30),
            'description' => mb_substr(trim($descricao) !== '' ? $descricao : 'Selecione uma opção:', 0, 120),
            'footer' => mb_substr((string) ($this->cfg('enquete_footer') ?: 'CONVICTOS UM 2027'), 0, 60),
            'buttons' => $botoes,
        ];

        $response = $this->post($endpoint, $payload);
        $body = $response->json();

        if ($response->successful() && empty(data_get($body, 'error'))) {
            return ['ok' => true, 'erro' => null];
        }

        $this->ultimoErro = $this->extrairErroApi($response);

        Log::warning('Falha ao enviar enquete com botões via Evolution API.', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return ['ok' => false, 'erro' => $this->ultimoErro];
    }

    public function enviarEnqueteBotoes(
        string $numero,
        string $titulo,
        string $descricao,
        array $opcoes,
        ?int $enqueteId = null,
    ): bool {
        return $this->tentarEnviarEnqueteBotoes($numero, $titulo, $descricao, $opcoes, $enqueteId)['ok'];
    }

    public function enviarMidia(
        string $numero,
        string $caption,
        string $url,
        string $filename = 'arquivo',
        string $mediatype = 'image'
    ): bool {
        return $this->tentarEnviarMidiaUrl($numero, $caption, $url, $filename, $mediatype)['ok'];
    }

    /**
     * Envia mídia por URL pública (Evolution API baixa o arquivo).
     *
     * @return array{ok: bool, erro: ?string}
     */
    public function tentarEnviarMidiaUrl(
        string $numero,
        string $caption,
        string $url,
        string $filename = 'arquivo',
        string $mediatype = 'image',
    ): array {
        $this->ultimoErro = null;

        if ($msg = $this->obterMensagemSeDesconectado()) {
            $this->ultimoErro = $msg;

            return ['ok' => false, 'erro' => $msg];
        }

        $number = $this->normalizarNumeroWhatsapp($numero);
        if ($number === null) {
            $this->ultimoErro = 'Número inválido. Use DDD + número (11 dígitos), ex: 61993640457.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        $endpoint = $this->resolveEndpoint((string) $this->cfg('media_endpoint'));
        $payload = [
            'number' => $number,
            'mediatype' => $mediatype,
            'mimetype' => $this->guessMimeFromMediatype($mediatype, $url),
            'caption' => $caption,
            'media' => $url,
            'fileName' => $filename,
        ];

        $response = $this->post($endpoint, $payload);
        $body = $response->json();

        if ($response->successful() && empty(data_get($body, 'error'))) {
            return ['ok' => true, 'erro' => null];
        }

        $this->ultimoErro = $this->extrairErroApi($response);

        Log::warning('Falha ao enviar mídia via URL na Evolution API.', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return ['ok' => false, 'erro' => $this->ultimoErro];
    }

    /**
     * Envia mídia como base64 (conteúdo embutido). Não depende de URL pública —
     * ideal quando a Evolution API não consegue acessar o endereço do sistema.
     *
     * @return array{ok: bool, erro: ?string}
     */
    public function tentarEnviarMidiaBase64(
        string $numero,
        string $caption,
        string $conteudo,
        string $filename,
        string $mediatype,
        string $mimetype,
    ): array {
        $this->ultimoErro = null;

        if ($msg = $this->obterMensagemSeDesconectado()) {
            $this->ultimoErro = $msg;

            return ['ok' => false, 'erro' => $msg];
        }

        $number = $this->normalizarNumeroWhatsapp($numero);
        if ($number === null) {
            $this->ultimoErro = 'Número inválido. Use DDD + número (11 dígitos), ex: 61993640457.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        $endpoint = $this->resolveEndpoint((string) $this->cfg('media_endpoint'));
        $payload = [
            'number' => $number,
            'mediatype' => $mediatype,
            'mimetype' => $mimetype,
            'caption' => $caption,
            'media' => base64_encode($conteudo),
            'fileName' => $filename,
        ];

        $response = Http::withHeaders($this->headers())
            ->timeout(120)
            ->post($this->url($endpoint), $payload);

        $body = $response->json();

        if ($response->successful() && empty(data_get($body, 'error'))) {
            return ['ok' => true, 'erro' => null];
        }

        $this->ultimoErro = $this->extrairErroApi($response);

        Log::warning('Falha ao enviar mídia via base64 na Evolution API.', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return ['ok' => false, 'erro' => $this->ultimoErro];
    }

    /**
     * Envia arquivo de mídia diretamente (multipart), como no ADELSS.
     * Funciona em localhost — não depende de URL pública.
     *
     * @return array{ok: bool, erro: ?string}
     */
    public function tentarEnviarMidiaArquivo(
        string $numero,
        UploadedFile $arquivo,
        string $caption = '',
        ?string $mediatype = null,
    ): array {
        $this->ultimoErro = null;

        if ($msg = $this->obterMensagemSeDesconectado()) {
            $this->ultimoErro = $msg;

            return ['ok' => false, 'erro' => $msg];
        }

        $number = $this->normalizarNumeroWhatsapp($numero);
        if ($number === null) {
            $this->ultimoErro = 'Número inválido. Use DDD + número (11 dígitos), ex: 61993640457.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        $originalName = $arquivo->getClientOriginalName() ?: 'arquivo';
        $mediatype ??= $this->detectMediaType($arquivo->getMimeType(), $originalName);

        $path = $arquivo->getRealPath();
        if ($path === false || ! is_readable($path)) {
            $this->ultimoErro = 'Não foi possível ler o arquivo anexo.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        // Formato Evolution API (multipart): file + number + mediatype
        // Ref: POST /message/sendMedia/{instance} --form file --form number --form mediatype
        $payload = [
            'number' => $number,
            'mediatype' => $mediatype,
        ];

        if (trim($caption) !== '') {
            $payload['caption'] = $caption;
        }

        if ($mediatype === 'document') {
            $payload['fileName'] = $originalName ?: 'arquivo.pdf';
            $payload['mimetype'] = $arquivo->getMimeType() ?: 'application/pdf';
        }

        $endpoint = $this->resolveEndpoint((string) $this->cfg('media_endpoint'));
        $response = Http::withHeaders($this->headersMultipart())
            ->timeout(120)
            ->attach('file', fopen($path, 'r'), $originalName)
            ->post($this->url($endpoint), $payload);

        $body = $response->json();

        if ($response->successful() && empty(data_get($body, 'error'))) {
            return ['ok' => true, 'erro' => null];
        }

        $this->ultimoErro = $this->extrairErroApi($response);

        Log::warning('Falha ao enviar mídia via Evolution API.', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return ['ok' => false, 'erro' => $this->ultimoErro];
    }

    /**
     * @return array{status: mixed, qrCode: string, pairingCode: string, qrMensagem: string, instanceInfo: mixed, erros: array<int, string>}
     */
    public function obterStatusInstancia(string $action = 'all'): array
    {
        $instance = $this->instanceName();
        $status = null;
        $qrCode = '';
        $pairingCode = '';
        $qrMensagem = '';
        $instanceInfo = null;
        $erros = [];

        if (! $this->isConfigured()) {
            $erros[] = 'Configure WHATSAPP_API_URL, WHATSAPP_INSTANCE_NAME e WHATSAPP_API_KEY no .env.';

            return compact('status', 'qrCode', 'instanceInfo', 'erros');
        }

        if (in_array($action, ['status', 'qr', 'all'], true)) {
            $statusResponse = $this->get("/instance/connectionState/{$instance}");
            if ($statusResponse !== null && $statusResponse->ok()) {
                $status = $statusResponse->json();
            } elseif (in_array($action, ['status', 'all'], true)) {
                $erros[] = 'Não foi possível obter o status da instância.';
            }
        }

        $allResponse = $this->get('/instance/fetchInstances');
        if ($allResponse !== null && $allResponse->ok()) {
            $instancias = collect($allResponse->json());
            $instanceInfo = $instancias->first(
                fn (mixed $item) => (string) data_get($item, 'name') === $instance
                    || (string) data_get($item, 'instanceName') === $instance
            );
        } elseif ($status === null) {
            $erros[] = 'Não foi possível listar as instâncias.';
        }

        if (in_array($action, ['qr', 'all'], true)) {
            $parsed = $this->solicitarQrCode($instance);

            if ($parsed['qrCode'] === '' && ! $this->instanciaConectada($status, $instanceInfo)) {
                usleep(2_000_000);
                $parsed = $this->solicitarQrCode($instance);
            }

            $qrCode = $parsed['qrCode'];
            $pairingCode = $parsed['pairingCode'];
            $qrMensagem = $parsed['mensagem'];

            if ($qrCode === '' && $qrMensagem === '' && ! $this->instanciaConectada($status, $instanceInfo)) {
                $erros[] = 'QR Code indisponível. Tente "Desconectar e gerar QR" ou aguarde alguns segundos.';
            } elseif ($qrMensagem !== '' && $qrCode === '') {
                // Instância já conectada — mensagem amigável, não é erro.
            }
        }

        return compact('status', 'qrCode', 'pairingCode', 'qrMensagem', 'instanceInfo', 'erros');
    }

    public function desconectarInstancia(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $instance = $this->instanceName();
        $response = Http::withHeaders($this->headers())
            ->timeout(20)
            ->delete($this->url("/instance/logout/{$instance}"));

        return $response->successful();
    }

    public function configurarWebhook(?string $url = null): bool
    {
        $url ??= (string) config('services.evolution_api.webhook_url', '');

        if ($url === '' || ! $this->isConfigured()) {
            return false;
        }

        $instance = $this->instanceName();
        $payloads = [
            [
                'webhook' => [
                    'enabled' => true,
                    'url' => $url,
                    'webhookByEvents' => false,
                    'webhookBase64' => false,
                    'events' => ['MESSAGES_UPSERT'],
                ],
            ],
            [
                'enabled' => true,
                'url' => $url,
                'webhookByEvents' => false,
                'events' => ['MESSAGES_UPSERT'],
            ],
        ];

        foreach ($payloads as $payload) {
            $response = Http::withHeaders($this->headers())
                ->timeout(20)
                ->post($this->url("/webhook/set/{$instance}"), $payload);

            if ($response->successful()) {
                return true;
            }
        }

        Log::warning('Falha ao configurar webhook na Evolution API.', [
            'instance' => $instance,
            'url' => $url,
        ]);

        return false;
    }

    /**
     * @return array{qrCode: string, pairingCode: string, mensagem: string}
     */
    private function solicitarQrCode(string $instance): array
    {
        $response = $this->get("/instance/connect/{$instance}");

        if ($response === null || ! $response->ok()) {
            return ['qrCode' => '', 'pairingCode' => '', 'mensagem' => ''];
        }

        return $this->parseConnectResponse($response);
    }

    /**
     * @return array{qrCode: string, pairingCode: string, mensagem: string}
     */
    private function parseConnectResponse(Response $response): array
    {
        $json = $response->json();

        if (is_array($json) && isset($json[0]) && is_array($json[0])) {
            $json = $json[0];
        }

        $pairingCode = trim((string) (data_get($json, 'pairingCode') ?? ''));
        $state = strtolower((string) (
            data_get($json, 'instance.state')
            ?? data_get($json, 'instance.status')
            ?? data_get($json, 'state')
            ?? ''
        ));

        $qrCode = '';

        foreach ([
            data_get($json, 'base64'),
            data_get($json, 'qrcode.base64'),
            data_get($json, 'instance.qrCode.base64'),
            data_get($json, 'instance.qrcode.base64'),
        ] as $candidate) {
            if (! is_string($candidate) || $candidate === '') {
                continue;
            }

            $normalized = $this->normalizeBase64Image($candidate);
            if ($normalized !== '') {
                $qrCode = $normalized;
                break;
            }
        }

        if ($qrCode === '') {
            $rawCode = trim((string) (data_get($json, 'code') ?? data_get($json, 'instance.qrCode.code') ?? ''));
            if ($rawCode !== '' && ! str_starts_with($rawCode, 'data:')) {
                $qrCode = 'https://quickchart.io/qr?size=260&text='.rawurlencode($rawCode);
            }
        }

        $mensagem = '';
        if ($qrCode === '' && in_array($state, ['open', 'connected'], true)) {
            $mensagem = 'WhatsApp já conectado. Para escanear um novo QR, use "Desconectar e gerar QR".';
        } elseif ($qrCode === '' && $state === 'connecting') {
            $mensagem = 'Aguardando QR Code… clique novamente em "Obter QR Code".';
        }

        return compact('qrCode', 'pairingCode', 'mensagem');
    }

    private function normalizeBase64Image(string $value): string
    {
        if (str_starts_with($value, 'data:image')) {
            return $value;
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Evita tratar o payload bruto do WhatsApp (2@…) como base64.
        if (str_starts_with($value, '2@') || strlen($value) > 512) {
            return 'https://quickchart.io/qr?size=260&text='.rawurlencode($value);
        }

        return 'data:image/png;base64,'.$value;
    }

    public function instanciaConectada(mixed $status = null, mixed $instanceInfo = null): bool
    {
        $state = strtolower((string) (
            data_get($status, 'instance.state')
            ?? data_get($status, 'state')
            ?? data_get($instanceInfo, 'connectionStatus')
            ?? ''
        ));

        return in_array($state, ['open', 'connected'], true);
    }

    public function nomeInstanciaConfigurada(): string
    {
        return $this->instanceName();
    }

    /**
     * @return array{nome: string, perfil: ?string, numero: ?string}
     */
    public function obterDadosInstancia(mixed $status = null, mixed $instanceInfo = null): array
    {
        $nome = trim((string) (
            data_get($instanceInfo, 'name')
            ?? data_get($instanceInfo, 'instanceName')
            ?? data_get($status, 'instance.instanceName')
            ?? $this->instanceName()
        ));

        $perfil = trim((string) data_get($instanceInfo, 'profileName', ''));
        $perfil = $perfil !== '' ? $perfil : null;

        $ownerJid = (string) data_get($instanceInfo, 'ownerJid', '');
        $numero = null;

        if ($ownerJid !== '') {
            $digits = preg_replace('/\D+/', '', explode('@', $ownerJid)[0] ?? '') ?: '';

            if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
                $local = substr($digits, 4);
                $ddd = substr($digits, 2, 2);

                if (strlen($local) === 9) {
                    $numero = sprintf('+%s (%s) %s-%s', substr($digits, 0, 2), $ddd, substr($local, 0, 5), substr($local, 5, 4));
                } elseif (strlen($local) === 8) {
                    $numero = sprintf('+%s (%s) %s-%s', substr($digits, 0, 2), $ddd, substr($local, 0, 4), substr($local, 4, 4));
                } else {
                    $numero = '+'.$digits;
                }
            } elseif ($digits !== '') {
                $numero = $digits;
            }
        }

        return [
            'nome' => $nome !== '' ? $nome : $this->instanceName(),
            'perfil' => $perfil,
            'numero' => $numero,
        ];
    }

    public function enviarComArquivo(string $numero, string $mensagem, ?UploadedFile $arquivo = null): bool
    {
        if ($arquivo !== null) {
            $resultado = $this->tentarEnviarMidiaArquivo($numero, $arquivo, $mensagem);
            $number = $this->normalizarNumeroWhatsapp($numero);
            if ($number !== null) {
                NotificacaoHistorico::registrar($number, $mensagem, $resultado['ok'] ? 'enviada' : 'erro');
            }

            return $resultado['ok'];
        }

        $ok = $this->enviarTexto($numero, $mensagem);
        $number = $this->normalizarNumeroWhatsapp($numero);
        if ($number !== null) {
            NotificacaoHistorico::registrar($number, $mensagem, $ok ? 'enviada' : 'erro');
        }

        return $ok;
    }

    public function normalizarNumeroWhatsapp(string $rawNumber): ?string
    {
        $digits = preg_replace('/\D+/', '', $rawNumber) ?: '';

        if ($digits === '') {
            return null;
        }

        // DDD + celular sem nono dígito (10 dígitos) → 55 + DDD + 9 + número
        if (strlen($digits) === 10) {
            $digits = '55'.substr($digits, 0, 2).'9'.substr($digits, 2);
        }

        // Celular com nono dígito, sem DDI (11 dígitos)
        if (strlen($digits) === 11) {
            $digits = '55'.$digits;
        }

        // DDI 55 + DDD + fixo/celular antigo sem nono (12 dígitos)
        if (strlen($digits) === 12 && str_starts_with($digits, '55')) {
            $ddd = substr($digits, 2, 2);
            $local = substr($digits, 4);

            if (strlen($local) === 8) {
                $digits = '55'.$ddd.'9'.$local;
            }
        }

        if (strlen($digits) === 13 && str_starts_with($digits, '55')) {
            return $digits;
        }

        return null;
    }

    public function numerosEquivalentes(string $a, string $b): bool
    {
        $normalizadoA = $this->normalizarNumeroWhatsapp($a);
        $normalizadoB = $this->normalizarNumeroWhatsapp($b);

        if ($normalizadoA === null || $normalizadoB === null) {
            return false;
        }

        return $normalizadoA === $normalizadoB;
    }

    public function isConfigured(): bool
    {
        return $this->cfg('base_url') !== ''
            && $this->cfg('api_key') !== ''
            && $this->instanceName() !== '';
    }

    public function substituirPlaceholdersPublico(string $template, Inscricao $inscricao): string
    {
        return $this->substituirPlaceholders($template, $inscricao);
    }

    private function substituirPlaceholders(string $template, Inscricao $inscricao): string
    {
        return strtr($template, [
            '{nome_do_inscrito}' => (string) $inscricao->nome,
            '{tamanho_camiseta}' => (string) $inscricao->tamanho_camiseta,
            '{codigo}' => (string) $inscricao->codigo,
            '{link_ingresso}' => filled($inscricao->codigo) ? $inscricao->urlIngresso() : '',
        ]);
    }

    private function cfg(string $key): string
    {
        return (string) config("services.evolution_api.{$key}", '');
    }

    private function instanceName(): string
    {
        return $this->cfg('instance_name');
    }

    private function resolveEndpoint(string $template): string
    {
        return str_replace('{instance}', $this->instanceName(), $template);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function post(string $endpoint, array $payload): Response
    {
        return Http::withHeaders($this->headers())
            ->timeout(20)
            ->post($this->url($endpoint), $payload);
    }

    private function get(string $endpoint): ?Response
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(20)
            ->get($this->url($endpoint));

        if (in_array($response->status(), [401, 403, 404], true)) {
            return null;
        }

        return $response;
    }

    private function url(string $endpoint): string
    {
        return rtrim($this->cfg('base_url'), '/').'/'.ltrim($endpoint, '/');
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'apikey' => $this->cfg('api_key'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function headersMultipart(): array
    {
        return [
            'Accept' => 'application/json',
            'apikey' => $this->cfg('api_key'),
        ];
    }

    private function guessMimeFromMediatype(string $mediatype, string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $ext = strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));

        return match ($mediatype) {
            'video' => 'video/mp4',
            'audio' => 'audio/mpeg',
            'document' => 'application/pdf',
            default => match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            },
        };
    }

    private function detectMediaType(?string $mimeType, string $originalName): string
    {
        $mime = strtolower((string) $mimeType);
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }

        $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return 'image';
        }
        if (in_array($ext, ['mp4', 'mov', 'webm'], true)) {
            return 'video';
        }
        if (in_array($ext, ['mp3', 'ogg', 'wav'], true)) {
            return 'audio';
        }

        return 'document';
    }

    private function extrairErroApi(Response $response): string
    {
        $body = $response->json();
        $message = data_get($body, 'response.message');

        if (is_array($message)) {
            $message = implode(' ', array_map('strval', $message));
        }

        $message = trim((string) ($message ?: data_get($body, 'error', '')));

        if (str_contains(strtolower($message), 'connection closed')) {
            return 'WhatsApp desconectado. Acesse Notificações → Configuração WPP e reconecte a instância.';
        }

        return $message !== '' ? $message : 'Erro desconhecido ao enviar mensagem.';
    }
}
