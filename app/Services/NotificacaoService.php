<?php

namespace App\Services;

use App\Models\Inscricao;
use App\Models\NotificacaoEnviada;
use App\Models\NotificacaoGrupo;
use App\Support\NotificacaoHistorico;
use Illuminate\Http\UploadedFile;

class NotificacaoService
{
    public function __construct(
        private readonly WhatsAppService $whatsApp,
    ) {}

    /**
     * @return array{ok: int, erro: int, mensagem: ?string}
     */
    public function enviarParaGrupo(NotificacaoGrupo $grupo, string $mensagem, ?UploadedFile $arquivo = null): array
    {
        if ($msg = $this->whatsApp->obterMensagemSeDesconectado()) {
            return ['ok' => 0, 'erro' => 0, 'mensagem' => $msg];
        }

        $ok = 0;
        $erro = 0;

        foreach ($grupo->inscricoesQuery()->get() as $inscricao) {
            $resultado = $this->enviarParaInscricao($inscricao, $mensagem, $arquivo, 'grupo', $grupo->id);
            $resultado ? $ok++ : $erro++;
        }

        return ['ok' => $ok, 'erro' => $erro, 'mensagem' => null];
    }

    public function enviarManual(string $numeroRaw, string $mensagem, ?UploadedFile $arquivo = null): bool
    {
        $resultado = $arquivo !== null
            ? $this->whatsApp->tentarEnviarMidiaArquivo($numeroRaw, $arquivo, $mensagem)
            : $this->whatsApp->tentarEnviarTexto($numeroRaw, $mensagem);

        $numero = $this->whatsApp->normalizarNumeroWhatsapp($numeroRaw);
        if ($numero !== null) {
            $this->registrar($numero, $mensagem, $resultado['ok'] ? 'enviada' : 'erro', 'manual');
        }

        return $resultado['ok'];
    }

    public function enviarParaInscricao(
        Inscricao $inscricao,
        string $mensagem,
        ?UploadedFile $arquivo = null,
        string $tipoEnvio = 'massa',
        ?int $grupoId = null,
    ): bool {
        $numero = $this->whatsApp->normalizarNumeroWhatsapp((string) $inscricao->whatsapp);
        if ($numero === null) {
            return false;
        }

        $texto = $this->whatsApp->substituirPlaceholdersPublico($mensagem, $inscricao);

        $enviado = $arquivo
            ? $this->whatsApp->enviarComArquivo((string) $inscricao->whatsapp, $texto, $arquivo)
            : $this->whatsApp->enviarTexto($numero, $texto);

        $this->registrar($numero, $texto, $enviado ? 'enviada' : 'erro', $tipoEnvio, $grupoId, $inscricao->id);

        return $enviado;
    }

    private function registrar(
        string $destinatario,
        string $mensagem,
        string $status,
        string $tipoEnvio,
        ?int $grupoId = null,
        ?int $inscricaoId = null,
    ): void {
        NotificacaoHistorico::registrar($destinatario, $mensagem, $status);

        NotificacaoEnviada::query()->create([
            'destinatario' => $destinatario,
            'mensagem' => $mensagem,
            'status' => $status,
            'tipo_envio' => $tipoEnvio,
            'notificacao_grupo_id' => $grupoId,
            'inscricao_id' => $inscricaoId,
        ]);
    }
}
