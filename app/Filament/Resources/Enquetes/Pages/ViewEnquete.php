<?php

namespace App\Filament\Resources\Enquetes\Pages;

use App\Filament\Resources\Enquetes\EnqueteResource;
use App\Models\Inscricao;
use App\Models\NotificacaoGrupo;
use App\Services\EnqueteService;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEnquete extends ViewRecord
{
    protected static string $resource = EnqueteResource::class;

    protected string $view = 'filament.resources.enquetes.view-enquete';

    public string $tipo_destino = EnqueteService::DESTINO_GRUPO;

    public ?int $notificacao_grupo_id = null;

    public ?int $inscricao_id = null;

    /** @var array<int, string> */
    public array $numeros = [];

    public string $numero_input = '';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->tipo_destino = EnqueteService::DESTINO_GRUPO;
        $this->notificacao_grupo_id = $this->record->notificacao_grupo_id;
        $this->inscricao_id = null;
        $this->numeros = [];
        $this->numero_input = '';
    }

    public function adicionarNumero(): void
    {
        $numero = trim($this->numero_input);
        if ($numero === '') {
            return;
        }

        if (! in_array($numero, $this->numeros, true)) {
            $this->numeros[] = $numero;
        }

        $this->numero_input = '';
    }

    public function removerNumero(int $index): void
    {
        unset($this->numeros[$index]);
        $this->numeros = array_values($this->numeros);
    }

    public function enviarEnquete(): void
    {
        $this->validate($this->regrasEnvio(), $this->mensagensEnvio());

        $service = app(EnqueteService::class);

        $resultado = match ($this->tipo_destino) {
            EnqueteService::DESTINO_GRUPO => $service->enviarDestino($this->record, $this->tipo_destino, [
                'grupo_id' => (int) $this->notificacao_grupo_id,
            ]),
            EnqueteService::DESTINO_INSCRITO => $service->enviarDestino($this->record, $this->tipo_destino, [
                'inscricao_id' => (int) $this->inscricao_id,
            ]),
            EnqueteService::DESTINO_NUMEROS => $service->enviarDestino($this->record, $this->tipo_destino, [
                'numeros' => $this->numeros,
            ]),
            default => ['ok' => 0, 'erro' => 0, 'mensagem' => null],
        };

        if (! empty($resultado['mensagem'])) {
            Notification::make()
                ->title($resultado['mensagem'])
                ->danger()
                ->send();

            return;
        }

        if ($resultado['ok'] === 0) {
            $erro = app(\App\Services\WhatsAppService::class)->obterUltimoErro();
            Notification::make()
                ->title($erro ?: 'Nenhuma mensagem enviada. Verifique o destino e os números.')
                ->danger()
                ->send();

            return;
        }

        $msg = "Enviado com sucesso para {$resultado['ok']} destinatário(s).";
        if ($resultado['erro'] > 0) {
            $msg .= " Falhas: {$resultado['erro']}.";
        }

        Notification::make()->title($msg)->success()->send();
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'envios' => $this->record->envios()->latest()->limit(50)->get(),
            'respostas' => $this->record->respostas()->latest()->limit(50)->get(),
            'grupos' => NotificacaoGrupo::query()->orderBy('nome')->get(),
            'inscritos' => Inscricao::query()
                ->with('igrejaRel')
                ->whereNotNull('whatsapp')
                ->where('whatsapp', '!=', '')
                ->orderBy('nome')
                ->get(),
            'destinoOptions' => EnqueteService::destinoOptions(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /** @return array<string, mixed> */
    private function regrasEnvio(): array
    {
        $regras = [
            'tipo_destino' => ['required', 'in:'.implode(',', [
                EnqueteService::DESTINO_GRUPO,
                EnqueteService::DESTINO_INSCRITO,
                EnqueteService::DESTINO_NUMEROS,
            ])],
        ];

        if ($this->tipo_destino === EnqueteService::DESTINO_GRUPO) {
            $regras['notificacao_grupo_id'] = ['required', 'integer', 'exists:notificacao_grupos,id'];
        }

        if ($this->tipo_destino === EnqueteService::DESTINO_INSCRITO) {
            $regras['inscricao_id'] = ['required', 'integer', 'exists:inscricoes,id'];
        }

        if ($this->tipo_destino === EnqueteService::DESTINO_NUMEROS) {
            $regras['numeros'] = ['required', 'array', 'min:1'];
            $regras['numeros.*'] = ['required', 'string', 'min:8'];
        }

        return $regras;
    }

    /** @return array<string, string> */
    private function mensagensEnvio(): array
    {
        return [
            'notificacao_grupo_id.required' => 'Selecione um grupo.',
            'inscricao_id.required' => 'Selecione um inscrito.',
            'numeros.required' => 'Adicione pelo menos um número.',
            'numeros.min' => 'Adicione pelo menos um número.',
        ];
    }
}
