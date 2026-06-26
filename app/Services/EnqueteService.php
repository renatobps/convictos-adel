<?php

namespace App\Services;

use App\Models\Enquete;
use App\Models\EnqueteEnvio;
use App\Models\Inscricao;
use App\Models\NotificacaoGrupo;

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
