<?php

namespace App\Filament\Pages;

use App\Services\AtividadeLogService;
use App\Support\LojaRetiradaConfig;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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

class LojaConfiguracao extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configuração';

    protected static ?string $title = 'Configuração da loja';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = 'Loja';

    protected string $view = 'filament.pages.loja-configuracao';

    public ?array $configData = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->configData = LojaRetiradaConfig::paraFormulario();
    }

    public function configForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Retirada de produtos')
                        ->description('Informações exibidas no checkout. Todos os produtos são retirados no local indicado.')
                        ->schema([
                            TextInput::make('local')
                                ->label('Local de retirada')
                                ->required()
                                ->maxLength(255)
                                ->default('Catedral')
                                ->columnSpanFull(),
                            Textarea::make('instrucoes')
                                ->label('Instruções para o cliente')
                                ->rows(3)
                                ->maxLength(1000)
                                ->helperText('Texto exibido no checkout junto aos horários de retirada.')
                                ->columnSpanFull(),
                            Repeater::make('horarios')
                                ->label('Dias e horários de retirada')
                                ->schema([
                                    Select::make('dia')
                                        ->label('Dia da semana')
                                        ->options(LojaRetiradaConfig::diasSemana())
                                        ->required()
                                        ->native(false),
                                    TextInput::make('inicio')
                                        ->label('Início')
                                        ->placeholder('09:00')
                                        ->required()
                                        ->regex('/^\d{2}:\d{2}$/')
                                        ->validationMessages([
                                            'regex' => 'Use o formato HH:MM (ex.: 09:00).',
                                        ]),
                                    TextInput::make('fim')
                                        ->label('Fim')
                                        ->placeholder('18:00')
                                        ->required()
                                        ->regex('/^\d{2}:\d{2}$/')
                                        ->validationMessages([
                                            'regex' => 'Use o formato HH:MM (ex.: 18:00).',
                                        ]),
                                    Toggle::make('ativo')
                                        ->label('Ativo')
                                        ->default(true),
                                ])
                                ->columns(4)
                                ->addActionLabel('Adicionar horário')
                                ->reorderable(false)
                                ->columnSpanFull(),
                        ]),
                    Section::make('Localização da CADEL (WhatsApp)')
                        ->description('Enviada automaticamente nos templates com "Enviar localização" ativo.')
                        ->schema([
                            TextInput::make('localizacao_nome')
                                ->label('Nome do local')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Textarea::make('localizacao_endereco')
                                ->label('Endereço')
                                ->rows(2)
                                ->maxLength(500)
                                ->columnSpanFull(),
                            TextInput::make('localizacao_latitude')
                                ->label('Latitude')
                                ->numeric()
                                ->step('0.0000001')
                                ->required(),
                            TextInput::make('localizacao_longitude')
                                ->label('Longitude')
                                ->numeric()
                                ->step('0.0000001')
                                ->required(),
                        ])
                        ->columns(2),
                ])
                    ->statePath('configData'),
            ]);
    }

    public function salvarConfig(): void
    {
        $state = $this->configForm->getState();
        $data = (array) data_get($state, 'configData', $state);

        $horarios = $data['horarios'] ?? [];
        foreach ($horarios as $item) {
            $inicio = (string) ($item['inicio'] ?? '');
            $fim = (string) ($item['fim'] ?? '');

            if ($inicio !== '' && $fim !== '' && $inicio >= $fim) {
                Notification::make()
                    ->title('O horário de início deve ser anterior ao horário de fim.')
                    ->danger()
                    ->send();

                return;
            }
        }

        LojaRetiradaConfig::salvar($data);

        AtividadeLogService::registrar(
            'Salvou configuração de retirada da loja ('.LojaRetiradaConfig::local().')',
            AtividadeLogService::ACAO_CONFIG,
        );

        Notification::make()->title('Configuração salva com sucesso.')->success()->send();
    }
}
