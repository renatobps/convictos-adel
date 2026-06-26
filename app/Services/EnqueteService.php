<?php

namespace App\Services;

use App\Models\Enquete;
use App\Models\EnqueteEnvio;
use App\Models\EnqueteResposta;
use App\Models\Inscricao;
use App\Models\NotificacaoGrupo;
use Illuminate\Support\Facades\Log;

class EnqueteService
{
    public const DESTINO_GRUPO = 'grupo';

    public const DESTINO_INSCRITO = 'inscrito';

    public const DESTINO_NUMEROS = 'numeros';

    public function __construct(
        private readonly WhatsAppService $whatsApp,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: int, erro: int, mensagem: ?string}
     */
    public function enviarDestino(Enquete $enquete, string $tipo, array $params = []): array
    {
        if ($msg = $this->whatsApp->obterMensagemSeDesconectado()) {
            return ['ok' => 0, 'erro' => 0, 'mensagem' => $msg];
        }

        if ($msg = $this->validarOpcoesEnquete($enquete)) {
            return ['ok' => 0, 'erro' => 0, 'mensagem' => $msg];
        }

        $resultado = match ($tipo) {
            self::DESTINO_GRUPO => $this->enviarParaGrupo(
                $enquete,
                NotificacaoGrupo::query()->findOrFail((int) ($params['grupo_id'] ?? 0)),
            ),
            self::DESTINO_INSCRITO => $this->enviarParaInscricao(
                $enquete,
                Inscricao::query()->findOrFail((int) ($params['inscricao_id'] ?? 0)),
            ),
            self::DESTINO_NUMEROS => $this->enviarParaNumeros(
                $enquete,
                (array) ($params['numeros'] ?? []),
            ),
            default => ['ok' => 0, 'erro' => 0],
        };

        return array_merge(['mensagem' => null], $resultado);
    }

    /**
     * @return array{ok: int, erro: int}
     */
    public function enviar(Enquete $enquete, ?NotificacaoGrupo $grupo = null): array
    {
        $grupo ??= $enquete->grupo;
        if ($grupo === null) {
            return ['ok' => 0, 'erro' => 0];
        }

        return $this->enviarParaGrupo($enquete, $grupo);
    }

    /**
     * @return array{ok: int, erro: int}
     */
    public function enviarParaGrupo(Enquete $enquete, NotificacaoGrupo $grupo): array
    {
        $ok = 0;
        $erro = 0;

        foreach ($grupo->inscricoesQuery()->get() as $inscricao) {
            $this->enviarParaInscricaoInterno($enquete, $inscricao) ? $ok++ : $erro++;
        }

        return ['ok' => $ok, 'erro' => $erro];
    }

    /**
     * @return array{ok: int, erro: int}
     */
    public function enviarParaInscricao(Enquete $enquete, Inscricao $inscricao): array
    {
        $enviado = $this->enviarParaInscricaoInterno($enquete, $inscricao);

        return [
            'ok' => $enviado ? 1 : 0,
            'erro' => $enviado ? 0 : 1,
        ];
    }

    /**
     * @param  array<int, string>  $numeros
     * @return array{ok: int, erro: int}
     */
    public function enviarParaNumeros(Enquete $enquete, array $numeros): array
    {
        $ok = 0;
        $erro = 0;

        foreach (array_unique(array_filter(array_map('trim', $numeros))) as $numeroRaw) {
            $this->enviarParaNumeroInterno($enquete, $numeroRaw) ? $ok++ : $erro++;
        }

        return ['ok' => $ok, 'erro' => $erro];
    }

    public static function destinoOptions(): array
    {
        return [
            self::DESTINO_GRUPO => 'Grupo (igreja, regional ou inscritos)',
            self::DESTINO_INSCRITO => 'Inscrito individual',
            self::DESTINO_NUMEROS => 'Número(s) individual(is)',
        ];
    }

