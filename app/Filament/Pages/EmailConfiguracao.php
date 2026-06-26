<?php

namespace App\Filament\Pages;

use App\Mail\InscricaoStatusMail;
use App\Models\Inscricao;
use App\Support\EmailConfig;
use App\Services\AtividadeLogService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailConfiguracao extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Email';

    protected static ?string $title = 'Configuração de e-mails';

    protected static ?int $navigationSort = 6;

    protected static string|\UnitEnum|null $navigationGroup = 'Notificações';

    protected string $view = 'filament.pages.email-configuracao';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?array $realizadaData = [];

    public ?array $confirmadaData = [];

    public ?array $testeData = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->realizadaData = EmailConfig::templateParaFormulario(EmailConfig::TIPO_REALIZADA);
        $this->confirmadaData = EmailConfig::templateParaFormulario(EmailConfig::TIPO_CONFIRMADA);
        $this->testeData = [
            'email' => (string) (Auth::user()?->email ?? ''),
            'tipo' => EmailConfig::TIPO_REALIZADA,
        ];
    }

    public function realizadaForm(Schema $schema): Schema
    {
        return $this->templateSchema($schema, 'realizadaData', 'salvarRealizada', 'E-mail de inscrição realizada', 'realizadaForm');
    }

    public function confirmadaForm(Schema $schema): Schema
    {
        return $this->templateSchema($schema, 'confirmadaData', 'salvarConfirmada', 'E-mail de inscrição confirmada', 'confirmadaForm');
    }

    protected function templateSchema(Schema $schema, string $statePath, string $handler, string $heading, string $formId): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make($heading)
                        ->description('Placeholders disponíveis: {nome_do_inscrito}, {tamanho_camiseta}, {igreja}, {status}, {email}')
                        ->schema([
                            Toggle::make('ativo')
                                ->label('Enviar este e-mail automaticamente')
                                ->columnSpanFull(),
                            TextInput::make('assunto')
                                ->label('Assunto do e-mail')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            RichEditor::make('conteudo')
                                ->label('Texto do e-mail')
                                ->required()
                                ->columnSpanFull(),
                            FileUpload::make('imagem')
                                ->label('Imagem (banner no topo do e-mail)')
                                ->image()
                                ->disk('public')
                                ->directory('emails/inscricao')
                                ->visibility('public')
                                ->maxSize(4096)
                                ->nullable()
                                ->columnSpanFull(),
                            TextInput::make('botao_texto')
                                ->label('Texto do botão (opcional)')
                                ->maxLength(60),
                            TextInput::make('botao_url')
                                ->label('Link do botão (opcional)')
                                ->url(),
                        ])
                        ->columns(2),
                ])
                    ->id($formId)
                    ->statePath($statePath)
                    ->livewireSubmitHandler($handler)
                    ->footer([
                        Actions::make([
                            \Filament\Actions\Action::make($handler)
                                ->label('Salvar e-mail')
                                ->submit($formId),
                        ]),
                    ]),
            ]);
    }

    public function testeForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Enviar e-mail de teste')
                        ->description('Envia um e-mail real (se o modo de envio for SMTP) para o endereço informado.')
                        ->schema([
                            Select::make('tipo')
                                ->label('Modelo')
                                ->options([
                                    EmailConfig::TIPO_REALIZADA => 'Inscrição realizada',
                                    EmailConfig::TIPO_CONFIRMADA => 'Inscrição confirmada',
                                ])
                                ->required(),
                            TextInput::make('email')
                                ->label('Enviar para')
                                ->email()
                                ->required(),
                        ])
                        ->columns(2),
                ])
                    ->id('testeForm')
                    ->statePath('testeData')
                    ->livewireSubmitHandler('enviarTeste')
                    ->footer([
                        Actions::make([
                            \Filament\Actions\Action::make('enviarTeste')
                                ->label('Enviar teste')
                                ->submit('testeForm'),
                        ]),
                    ]),
            ]);
    }

    public function salvarRealizada(): void
    {
        EmailConfig::salvarTemplate(EmailConfig::TIPO_REALIZADA, (array) $this->realizadaData);
        $this->realizadaData = EmailConfig::templateParaFormulario(EmailConfig::TIPO_REALIZADA);
        AtividadeLogService::registrar(
            'Salvou template de e-mail de inscrição realizada',
            AtividadeLogService::ACAO_CONFIG,
        );
        Notification::make()->title('E-mail de inscrição realizada salvo.')->success()->send();
    }

    public function salvarConfirmada(): void
    {
        EmailConfig::salvarTemplate(EmailConfig::TIPO_CONFIRMADA, (array) $this->confirmadaData);
        $this->confirmadaData = EmailConfig::templateParaFormulario(EmailConfig::TIPO_CONFIRMADA);
        AtividadeLogService::registrar(
            'Salvou template de e-mail de inscrição confirmada',
            AtividadeLogService::ACAO_CONFIG,
        );
        Notification::make()->title('E-mail de inscrição confirmada salvo.')->success()->send();
    }

    public function enviarTeste(): void
    {
        $dados = (array) $this->testeData;
        $email = trim((string) ($dados['email'] ?? ''));
        $tipo = (string) ($dados['tipo'] ?? EmailConfig::TIPO_REALIZADA);

        if ($email === '') {
            Notification::make()->title('Informe um e-mail de destino.')->danger()->send();

            return;
        }

        $inscricao = new Inscricao([
            'nome' => Auth::user()?->name ?? 'Inscrito de teste',
            'email' => $email,
            'tamanho_camiseta' => 'M',
            'igreja' => 'Igreja Exemplo',
            'status' => $tipo === EmailConfig::TIPO_CONFIRMADA
                ? Inscricao::STATUS_CONFIRMADA
                : Inscricao::STATUS_AGUARDANDO,
        ]);

        try {
            EmailConfig::aplicarMailer();
            Mail::to($email)->send(new InscricaoStatusMail($inscricao, $tipo));
            AtividadeLogService::registrar(
                "Enviou e-mail de teste ({$tipo}) para {$email}",
                AtividadeLogService::ACAO_NOTIFICACAO,
            );
            Notification::make()->title('E-mail de teste enviado para '.$email.'.')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Falha ao enviar: '.$e->getMessage())->danger()->send();
        }
    }
}
