<?php

namespace App\Jobs;

use App\Mail\InscricaoAdmin;
use App\Mail\InscricaoStatusMail;
use App\Models\Inscricao;
use App\Services\WhatsAppService;
use App\Support\EmailConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarNotificacoesPosInscricao implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $inscricaoId) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $inscricao = Inscricao::query()->find($this->inscricaoId);

        if (! $inscricao) {
            Log::warning('Notificações pós-inscrição: inscrição não encontrada.', [
                'inscricao_id' => $this->inscricaoId,
            ]);

            return;
        }

        try {
            $whatsApp->enviarPosInscricao($inscricao);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar WhatsApp pós-inscrição', [
                'inscricao_id' => $this->inscricaoId,
                'message' => $e->getMessage(),
            ]);
        }

        $this->enviarEmails($inscricao);
    }

    protected function enviarEmails(Inscricao $inscricao): void
    {
        if (str_contains((string) $inscricao->email, '@convictos.local')) {
            return;
        }

        try {
            EmailConfig::aplicarMailer();

            if (EmailConfig::templateAtivo(EmailConfig::TIPO_REALIZADA)) {
                Mail::to($inscricao->email)->send(new InscricaoStatusMail($inscricao, EmailConfig::TIPO_REALIZADA));
            }

            if ($admin = config('services.loja.email_admin')) {
                Mail::to($admin)->send(new InscricaoAdmin($inscricao));
            }
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail de inscrição', [
                'inscricao_id' => $inscricao->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Job EnviarNotificacoesPosInscricao falhou definitivamente', [
            'inscricao_id' => $this->inscricaoId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
