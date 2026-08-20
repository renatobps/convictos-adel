<?php

namespace App\Filament\Pages;

use App\Models\Regional;
use App\Services\AtividadeLogService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConfiguracaoInscricao extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $title = 'Configurações da inscrição';

    protected static ?int $navigationSort = 10;

    protected static string|\UnitEnum|null $navigationGroup = 'Conferência';

    protected string $view = 'filament.pages.configuracao-inscricao';

    public ?array $metasData = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $config = DB::table('inscricao_meta_configuracoes')->first();
        $metasRegionais = DB::table('inscricao_meta_regionais')->pluck('meta', 'regional_id');

        $valorCom = (float) ($config?->valor_com_camiseta ?? $config?->valor_inscricao ?? 0);
        $valorSem = (float) ($config?->valor_sem_camiseta ?? 0);

        $this->metasData = [
            'meta_total' => (int) ($config?->meta_total ?? 500),
            'valor_com_camiseta' => $valorCom,
            'valor_sem_camiseta' => $valorSem,
            'data_evento' => $config?->data_evento ?? null,
            'metas_regionais' => Regional::query()
                ->orderBy('nome')
                ->get()
                ->mapWithKeys(fn (Regional $r) => [$r->id => (int) ($metasRegionais[$r->id] ?? 0)])
                ->all(),
        ];
    }

    public function metasForm(Schema $schema): Schema
    {
        $regionalFields = Regional::query()
            ->orderBy('nome')
            ->get()
            ->map(fn (Regional $regional) => TextInput::make('metas_regionais.'.$regional->id)
                ->label('Meta — '.$regional->nome)
                ->numeric()
                ->minValue(0)
                ->default(0))
            ->all();

        return $schema
            ->components([
                Form::make([
                    Section::make('Metas gerais')
                        ->schema([
                            TextInput::make('meta_total')
                                ->label('Meta total de inscrições')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                            DatePicker::make('data_evento')
                                ->label('Data do evento')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->closeOnDateSelection()
                                ->helperText('Usada no contador regressivo da página inicial.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('Valores da inscrição')
                        ->description('Valores exibidos no formulário público e usados no cálculo de arrecadação.')
                        ->schema([
                            TextInput::make('valor_com_camiseta')
                                ->label('Valor com camiseta (R$)')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01)
                                ->required(),
                            TextInput::make('valor_sem_camiseta')
                                ->label('Valor sem camiseta (R$)')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01)
                                ->required(),
                        ])
                        ->columns(2),
                    Section::make('Metas por regional')
                        ->schema($regionalFields)
                        ->columns(2),
                ])
                    ->statePath('metasData'),
            ]);
    }

    public function salvarMetas(): void
    {
        $state = $this->metasForm->getState();
        $data = (array) data_get($state, 'metasData', $state);

        $valorCom = round((float) $data['valor_com_camiseta'], 2);
        $valorSem = round((float) $data['valor_sem_camiseta'], 2);

        DB::transaction(function () use ($data, $valorCom, $valorSem): void {
            $configAtual = DB::table('inscricao_meta_configuracoes')->first();
            $payload = [
                'meta_total' => (int) $data['meta_total'],
                'valor_inscricao' => $valorCom,
                'valor_com_camiseta' => $valorCom,
                'valor_sem_camiseta' => $valorSem,
                'data_evento' => $data['data_evento'] ?: null,
                'updated_at' => now(),
            ];

            if ($configAtual) {
                DB::table('inscricao_meta_configuracoes')
                    ->where('id', $configAtual->id)
                    ->update($payload);
            } else {
                DB::table('inscricao_meta_configuracoes')->insert([
                    ...$payload,
                    'created_at' => now(),
                ]);
            }

            foreach ($data['metas_regionais'] ?? [] as $regionalId => $meta) {
                DB::table('inscricao_meta_regionais')->updateOrInsert(
                    ['regional_id' => (int) $regionalId],
                    [
                        'meta' => (int) ($meta ?? 0),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        AtividadeLogService::registrar(
            'Salvou metas da inscrição (total: '.(int) $data['meta_total']
                .', com camiseta: R$ '.number_format($valorCom, 2, ',', '.')
                .', sem camiseta: R$ '.number_format($valorSem, 2, ',', '.').')',
            AtividadeLogService::ACAO_CONFIG,
        );

        Notification::make()->title('Metas salvas com sucesso.')->success()->send();
    }
}
