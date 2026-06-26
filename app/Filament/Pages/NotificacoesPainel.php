<?php

namespace App\Filament\Pages;

use App\Models\NotificacaoEnviada;
use App\Models\NotificacaoGrupo;
use App\Services\AtividadeLogService;
use App\Services\NotificacaoService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithPagination;

class NotificacoesPainel extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $navigationLabel = 'Painel';

    protected static ?string $title = 'Painel de notificações';

    protected static ?int $navigationSort = 3;

    protected static string|\UnitEnum|null $navigationGroup = 'Notificações';

    protected string $view = 'filament.pages.notificacoes-painel';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?array $grupoData = [];

    public ?array $manualData = [];

    public string $statusHistorico = 'todos';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->grupoData = [
            'notificacao_grupo_id' => null,
            'mensagem' => '',
            'arquivo' => null,
        ];

        $this->manualData = [
            'numero' => '',
            'mensagem' => '',
            'arquivo' => null,
        ];
    }

    public function grupoForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Envio por grupo')
                        ->description('Grupos por igreja, regional ou inscritos.')
                        ->schema([
                            Select::make('notificacao_grupo_id')
                                ->label('Grupo')
                                ->options(fn () => NotificacaoGrupo::query()->orderBy('nome')->pluck('nome', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                            Textarea::make('mensagem')
                                ->label('Mensagem')
                                ->rows(5)
                                ->placeholder('Placeholders: {nome_do_inscrito}, {tamanho_camiseta}')
                                ->required(fn (Get $get): bool => blank($get('arquivo'))),
                            FileUpload::make('arquivo')
                                ->label('Anexo (opcional)')
                                ->disk('public')
                                ->directory('notificacoes/midias')
                                ->maxSize(20480)
                                ->acceptedFileTypes([
                                    'image/*',
                                    'video/*',
                                    'audio/*',
                                    'application/pdf',
                                ]),
                        ]),
                ])
                    ->id('grupoForm')
                    ->statePath('grupoData')
                    ->livewireSubmitHandler('enviarGrupo')
                    ->footer([
                        Actions::make([
                            \Filament\Actions\Action::make('enviarGrupo')
                                ->label('Enviar para o grupo')
                                ->submit('grupoForm'),
                        ]),
                    ]),
            ]);
    }

    public function manualForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Envio manual')
                        ->schema([
                            TextInput::make('numero')
                                ->label('Número (DDD + número)')
                                ->placeholder('61993640457')
                                ->required(),
                            Textarea::make('mensagem')
                                ->label('Mensagem')
                                ->rows(5)
                                ->required(fn (Get $get): bool => blank($get('arquivo'))),
                            FileUpload::make('arquivo')
                                ->label('Anexo (opcional)')
                                ->disk('public')
                                ->directory('notificacoes/midias')
                                ->maxSize(20480)
                                ->acceptedFileTypes([
                                    'image/*',
                                    'video/*',
                                    'audio/*',
                                    'application/pdf',
                                ]),
                        ]),
                ])
                    ->id('manualForm')
                    ->statePath('manualData')
                    ->livewireSubmitHandler('enviarManual')
                    ->footer([
                        Actions::make([
                            \Filament\Actions\Action::make('enviarManual')
                                ->label('Enviar mensagem')
                                ->submit('manualForm'),
                        ]),
                    ]),
            ]);
    }

    public function enviarGrupo(): void
    {
        $this->validate([
            'grupoData.notificacao_grupo_id' => ['required', 'integer', 'exists:notificacao_grupos,id'],
            'grupoData.mensagem' => ['nullable', 'string', 'required_without:grupoData.arquivo'],
            'grupoData.arquivo' => ['nullable', 'required_without:grupoData.mensagem'],
        ], [
            'grupoData.mensagem.required_without' => 'Informe a mensagem ou anexe um arquivo.',
            'grupoData.arquivo.required_without' => 'Informe a mensagem ou anexe um arquivo.',
        ]);

        $grupo = NotificacaoGrupo::query()->find($this->grupoData['notificacao_grupo_id'] ?? null);
        if ($grupo === null) {
            Notification::make()->title('Selecione um grupo.')->danger()->send();

            return;
        }

        $arquivo = $this->resolverUpload($this->grupoData['arquivo'] ?? null);
        if ($arquivo === null && filled($this->grupoData['arquivo'] ?? null)) {
            Notification::make()
                ->title('Aguarde o upload do anexo terminar e tente novamente.')
                ->danger()
                ->send();

            return;
        }

        $resultado = app(NotificacaoService::class)->enviarParaGrupo(
            $grupo,
            trim($this->grupoData['mensagem'] ?? ''),
            $arquivo,
        );

        if (! empty($resultado['mensagem'])) {
            Notification::make()->title($resultado['mensagem'])->danger()->send();

            return;
        }

        if ($resultado['ok'] === 0) {
            $erro = app(\App\Services\WhatsAppService::class)->obterUltimoErro()
                ?: 'Nenhuma mensagem enviada.';
            Notification::make()->title($erro)->danger()->send();

            return;
        }

        $msg = "Envio concluído. Sucesso: {$resultado['ok']}.";
        if ($resultado['erro'] > 0) {
            $msg .= " Falhas: {$resultado['erro']}.";
        }

        AtividadeLogService::registrar(
            "Enviou notificação WhatsApp para o grupo \"{$grupo->nome}\" ({$resultado['ok']} enviadas, {$resultado['erro']} falhas)",
            AtividadeLogService::ACAO_NOTIFICACAO,
            $grupo,
        );

        Notification::make()->title($msg)->success()->send();
    }

    public function enviarManual(): void
    {
        $this->validate([
            'manualData.numero' => ['required', 'string', 'min:8'],
            'manualData.mensagem' => ['nullable', 'string', 'required_without:manualData.arquivo'],
            'manualData.arquivo' => ['nullable', 'required_without:manualData.mensagem'],
        ], [
            'manualData.mensagem.required_without' => 'Informe a mensagem ou anexe um arquivo.',
            'manualData.arquivo.required_without' => 'Informe a mensagem ou anexe um arquivo.',
        ]);

        $arquivo = $this->resolverUpload($this->manualData['arquivo'] ?? null);
        if ($arquivo === null && filled($this->manualData['arquivo'] ?? null)) {
            Notification::make()
                ->title('Aguarde o upload do anexo terminar e tente novamente.')
                ->danger()
                ->send();

            return;
        }

        $ok = app(NotificacaoService::class)->enviarManual(
            $this->manualData['numero'] ?? '',
            trim($this->manualData['mensagem'] ?? ''),
            $arquivo,
        );

        if ($ok) {
            AtividadeLogService::registrar(
                'Enviou mensagem WhatsApp manual para '.$this->manualData['numero'],
                AtividadeLogService::ACAO_NOTIFICACAO,
            );
            Notification::make()->title('Mensagem enviada.')->success()->send();
        } else {
            $erro = app(\App\Services\WhatsAppService::class)->obterUltimoErro()
                ?: 'Falha ao enviar mensagem.';
            Notification::make()->title($erro)->danger()->send();
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $query = NotificacaoEnviada::query()->with('grupo')->latest();

        if ($this->statusHistorico !== 'todos') {
            $query->where('status', $this->statusHistorico);
        }

        return [
            'historico' => $query->paginate(15),
        ];
    }

    private function resolverUpload(mixed $arquivo): ?\Illuminate\Http\UploadedFile
    {
        if ($arquivo instanceof TemporaryUploadedFile) {
            return $arquivo;
        }

        if (is_string($arquivo) && $arquivo !== '') {
            return $this->uploadedFileFromPath($arquivo);
        }

        if (! is_array($arquivo) || empty($arquivo)) {
            return null;
        }

        $first = Arr::first($arquivo);

        if ($first instanceof TemporaryUploadedFile) {
            return $first;
        }

        if (is_string($first) && $first !== '') {
            return $this->uploadedFileFromPath($first);
        }

        return null;
    }

    private function uploadedFileFromPath(string $storedPath): ?\Illuminate\Http\UploadedFile
    {
        if ($storedPath === '') {
            return null;
        }

        $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($storedPath);
        if (! is_file($fullPath)) {
            return null;
        }

        return new \Illuminate\Http\UploadedFile(
            $fullPath,
            basename($fullPath),
            mime_content_type($fullPath) ?: null,
            null,
            true
        );
    }
}
