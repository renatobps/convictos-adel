<?php

namespace App\Http\Controllers;

use App\Jobs\EnviarNotificacoesPosInscricao;
use App\Models\Igreja;
use App\Models\Inscricao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InscricaoController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'whatsapp' => [
                    'required',
                    'string',
                    'regex:/^\(\d{2}\) \d{5}-\d{4}$/',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (Inscricao::jaExisteWhatsapp((string) $value)) {
                            $fail('Este número de celular já foi usado em uma inscrição. Cada pessoa pode se inscrever apenas uma vez.');
                        }
                    },
                ],
                'idade' => ['required', 'integer', 'min:10', 'max:120'],
                'tipo_ingresso' => ['required', Rule::in([Inscricao::TIPO_COM_CAMISETA, Inscricao::TIPO_SEM_CAMISETA])],
                'tamanho_camiseta' => [
                    'nullable',
                    'string',
                    Rule::requiredIf($request->input('tipo_ingresso') === Inscricao::TIPO_COM_CAMISETA),
                    Rule::in(array_keys(Inscricao::tamanhoCamisetaOptions())),
                ],
                'igreja_id' => ['required', 'integer', 'exists:igrejas,id'],
                'lider' => ['required', 'in:sim,nao'],
            ], [
                'whatsapp.regex' => 'Informe o WhatsApp no formato (99) 99999-9999.',
                'tipo_ingresso.required' => 'Selecione se a inscrição será com ou sem camiseta.',
                'tamanho_camiseta.required' => 'Selecione o tamanho da camiseta.',
                'tamanho_camiseta.required_if' => 'Selecione o tamanho da camiseta.',
            ]);
        } catch (ValidationException $e) {
            throw $e->redirectTo(route('home').'#inscricao');
        }

        $config = DB::table('inscricao_meta_configuracoes')->first();
        $comCamiseta = $validated['tipo_ingresso'] === Inscricao::TIPO_COM_CAMISETA;
        $valor = $comCamiseta
            ? (float) ($config?->valor_com_camiseta ?? $config?->valor_inscricao ?? 0)
            : (float) ($config?->valor_sem_camiseta ?? 0);

        $igreja = Igreja::query()->with('regional')->findOrFail($validated['igreja_id']);

        $inscricao = Inscricao::create([
            'nome' => $validated['nome'],
            'email' => $validated['email'] ?: 'inscrito.'.now()->timestamp.'@convictos.local',
            'whatsapp' => $validated['whatsapp'],
            'idade' => (string) $validated['idade'],
            'tipo_ingresso' => $validated['tipo_ingresso'],
            'valor' => round($valor, 2),
            'tamanho_camiseta' => $comCamiseta ? $validated['tamanho_camiseta'] : null,
            'igreja_id' => $igreja->id,
            'igreja' => $igreja->nomeNoFormulario(),
            'lider_jovens' => $validated['lider'] === 'sim',
            'status' => Inscricao::STATUS_PENDENTE,
        ]);

        // Fila: a página de sucesso responde na hora; WhatsApp/e-mail rodam depois.
        EnviarNotificacoesPosInscricao::dispatch($inscricao->id);

        return redirect()
            ->route('ingresso.show', ['inscricao' => $inscricao->codigo])
            ->with('inscricao_success', 'Inscrição registrada com status Pendente. Realize o pagamento via PIX com o coordenador da sua regional. Guarde o código do ingresso abaixo.');
    }
}
