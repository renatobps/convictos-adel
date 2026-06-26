<?php

namespace App\Filament\Pages;

use App\Models\ConfiguracaoMensagem;
use App\Services\AtividadeLogService;
use App\Support\NotificacaoPosInscricaoConfig;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class NotificacoesTemplates extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Templates';

    protected static ?string $title = 'Templates de mensagens';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = 'Notificações';

    protected string $view = 'filament.pages.notificacoes-templates';

    public ?array $templateData = [];

    public ?array $novoTemplate = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->templateData = [
            'mensagem_pos_inscricao' => NotificacaoPosInscricaoConfig::mensagemPosInscricao(),
            'mensagem_confirmada' => NotificacaoPosInscricaoConfig::mensagemConfirmada(),
            'imagem_pos_inscricao_url' => NotificacaoPosInscricaoConfig::imagemPosInscricaoUrl(),
        ];

        $this->novoTemplate = [
            'chave' => '',
            'titulo' => '',
            'conteudo' => '',
            'imagem_url' => '',
        ];
    }

    public function templatesForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Mensagem pós-inscrição')
                        ->description('Placeholders: {nome_do_inscrito}, {tamanho_camiseta}')
                        ->schema([
                            Textarea::make('mensagem_pos_inscricao')
                                ->label('Mensagem')
                                ->rows(8)
                                ->required(),
                            TextInput::make('imagem_pos_inscricao_url')
                                ->label('URL da imagem (opcional)')
                                ->url()
                                ->columnSpanFull(),
                        ]),
                    Section::make('Mensagem de confirmação')
                        ->schema([
                            Textarea::make('mensagem_confirmada')
                                ->label('Mensagem')
                                ->rows(8)
                                ->required(),
                        ]),
                ])
                    ->statePath('templateData')
                    ->livewireSubmitHandler('salvarTemplates'),
                Actions::make([
                    \Filament\Actions\Action::make('salvarTemplates')
                        ->label('Salvar mensagens automáticas')
                        ->submit('salvarTemplates'),
                ]),
            ]);
    }

    public function novoTemplateForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Novo template para o painel')
                        ->schema([
                            TextInput::make('chave')
                                ->label('Chave')
                                ->required()
                                ->maxLength(100)
                                ->helperText('Identificador único, ex: lembrete_pagamento'),
                            TextInput::make('titulo')
                                ->label('Título')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('conteudo')
                                ->label('Conteúdo')
                                ->rows(6)
                                ->required(),
                            TextInput::make('imagem_url')
                                ->label('URL da imagem (opcional)')
                                ->url(),
                        ]),
                ])
                    ->statePath('novoTemplate')
                    ->livewireSubmitHandler('salvarNovoTemplate'),
                Actions::make([
                    \Filament\Actions\Action::make('salvarNovoTemplate')
                        ->label('Adicionar template')
                        ->submit('salvarNovoTemplate'),
                ]),
            ]);
    }

    public function salvarTemplates(): void
    {
        NotificacaoPosInscricaoConfig::salvarMensagemPosInscricao($this->templateData['mensagem_pos_inscricao']);
        NotificacaoPosInscricaoConfig::salvarMensagemConfirmada($this->templateData['mensagem_confirmada']);
        NotificacaoPosInscricaoConfig::salvarImagemPosInscricaoUrl($this->templateData['imagem_pos_inscricao_url'] ?? '');

        AtividadeLogService::registrar(
            'Salvou mensagens automáticas de WhatsApp (pós-inscrição e confirmada)',
            AtividadeLogService::ACAO_CONFIG,
        );

        Notification::make()->title('Mensagens automáticas salvas.')->success()->send();
    }

    public function salvarNovoTemplate(): void
    {
        ConfiguracaoMensagem::query()->updateOrCreate(
            ['chave' => $this->novoTemplate['chave']],
            [
                'titulo' => $this->novoTemplate['titulo'],
                'conteudo' => $this->novoTemplate['conteudo'],
                'imagem_url' => $this->novoTemplate['imagem_url'] ?: null,
            ],
        );

        $this->novoTemplate = [
            'chave' => '',
            'titulo' => '',
            'conteudo' => '',
            'imagem_url' => '',
        ];

        Notification::make()->title('Template adicionado.')->success()->send();
    }

    public function excluirTemplate(int $id): void
    {
        ConfiguracaoMensagem::query()->whereKey($id)->delete();
        Notification::make()->title('Template removido.')->success()->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'templatesDb' => ConfiguracaoMensagem::query()->orderBy('titulo')->get(),
        ];
    }
}