    /**
     * Processa webhook da Evolution API (MESSAGES_UPSERT) e registra respostas de enquete.
     *
     * @param  array<string, mixed>  $payload
     */
    public function processarWebhookPayload(array $payload): bool
    {
        $processou = false;

        foreach ($this->normalizarItensMensagem($payload) as $item) {
            if ($this->processarItemMensagem($payload, $item)) {
                $processou = true;
            }
        }

        return $processou;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $data
     */
    private function processarItemMensagem(array $payload, array $data): bool
    {
        if ($this->deveIgnorarTipoMensagem($data)) {
            return false;
        }

        if (! $this->isEventoMensagemRecebida($payload, $data)) {
            $this->logRejeicaoEnquete($data, 'evento ou mensagem inválida');

            return false;
        }

        if ($this->isFromMe(data_get($data, 'key.fromMe')) && ! $this->isPossivelRespostaEnquete($data)) {
            $this->logRejeicaoEnquete($data, 'mensagem fromMe (enviada pela instância)');

            return false;
        }

        $resposta = $this->extrairRespostaMensagem($data);
        if ($resposta === null) {
            if ($this->pareceRespostaEnquete($data)) {
                $this->logRejeicaoEnquete($data, 'resposta reconhecível mas texto/botão não encontrado no payload');
            }

            return false;
        }

        $numero = $this->extrairNumeroRemetente($data);
        if ($numero === null) {
            $numero = $this->resolverNumeroPorEnqueteEnvio($resposta);
        }

        if ($numero === null) {
            $this->logRejeicaoEnquete($data, 'número do remetente não identificado', [
                'enquete_id' => $resposta['enquete_id'],
                'opcao_indice' => $resposta['opcao_indice'],
                'texto' => $resposta['texto'],
            ]);

            return false;
        }

        return $this->registrarRespostaWebhook($numero, $resposta);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $extra
     */
    private function logRejeicaoEnquete(array $data, string $motivo, array $extra = []): void
    {
        if (! $this->pareceRespostaEnquete($data) && $extra === []) {
            return;
        }

        Log::info('Webhook enquete não registrado.', array_merge([
            'motivo' => $motivo,
            'messageType' => data_get($data, 'messageType'),
            'remoteJid' => data_get($data, 'key.remoteJid'),
            'remoteJidAlt' => data_get($data, 'key.remoteJidAlt'),
            'fromMe' => data_get($data, 'key.fromMe'),
        ], $extra));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function pareceRespostaEnquete(array $data): bool
    {
        return $this->isPossivelRespostaEnquete($data);
    }

    /**
     * Botão nativo OU texto curto (ex.: "SIM", "NÃO") enviado como resposta no WhatsApp.
     *
     * @param  array<string, mixed>  $data
     */
    private function isPossivelRespostaEnquete(array $data): bool
    {
        $tipo = strtolower((string) (data_get($data, 'messageType') ?? ''));

        if (in_array($tipo, [
            'templatebuttonreplymessage',
            'buttonsresponsemessage',
            'listresponsemessage',
            'interactivemessage',
        ], true)) {
            return true;
        }

        if (in_array($tipo, ['conversation', 'extendedtextmessage'], true)) {
            $texto = $this->extrairTextoSimples($data);

            return $texto !== '' && mb_strlen($texto) <= 40;
        }

        return $this->extrairRespostaMensagem($data) !== null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extrairTextoSimples(array $data): string
    {
        $message = $this->normalizarConteudoMensagem($data);

        return trim((string) (
            data_get($message, 'conversation')
            ?? data_get($message, 'extendedTextMessage.text')
            ?? data_get($data, 'conversation')
            ?? data_get($data, 'extendedTextMessage.text')
            ?? data_get($data, 'message.conversation')
            ?? data_get($data, 'message.extendedTextMessage.text')
            ?? ''
        ));
    }

    /**
     * Fallback quando o WhatsApp envia @lid sem telefone: usa envio pendente da enquete.
     *
     * @param  array{enquete_id: ?int, opcao_indice: ?int, texto: string}  $resposta
     */
    private function resolverNumeroPorEnqueteEnvio(array $resposta): ?string
    {
        if ($resposta['enquete_id'] === null) {
            return null;
        }

        $envios = EnqueteEnvio::query()
            ->where('enquete_id', $resposta['enquete_id'])
            ->where('status', 'enviada')
            ->latest('id')
            ->get();

        if ($envios->isEmpty()) {
            return null;
        }

        $respondidos = EnqueteResposta::query()
            ->where('enquete_id', $resposta['enquete_id'])
            ->pluck('destinatario')
            ->all();

        $pendentes = $envios->filter(function (EnqueteEnvio $envio) use ($respondidos) {
            foreach ($respondidos as $destinatario) {
                if ($this->whatsApp->numerosEquivalentes($destinatario, $envio->destinatario)) {
                    return false;
                }
            }

            return true;
        });

        if ($pendentes->count() === 1) {
            return $pendentes->first()->destinatario;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isRespostaBotao(array $data): bool
    {
        $tipo = strtolower((string) (data_get($data, 'messageType') ?? ''));

        if (in_array($tipo, [
            'templatebuttonreplymessage',
            'buttonsresponsemessage',
            'listresponsemessage',
            'interactivemessage',
        ], true)) {
            return true;
        }

        return $this->extrairRespostaMensagem($data) !== null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function deveIgnorarTipoMensagem(array $data): bool
    {
        $tipo = strtolower((string) (data_get($data, 'messageType') ?? ''));

        return in_array($tipo, [
            'reactionmessage',
            'protocolmessage',
            'senderkeydistributionmessage',
            'pollupdatemessage',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function normalizarItensMensagem(array $payload): array
    {
        $data = $payload['data'] ?? $payload;

        if (! is_array($data)) {
            return [];
        }

        if (isset($data[0]) && is_array($data[0])) {
            return array_values(array_filter($data, 'is_array'));
        }

        if (isset($data['messages']) && is_array($data['messages'])) {
            return array_values(array_filter($data['messages'], 'is_array'));
        }

        return [$data];
    }

    private function isFromMe(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array{enquete_id: ?int, opcao_indice: ?int, texto: string}  $resposta
     */
    private function registrarRespostaWebhook(string $numero, array $resposta): bool
    {
        $enquete = null;

        if ($resposta['enquete_id'] !== null) {
            $enquete = Enquete::query()->find($resposta['enquete_id']);
        }

        if ($enquete === null) {
            $textoBruto = trim($resposta['texto']);

            if ($textoBruto !== '') {
                $enquete = $this->resolverEnquetePorTextoEnvio($numero, $textoBruto);
            }

            if ($enquete === null) {
                $envio = EnqueteEnvio::query()
                    ->where('status', 'enviada')
                    ->latest('id')
                    ->get()
                    ->first(fn (EnqueteEnvio $item) => $this->whatsApp->numerosEquivalentes($item->destinatario, $numero));

                $enquete = $envio?->enquete;
            }
        }

        if ($enquete === null || ! $enquete->ativa) {
            Log::info('Webhook enquete não registrado.', [
                'motivo' => 'enquete não encontrada ou inativa',
                'remetente' => $numero,
                'enquete_id' => $resposta['enquete_id'],
                'opcao_indice' => $resposta['opcao_indice'],
                'texto_bruto' => $resposta['texto'],
            ]);

            return false;
        }

        $envio = $this->resolverEnvioEnquete($enquete, $numero);
        $destinatario = $envio?->destinatario
            ?? $this->whatsApp->normalizarNumeroWhatsapp($numero)
            ?? $numero;

        $opcoes = $this->opcoesNormalizadas($enquete);
        $texto = trim($resposta['texto']);

        if ($texto === '' && $resposta['opcao_indice'] !== null) {
            $texto = (string) ($opcoes[$resposta['opcao_indice']] ?? '');
        }

        if ($texto !== '') {
            $opcaoCorrespondente = $this->corresponderOpcaoPorTexto($texto, $opcoes);

            if ($opcaoCorrespondente === null && $resposta['enquete_id'] === null && $resposta['opcao_indice'] === null) {
                Log::info('Webhook enquete não registrado.', [
                    'motivo' => 'texto não corresponde a nenhuma opção da enquete',
                    'enquete_id' => $enquete->id,
                    'destinatario' => $destinatario,
                    'texto_bruto' => $resposta['texto'],
                    'opcoes' => $opcoes,
                ]);

                return false;
            }

            $texto = $opcaoCorrespondente ?? $texto;
        }

        if ($texto === '') {
            Log::debug('Webhook enquete: resposta vazia após interpretação.', [
                'destinatario' => $destinatario,
                'enquete_id' => $enquete->id,
                'opcao_indice' => $resposta['opcao_indice'],
                'texto_bruto' => $resposta['texto'],
            ]);

            return false;
        }

        $opcaoIndice = $resposta['opcao_indice'];
        if ($opcaoIndice === null) {
            $indiceEncontrado = array_search($texto, $opcoes, true);
            $opcaoIndice = $indiceEncontrado !== false ? (int) $indiceEncontrado : null;
        }

        $origem = $resposta['enquete_id'] !== null || $resposta['opcao_indice'] !== null
            ? 'botao'
            : 'texto';

        $registro = EnqueteResposta::query()->create([
            'enquete_id' => $enquete->id,
            'enquete_envio_id' => $envio?->id,
            'destinatario' => $destinatario,
            'nome_destinatario' => $envio?->nome_destinatario,
            'resposta' => $texto,
            'opcao_indice' => $opcaoIndice,
            'origem' => $origem,
        ]);

        $this->enviarAgradecimentoResposta($enquete, $destinatario, $texto, $envio?->nome_destinatario);

        Log::info('Resposta de enquete registrada.', [
            'enquete_id' => $enquete->id,
            'destinatario' => $destinatario,
            'remetente_webhook' => $numero,
            'resposta' => $texto,
            'origem' => $origem,
            'resposta_id' => $registro->id,
        ]);

        return true;
    }

    private function enviarAgradecimentoResposta(
        Enquete $enquete,
        string $destinatario,
        string $resposta,
        ?string $nomeDestinatario,
    ): void {
        $template = trim((string) (
            $enquete->mensagem_agradecimento
            ?: config('services.enquete.agradecimento', '')
        ));

        if ($template === '') {
            return;
        }

        $mensagem = str_replace(
            ['{resposta}', '{nome}', '{pergunta}', '{titulo}'],
            [
                $resposta,
                $nomeDestinatario ?? '',
                (string) $enquete->pergunta,
                (string) $enquete->titulo,
            ],
            $template,
        );

        $resultado = $this->whatsApp->tentarEnviarTexto($destinatario, $mensagem);

        if (! $resultado['ok']) {
            Log::warning('Falha ao enviar agradecimento de enquete.', [
                'enquete_id' => $enquete->id,
                'destinatario' => $destinatario,
                'erro' => $resultado['erro'],
            ]);
        }
    }

    /**
     * @return array<string, int>
     */
    public static function metricasRespostas(Enquete $enquete): array
    {
        return EnqueteResposta::query()
            ->where('enquete_id', $enquete->id)
            ->selectRaw('resposta, COUNT(*) as total')
            ->groupBy('resposta')
            ->orderByDesc('total')
            ->pluck('total', 'resposta')
            ->all();
    }

    private function resolverEnvioEnquete(Enquete $enquete, string $numero): ?EnqueteEnvio
    {
        return EnqueteEnvio::query()
            ->where('enquete_id', $enquete->id)
            ->where('status', 'enviada')
            ->latest('id')
            ->get()
            ->first(fn (EnqueteEnvio $item) => $this->whatsApp->numerosEquivalentes($item->destinatario, $numero));
    }

    private function resolverEnquetePorTextoEnvio(string $numero, string $texto): ?Enquete
    {
        $envios = EnqueteEnvio::query()
            ->where('status', 'enviada')
            ->with('enquete')
            ->latest('id')
            ->get()
            ->filter(fn (EnqueteEnvio $envio) => $this->whatsApp->numerosEquivalentes($envio->destinatario, $numero));

        foreach ($envios as $envio) {
            $enquete = $envio->enquete;

            if ($enquete === null || ! $enquete->ativa) {
                continue;
            }

            $opcoes = $this->opcoesNormalizadas($enquete);

            if ($this->corresponderOpcaoPorTexto($texto, $opcoes) !== null) {
                return $enquete;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $data
     */
    private function isEventoMensagemRecebida(array $payload, array $data): bool
    {
        $evento = strtolower(str_replace('.', '_', (string) ($payload['event'] ?? '')));

        if ($evento !== '') {
            $eventoValido = str_contains($evento, 'messages_upsert')
                || str_contains($evento, 'message_upsert');

            if (! $eventoValido) {
                return false;
            }
        }

        return data_get($data, 'message') !== null
            || data_get($data, 'messageType') !== null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function extrairNumeroRemetente(array $data): ?string
    {
        $remoteJid = (string) data_get($data, 'key.remoteJid', '');

        if (str_contains($remoteJid, '@g.us')) {
            $participante = (string) (
                data_get($data, 'key.participant')
                ?? data_get($data, 'participant')
                ?? ''
            );

            return $this->numeroDeJid($participante);
        }

        $candidatos = [
            data_get($data, 'key.remoteJidAlt'),
            data_get($data, 'key.remoteJid'),
            data_get($data, 'key.participant'),
            data_get($data, 'participant'),
            data_get($data, 'remoteJid'),
        ];

        foreach ($candidatos as $jid) {
            $numero = $this->numeroDeJid((string) $jid);

            if ($numero !== null && ! str_contains((string) $jid, '@g.us')) {
                return $numero;
            }
        }

        return null;
    }

    private function numeroDeJid(string $jid): ?string
    {
        if ($jid === '' || str_contains($jid, '@g.us')) {
            return null;
        }

        if (str_contains($jid, '@lid')) {
            return null;
        }

        $parte = explode('@', $jid)[0] ?? '';
        $digits = preg_replace('/\D+/', '', $parte) ?: '';

        return $this->whatsApp->normalizarNumeroWhatsapp($digits);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizarConteudoMensagem(array $data): array
    {
        $message = (array) (data_get($data, 'message') ?? []);
        $messageType = (string) (data_get($data, 'messageType') ?? '');

        if ($messageType !== '' && ! isset($message[$messageType])) {
            $bloco = data_get($data, $messageType);
            if (is_array($bloco)) {
                $message[$messageType] = $bloco;
            }
        }

        foreach ([
            'templateButtonReplyMessage',
            'buttonsResponseMessage',
            'listResponseMessage',
            'interactiveResponseMessage',
        ] as $tipo) {
            if (! isset($message[$tipo]) && is_array(data_get($data, $tipo))) {
                $message[$tipo] = data_get($data, $tipo);
            }
        }

        return $message;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{enquete_id: ?int, opcao_indice: ?int, texto: string}|null
     */
    private function extrairRespostaMensagem(array $data): ?array
    {
        $message = $this->normalizarConteudoMensagem($data);

        $buttonId = trim((string) $this->buscarValorAninhado($message, [
            ['buttonsResponseMessage', 'selectedButtonId'],
            ['buttonsResponseMessage', 'selectedId'],
            ['templateButtonReplyMessage', 'selectedId'],
            ['templateButtonReplyMessage', 'selectedButtonId'],
            ['interactiveResponseMessage', 'nativeFlowResponseMessage', 'name'],
            ['listResponseMessage', 'singleSelectReply', 'selectedRowId'],
        ]));

        $buttonText = trim((string) $this->buscarValorAninhado($message, [
            ['buttonsResponseMessage', 'selectedDisplayText'],
            ['buttonsResponseMessage', 'selectedButtonText'],
            ['templateButtonReplyMessage', 'selectedDisplayText'],
            ['templateButtonReplyMessage', 'selectedButtonText'],
            ['listResponseMessage', 'title'],
            ['listResponseMessage', 'description'],
        ]));

        $selectedIndex = $this->buscarValorAninhado($message, [
            ['templateButtonReplyMessage', 'selectedIndex'],
            ['buttonsResponseMessage', 'selectedIndex'],
        ]);

        if ($buttonId === '' && $buttonText === '') {
            $messageType = strtolower((string) (data_get($data, 'messageType') ?? ''));

            if (str_contains($messageType, 'button') || str_contains($messageType, 'list') || str_contains($messageType, 'template')) {
                $buttonId = trim((string) (
                    data_get($data, 'selectedButtonId')
                    ?? data_get($data, 'selectedId')
                    ?? data_get($data, 'templateButtonReplyMessage.selectedId')
                    ?? data_get($data, 'buttonsResponseMessage.selectedButtonId')
                    ?? data_get($message, 'selectedId')
                    ?? ''
                ));
                $buttonText = trim((string) (
                    data_get($data, 'selectedDisplayText')
                    ?? data_get($data, 'selectedButtonText')
                    ?? data_get($data, 'templateButtonReplyMessage.selectedDisplayText')
                    ?? data_get($data, 'buttonsResponseMessage.selectedDisplayText')
                    ?? data_get($message, 'selectedDisplayText')
                    ?? ''
                ));
            }
        }

        if ($buttonId !== '' || $buttonText !== '' || $selectedIndex !== null) {
            $parsed = $this->interpretarIdentificadorBotao($buttonId);
            $opcaoIndice = $parsed['opcao_indice'];

            if ($opcaoIndice === null && is_numeric($selectedIndex)) {
                $opcaoIndice = max(0, (int) $selectedIndex);
            }

            return [
                'enquete_id' => $parsed['enquete_id'],
                'opcao_indice' => $opcaoIndice,
                'texto' => $buttonText !== '' ? $buttonText : $parsed['texto_fallback'],
            ];
        }

        $texto = $this->extrairTextoSimples($data);

        if ($texto === '') {
            return null;
        }

        if (preg_match('/^enq_(\d+)_(\d+)$/i', $texto, $matches) === 1) {
            return [
                'enquete_id' => (int) $matches[1],
                'opcao_indice' => max(0, (int) $matches[2] - 1),
                'texto' => '',
            ];
        }

        return [
            'enquete_id' => null,
            'opcao_indice' => null,
            'texto' => $texto,
        ];
    }

    /**
     * @param  array<string, mixed>  $dados
     * @param  array<int, array<int, string>>  $caminhos
     */
    private function buscarValorAninhado(array $dados, array $caminhos): mixed
    {
        foreach ($caminhos as $caminho) {
            $valor = $dados;
            foreach ($caminho as $chave) {
                if (! is_array($valor) || ! array_key_exists($chave, $valor)) {
                    $valor = null;
                    break;
                }
                $valor = $valor[$chave];
            }

            if (filled($valor)) {
                return $valor;
            }
        }

        return null;
    }

    /**
     * @return array{enquete_id: ?int, opcao_indice: ?int, texto_fallback: string}
     */
    private function interpretarIdentificadorBotao(string $buttonId): array
    {
        if (preg_match('/^enq_(\d+)_(\d+)$/i', $buttonId, $matches) === 1) {
            return [
                'enquete_id' => (int) $matches[1],
                'opcao_indice' => max(0, (int) $matches[2] - 1),
                'texto_fallback' => '',
            ];
        }

        if (is_numeric($buttonId)) {
            return [
                'enquete_id' => null,
                'opcao_indice' => max(0, (int) $buttonId - 1),
                'texto_fallback' => '',
            ];
        }

        return [
            'enquete_id' => null,
            'opcao_indice' => null,
            'texto_fallback' => $buttonId,
        ];
    }

    /**
     * @param  array<int, string>  $opcoes
     */
    private function corresponderOpcaoPorTexto(string $texto, array $opcoes): ?string
    {
        $textoNormalizado = $this->normalizarTextoComparacao($texto);

        foreach ($opcoes as $opcao) {
            if ($this->normalizarTextoComparacao($opcao) === $textoNormalizado) {
                return $opcao;
            }
        }

        return null;
    }

    private function normalizarTextoComparacao(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));

        if (class_exists(\Normalizer::class)) {
            $normalizado = \Normalizer::normalize($texto, \Normalizer::FORM_D);

            if (is_string($normalizado)) {
                $texto = preg_replace('/\p{M}/u', '', $normalizado) ?? $normalizado;
            }
        }

        return $texto;
    }

    /**
     * @return array<int, string>
     */
    private function opcoesNormalizadas(Enquete $enquete): array
    {
        return array_values(array_filter(
            array_map(
                fn (mixed $opcao) => trim(is_string($opcao) ? $opcao : (string) ($opcao['label'] ?? $opcao['name'] ?? '')),
                (array) ($enquete->opcoes ?? []),
            ),
            fn (string $opcao) => $opcao !== '',
        ));
    }

    private function enviarParaInscricaoInterno(Enquete $enquete, Inscricao $inscricao): bool
    {
        $numero = $this->whatsApp->normalizarNumeroWhatsapp((string) $inscricao->whatsapp);
        if ($numero === null) {
            return false;
        }

        $resultado = $this->whatsApp->tentarEnviarEnqueteBotoes(
            $numero,
            (string) $enquete->titulo,
            (string) $enquete->pergunta,
            (array) ($enquete->opcoes ?? []),
            $enquete->id,
        );

        return $this->registrarEnvio($enquete, $numero, $inscricao->nome, $resultado['ok']);
    }

    private function enviarParaNumeroInterno(Enquete $enquete, string $numeroRaw): bool
    {
        $numero = $this->whatsApp->normalizarNumeroWhatsapp($numeroRaw);
        if ($numero === null) {
            return false;
        }

        $resultado = $this->whatsApp->tentarEnviarEnqueteBotoes(
            $numero,
            (string) $enquete->titulo,
            (string) $enquete->pergunta,
            (array) ($enquete->opcoes ?? []),
            $enquete->id,
        );

        return $this->registrarEnvio($enquete, $numero, null, $resultado['ok']);
    }

    private function validarOpcoesEnquete(Enquete $enquete): ?string
    {
        $opcoes = array_values(array_filter(
            array_map(
                fn (mixed $opcao) => trim(is_string($opcao) ? $opcao : (string) ($opcao['label'] ?? $opcao['name'] ?? '')),
                (array) ($enquete->opcoes ?? []),
            ),
            fn (string $opcao) => $opcao !== '',
        ));

        if (count($opcoes) < 2) {
            return 'A enquete precisa de pelo menos 2 opções para enviar botões no WhatsApp.';
        }

        return null;
    }

    private function registrarEnvio(Enquete $enquete, string $numero, ?string $nome, bool $enviado): bool
    {
        EnqueteEnvio::query()->create([
            'enquete_id' => $enquete->id,
            'destinatario' => $numero,
            'nome_destinatario' => $nome,
            'status' => $enviado ? 'enviada' : 'erro',
        ]);

        return $enviado;
    }
}
