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
            'type' => $mediatype,
            'url' => $url,
            'filename' => $filename,
            'caption' => $caption,
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
            'type' => $mediatype,
            'url' => 'data:'.$mimetype.';base64,'.base64_encode($conteudo),
            'filename' => $filename,
            'caption' => $caption,
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
        $mimetype = $arquivo->getMimeType() ?: $this->guessMimeFromMediatype($mediatype, $originalName);

        $path = $arquivo->getRealPath();
        if ($path === false || ! is_readable($path)) {
            $this->ultimoErro = 'Não foi possível ler o arquivo anexo.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        $conteudo = file_get_contents($path);
        if ($conteudo === false) {
            $this->ultimoErro = 'Não foi possível ler o arquivo anexo.';

            return ['ok' => false, 'erro' => $this->ultimoErro];
        }

        // Evolution GO espera JSON em /send/media (url/type/filename).
        return $this->tentarEnviarMidiaBase64(
            $numero,
            $caption,
            $conteudo,
            $originalName,
            $mediatype,
            $mimetype,
        );
    }

    /**
     * @return array{status: mixed, qrCode: string, pairingCode: string, qrMensagem: string, instanceInfo: mixed, erros: array<int, string>}
     */
    public function obterStatusInstancia(string $action = 'all'): array
    {
        $status = null;
        $qrCode = '';
        $pairingCode = '';
        $qrMensagem = '';
        $instanceInfo = null;
        $erros = [];

        if (! $this->isConfigured()) {
            $erros[] = 'Configure WHATSAPP_API_URL e WHATSAPP_API_KEY no .env.';

            return compact('status', 'qrCode', 'pairingCode', 'qrMensagem', 'instanceInfo', 'erros');
        }

        if (in_array($action, ['status', 'qr', 'all'], true)) {
            $statusResponse = $this->get('/instance/status');
            if ($statusResponse !== null && $statusResponse->ok()) {
                $status = $statusResponse->json();
                $instanceInfo = data_get($status, 'data', $status);
            } elseif (in_array($action, ['status', 'all'], true)) {
                $detalhe = $statusResponse ? $this->extrairErroApi($statusResponse) : '';
                $erros[] = $detalhe !== '' && $detalhe !== 'Erro desconhecido ao enviar mensagem.'
                    ? 'Status: '.$detalhe
                    : 'Não foi possível obter o status da instância.';
            }
        }

        // Em Evolution GO, /instance/all exige chave global (admin). Com token de instância retorna 401 — isso é esperado.
        $allResponse = $this->get('/instance/all');
        if ($allResponse !== null && $allResponse->ok()) {
            $payload = $allResponse->json();
            $lista = collect(data_get($payload, 'data', $payload));
            $instanceName = $this->instanceName();
            $encontrada = $lista->first(function (mixed $item) use ($instanceName, $instanceInfo) {
                $nome = (string) (data_get($item, 'name') ?? data_get($item, 'Name') ?? data_get($item, 'instanceName') ?? '');
                $atual = (string) (data_get($instanceInfo, 'Name') ?? '');

                return ($instanceName !== '' && strcasecmp($nome, $instanceName) === 0)
                    || ($atual !== '' && strcasecmp($nome, $atual) === 0);
            });

            if (is_array($encontrada)) {
                $instanceInfo = array_merge(is_array($instanceInfo) ? $instanceInfo : [], $encontrada);
            }
        }

        if (in_array($action, ['qr', 'all'], true)) {
            if ($this->instanciaConectada($status, $instanceInfo)) {
                $qrMensagem = 'Instância já conectada. Para gerar um novo QR, use "Desconectar e gerar QR".';
            } else {
                $parsed = $this->solicitarQrCode();

                if ($parsed['qrCode'] === '' && $parsed['mensagem'] === '') {
                    usleep(2_000_000);
                    $parsed = $this->solicitarQrCode();
                }

                $qrCode = $parsed['qrCode'];
                $pairingCode = $parsed['pairingCode'];
                $qrMensagem = $parsed['mensagem'];

                if ($qrCode === '' && $qrMensagem === '') {
                    $erros[] = 'QR Code indisponível. Tente "Desconectar e gerar QR" ou aguarde alguns segundos.';
                }
            }
        }

        return compact('status', 'qrCode', 'pairingCode', 'qrMensagem', 'instanceInfo', 'erros');
    }

    public function desconectarInstancia(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $logout = Http::withHeaders($this->headers())
            ->timeout(20)
            ->delete($this->url('/instance/logout'));

        if ($logout->successful()) {
            return true;
        }

        $disconnect = Http::withHeaders($this->headers())
            ->timeout(20)
            ->post($this->url('/instance/disconnect'), []);

        return $disconnect->successful();
    }

    public function configurarWebhook(?string $url = null): bool
    {
        $url ??= (string) config('services.evolution_api.webhook_url', '');

        if ($url === '' || ! $this->isConfigured()) {
            return false;
        }

        $response = Http::withHeaders($this->headers())
            ->timeout(20)
            ->post($this->url('/instance/connect'), [
                'immediate' => true,
                'webhookUrl' => $url,
                'subscribe' => ['MESSAGE'],
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::warning('Falha ao configurar webhook na Evolution GO.', [
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
    }

    /**
     * @return array{qrCode: string, pairingCode: string, mensagem: string}
     */
    private function solicitarQrCode(): array
    {
        // Na Evolution GO, o QR costuma ser emitido após iniciar a conexão.
        Http::withHeaders($this->headers())
            ->timeout(20)
            ->post($this->url('/instance/connect'), [
                'immediate' => false,
            ]);

        $response = $this->get('/instance/qr');

        if ($response === null) {
            return ['qrCode' => '', 'pairingCode' => '', 'mensagem' => ''];
        }

        if ($response->status() === 400) {
            $erro = strtolower($this->extrairErroApi($response));
            if (str_contains($erro, 'already logged in') || str_contains($erro, 'já conect')) {
                return ['qrCode' => '', 'pairingCode' => '', 'mensagem' => 'Instância já conectada.'];
            }

            return ['qrCode' => '', 'pairingCode' => '', 'mensagem' => $this->extrairErroApi($response)];
        }

        if (! $response->ok()) {
            return ['qrCode' => '', 'pairingCode' => '', 'mensagem' => $this->extrairErroApi($response)];
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

        $data = is_array(data_get($json, 'data')) ? data_get($json, 'data') : $json;

        $pairingCode = trim((string) (
            data_get($data, 'pairingCode')
            ?? data_get($json, 'pairingCode')
            ?? ''
        ));

        $state = strtolower((string) (
            data_get($json, 'instance.state')
            ?? data_get($json, 'instance.status')
            ?? data_get($json, 'state')
            ?? ''
        ));

        $qrCode = '';

        foreach ([
            data_get($data, 'base64'),
            data_get($data, 'qrcode'),
            data_get($data, 'qrCode'),
            data_get($data, 'QR'),
            data_get($data, 'qr'),
            data_get($json, 'base64'),
            data_get($json, 'qrcode'),
            data_get($json, 'qrCode'),
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
            $code = trim((string) (data_get($data, 'code') ?? data_get($json, 'code') ?? ''));
            if ($code !== '' && ! str_starts_with($code, '{')) {
                // Alguns servidores devolvem o raw do QR em "code".
                $normalized = $this->normalizeBase64Image($code);
                $qrCode = $normalized;
            }
        }

        $mensagem = '';
        if ($qrCode === '') {
            if (in_array($state, ['open', 'connected'], true)) {
                $mensagem = 'Instância já conectada.';
            } elseif (data_get($data, 'Connected') === true || data_get($data, 'LoggedIn') === true) {
                $mensagem = 'Instância já conectada.';
            }
        }

        return [
            'qrCode' => $qrCode,
            'pairingCode' => $pairingCode,
            'mensagem' => $mensagem,
        ];
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
        foreach ([
            data_get($status, 'data.Connected'),
            data_get($status, 'data.LoggedIn'),
            data_get($status, 'Connected'),
            data_get($status, 'LoggedIn'),
            data_get($instanceInfo, 'Connected'),
            data_get($instanceInfo, 'LoggedIn'),
        ] as $flag) {
            if ($flag === true || $flag === 1 || $flag === 'true') {
                return true;
            }
        }

        $state = strtolower((string) (
            data_get($status, 'instance.state')
            ?? data_get($status, 'state')
            ?? data_get($instanceInfo, 'connectionStatus')
            ?? data_get($instanceInfo, 'state')
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
            data_get($instanceInfo, 'Name')
            ?? data_get($instanceInfo, 'name')
            ?? data_get($instanceInfo, 'profileName')
            ?? data_get($instanceInfo, 'instanceName')
            ?? data_get($status, 'data.Name')
            ?? data_get($status, 'instance.instanceName')
            ?? $this->instanceName()
        ));

        $perfil = trim((string) (
            data_get($instanceInfo, 'profileName')
            ?? data_get($instanceInfo, 'Name')
            ?? data_get($status, 'data.Name')
            ?? ''
        ));
        $perfil = $perfil !== '' ? $perfil : null;

        $ownerJid = (string) (
            data_get($instanceInfo, 'ownerJid')
            ?? data_get($instanceInfo, 'jid')
            ?? data_get($status, 'data.jid')
            ?? ''
        );
        $numero = null;

        if ($ownerJid !== '') {
            $digits = preg_replace('/\D+/', '', explode('@', $ownerJid)[0] ?? '') ?: '';
            // Remove device suffix "5561...:3"
            if (str_contains((string) explode('@', $ownerJid)[0], ':')) {
                $digits = preg_replace('/\D+/', '', explode(':', explode('@', $ownerJid)[0])[0] ?? '') ?: '';
            }

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
            && $this->cfg('api_key') !== '';
    }

    public function substituirPlaceholdersPublico(string $template, Inscricao $inscricao): string
    {
        return $this->substituirPlaceholders($template, $inscricao);
    }

    private function substituirPlaceholders(string $template, Inscricao $inscricao): string
    {
        return strtr($template, [
            '{nome_do_inscrito}' => (string) $inscricao->nome,
            '{tamanho_camiseta}' => $inscricao->comCamiseta()
                ? (string) $inscricao->tamanho_camiseta
                : 'Sem camiseta',
            '{tipo_ingresso}' => $inscricao->tipoIngressoLabel(),
            '{valor}' => $inscricao->valor !== null
                ? 'R$ '.number_format((float) $inscricao->valor, 2, ',', '.')
                : '',
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
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(20)
                ->get($this->url($endpoint));
        } catch (\Throwable $e) {
            Log::warning('Falha de conexão com Evolution GO.', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (in_array($response->status(), [401, 403, 404], true)) {
            $this->ultimoErro = $this->extrairErroApi($response);

            return $response->status() === 401 ? $response : null;
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
        $message = data_get($body, 'response.message')
            ?? data_get($body, 'message')
            ?? data_get($body, 'error')
            ?? data_get($body, 'data.error');

        if (is_array($message)) {
            $message = implode(' ', array_map('strval', $message));
        }

        $message = trim((string) $message);

        // "success" não é erro — alguns endpoints GO usam message=success.
        if (strtolower($message) === 'success') {
            $message = '';
        }

        if (str_contains(strtolower($message), 'connection closed') || str_contains(strtolower($message), 'not authorized')) {
            if (str_contains(strtolower($message), 'not authorized')) {
                return 'Não autorizado. Verifique se WHATSAPP_API_KEY é o token da instância na Evolution GO.';
            }

            return 'WhatsApp desconectado. Acesse Notificações → Configuração WPP e reconecte a instância.';
        }

        return $message !== '' ? $message : 'Erro desconhecido ao comunicar com a Evolution GO.';
    }
}
