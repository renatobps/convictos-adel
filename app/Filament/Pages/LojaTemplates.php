<?php

namespace App\Filament\Pages;

use App\Services\AtividadeLogService;
use App\Support\LojaEmailConfig;
use App\Support\LojaNotificacaoConfig;
use App\Support\LojaTemplatePlaceholders;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class LojaTemplates extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Templates';

    protected static ?string $title = 'Templates da loja';

    protected static ?int $navigationSort = 5;

    protected static string|\UnitEnum|null $navigationGroup = 'Loja';

    protected string $view = 'filament.pages.loja-templates';

    public ?array $templatesData = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        foreach (LojaNotificacaoConfig::eventos() as $evento => $label) {
            $this->templatesData[$evento] = [
                'whatsapp' => LojaNotificacaoConfig::templateParaFormulario($evento),
                'email' => LojaEmailConfig::templateParaFormulario($evento),
            ];
        }
    }

    public function templatesForm(Schema $schema): Schema
    {
        $sections = [];

        foreach (LojaNotificacaoConfig::eventos() as $evento => $label) {
            $sections[] = Section::make($label)
                ->schema([
                    Section::make('WhatsApp')
                        ->schema([
                            Textarea::make("templatesData.{$evento}.whatsapp.mensagem")
                                ->label('Mensagem')
                                ->rows(8)
                                ->required()
                                ->columnSpanFull(),
                            Toggle::make("templatesData.{$evento}.whatsapp.enviar_imagem_produto")
                                ->label('Enviar imagem dos produtos do pedido'),
                            Toggle::make("templatesData.{$evento}.whatsapp.enviar_localizacao")
                                ->label('Enviar localização da CADEL no WhatsApp'),
                            TextInput::make("templatesData.{$evento}.whatsapp.imagem_url")
                                ->label('URL de imagem extra (banner, opcional)')
                                ->url()
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('E-mail')
                        ->schema([
                            Toggle::make("templatesData.{$evento}.email.ativo")
                                ->label('Enviar este e-mail automaticamente')
                                ->columnSpanFull(),
                            TextInput::make("templatesData.{$evento}.email.assunto")
                                ->label('Assunto')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            RichEditor::make("templatesData.{$evento}.email.conteudo")
                                ->label('Conteúdo')
                                ->required()
                                ->columnSpanFull(),
                            FileUpload::make("templatesData.{$evento}.email.imagem")
                                ->label('Banner no topo do e-mail (opcional)')
                                ->image()
                                ->disk('public')
                                ->directory('emails/loja')
                                ->visibility('public')
                                ->maxSize(4096)
                                ->nullable()
                                ->columnSpanFull(),
                            Toggle::make("templatesData.{$evento}.email.enviar_imagem_produto")
                                ->label('Incluir imagens dos produtos no e-mail'),
                            TextInput::make("templatesData.{$evento}.email.botao_texto")
                                ->label('Texto do botão (opcional)')
                                ->maxLength(60),
                            TextInput::make("templatesData.{$evento}.email.botao_url")
                                ->label('Link do botão (opcional)')
                                ->url(),
                        ])
                        ->columns(2),
                ])
                ->collapsible()
                ->collapsed($evento !== LojaNotificacaoConfig::CLIENTE_EM_SEPARACAO);
        }

        return $schema
            ->components([
                Form::make([
                    Section::make('Placeholders disponíveis')
                        ->description(LojaTemplatePlaceholders::textoPlaceholders())
                        ->schema([]),
                    ...$sections,
                ]),
            ]);
    }

    public function salvarTemplates(): void
    {
        foreach (LojaNotificacaoConfig::eventos() as $evento => $label) {
            $dados = (array) ($this->templatesData[$evento] ?? []);

            LojaNotificacaoConfig::salvarTemplate($evento, (array) ($dados['whatsapp'] ?? []));
            LojaEmailConfig::salvarTemplate($evento, (array) ($dados['email'] ?? []));
        }

        $this->mount();

        AtividadeLogService::registrar(
            'Salvou templates de WhatsApp e e-mail da loja',
            AtividadeLogService::ACAO_CONFIG,
        );

        Notification::make()->title('Templates salvos com sucesso.')->success()->send();
    }
}
