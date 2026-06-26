<?php

namespace App\Http\Controllers;

use App\Mail\InscricaoAdmin;
use App\Mail\InscricaoStatusMail;
use App\Models\Igreja;
use App\Models\Inscricao;
use App\Services\WhatsAppService;
use App\Support\EmailConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InscricaoController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsApp
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp' => ['required', 'string', 'regex:/^\(\d{2}\) \d{5}-\d{4}$/'],
            'idade' => ['required', 'integer', 'min:10', 'max:120'],
            'tamanho_camiseta' => ['required', 'string', 'in:P,M,G,GG,XG'],
            'igreja_id' => ['required', 'integer', 'exists:igrejas,id'],
            'lider' => ['required', 'in:sim,nao'],
        ], [
            'whatsapp.regex' => 'Informe o WhatsApp no formato (99) 99999-9999.',
        ]);

        $igreja = Igreja::query()->with('regional')->findOrFail($validated['igreja_id']);

        $inscricao = Inscricao::create([
            'nome' => $validated['nome'],
            'email' => $validated['email'] ?: 'inscrito.'.now()->timestamp.'@convictos.local',
            'whatsapp' => $validated['whatsapp'],
            'idade' => (string) $validated['idade'],
            'tamanho_camiseta' => $validated['tamanho_camiseta'],
            'igreja_id' => $igreja->id,
            'igreja' => $igreja->nomeNoFormulario(),
            'lider_jovens' => $validated['lider'] === 'sim',
            'status' => Inscricao::STATUS_AGUARDANDO,
        ]);

        // Envia notificações (WhatsApp/e-mail) somente após a resposta ser
        // entregue ao usuário, para que o redirecionamento seja imediato.
        app()->terminating(function () use ($inscricao): void {
            try {
                app(WhatsAppService::class)->enviarPosInscricao($inscricao);
            } catch (\Throwable $e) {
                Log::error('Falha ao enviar WhatsApp pós-inscrição', ['message' => $e->getMessage()]);
            }

            $this->sendEmails($inscricao);
        });

        return redirect()
            ->route('ingresso.show', ['inscricao' => $inscricao->codigo])
            ->with('inscricao_success', 'Inscrição recebida! Este é o seu ingresso digital — guarde o código abaixo.');
    }

    protected function sendEmails(Inscricao $inscricao): void
    {
        if (str_contains($inscricao->email, '@convictos.local')) {
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
            Log::error('Falha ao enviar e-mail de inscrição', ['message' => $e->getMessage()]);
        }
    }
}
