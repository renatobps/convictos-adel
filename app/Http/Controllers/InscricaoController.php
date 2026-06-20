<?php

namespace App\Http\Controllers;

use App\Mail\InscricaoAdmin;
use App\Mail\InscricaoRecebida;
use App\Models\Inscricao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InscricaoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'idade' => ['nullable', 'string', 'max:10'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'igreja' => ['nullable', 'string', 'max:255'],
        ]);

        $inscricao = Inscricao::create($data + ['status' => 'novo']);

        $this->sendEmails($inscricao);

        return redirect()
            ->route('home')
            ->withFragment('inscricao')
            ->with('inscricao_success', 'Inscrição enviada! Em breve entraremos em contato.');
    }

    protected function sendEmails(Inscricao $inscricao): void
    {
        try {
            Mail::to($inscricao->email)->send(new InscricaoRecebida($inscricao));

            if ($admin = config('services.loja.email_admin')) {
                Mail::to($admin)->send(new InscricaoAdmin($inscricao));
            }
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail de inscrição', ['message' => $e->getMessage()]);
        }
    }
}
