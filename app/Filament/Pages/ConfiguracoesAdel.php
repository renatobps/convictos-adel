<?php

namespace App\Filament\Pages;

use App\Models\Membro;
use App\Models\MembroAcessoRegional;
use App\Models\Regional;
use App\Models\User;
use App\Services\AtividadeLogService;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ConfiguracoesAdel extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $title = 'Configurações — Política de acessos';

    protected static ?int $navigationSort = 90;

    protected static string|\UnitEnum|null $navigationGroup = 'ADEL';

    protected string $view = 'filament.pages.configuracoes-adel';

    public ?array $acessoData = [];

    public ?array $adminData = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->acessoData = [
            'membro_id' => null,
            'regional_ids' => [],
        ];

        $this->adminData = [
            'membro_id_admin' => null,
        ];
    }

    public function acessoForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Select::make('membro_id')
                        ->label('Membro')
                        ->options(fn () => Membro::query()->orderBy('nome')->pluck('nome', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('regional_ids')
                        ->label('Regionais com acesso')
                        ->options(fn () => Regional::query()->orderBy('nome')->pluck('nome', 'id'))
                        ->multiple()
                        ->required(),
                ])
                    ->statePath('acessoData'),
            ]);
    }

    public function adminForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Select::make('membro_id_admin')
                        ->label('Membro administrador')
                        ->options(fn () => Membro::query()->orderBy('nome')->pluck('nome', 'id'))
                        ->searchable()
                        ->required()
                        ->helperText('O login do painel usará o e-mail e a senha cadastrados no membro.'),
                ])
                    ->statePath('adminData'),
            ]);
    }

    public function atribuirAcesso(): void
    {
        $state = $this->acessoForm->getState();
        $data = (array) data_get($state, 'acessoData', $state);
        $membro = Membro::query()->findOrFail($data['membro_id']);

        if (! $membro->possuiCredenciais()) {
            Notification::make()
                ->title('O membro precisa ter e-mail e senha cadastrados.')
                ->body('Edite o membro em ADEL → Membros e defina uma senha antes de atribuir acesso.')
                ->danger()
                ->send();

            return;
        }

        try {
            $membro->sincronizarUsuario();
        } catch (\Throwable $e) {
            Notification::make()->title('Não foi possível sincronizar o usuário.')->danger()->send();

            return;
        }

        $regionalIds = array_values(array_unique(array_map('intval', $data['regional_ids'] ?? [])));

        MembroAcessoRegional::withoutEvents(function () use ($membro, $regionalIds): void {
            MembroAcessoRegional::query()->where('membro_id', $membro->id)->delete();

            foreach ($regionalIds as $regionalId) {
                MembroAcessoRegional::query()->create([
                    'membro_id' => $membro->id,
                    'regional_id' => $regionalId,
                ]);
            }
        });

        $regionais = Regional::query()->whereIn('id', $regionalIds)->orderBy('nome')->pluck('nome')->join(', ');

        AtividadeLogService::registrar(
            "Atribuiu acessos regionais ({$regionais}) para {$membro->nome}",
            AtividadeLogService::ACAO_CONFIG,
            $membro,
        );

        $this->acessoData = ['membro_id' => null, 'regional_ids' => []];

        Notification::make()
            ->title('Acessos regionais atribuídos.')
            ->body('Login: '.$membro->email.' — use a mesma senha cadastrada no membro.')
            ->success()
            ->send();
    }

    public function promoverAdmin(): void
    {
        $state = $this->adminForm->getState();
        $data = (array) data_get($state, 'adminData', $state);
        $membro = Membro::query()->findOrFail($data['membro_id_admin']);

        if (! $membro->possuiCredenciais()) {
            Notification::make()
                ->title('O membro precisa ter e-mail e senha cadastrados.')
                ->body('Edite o membro em ADEL → Membros e defina uma senha antes de promover.')
                ->danger()
                ->send();

            return;
        }

        try {
            $membro->sincronizarUsuario(promoverAdmin: true);
        } catch (\Throwable $e) {
            Notification::make()->title('Não foi possível promover o administrador.')->danger()->send();

            return;
        }

        Notification::make()
            ->title('Administrador promovido com sucesso.')
            ->body('Login: '.$membro->email.' — use a mesma senha cadastrada no membro.')
            ->success()
            ->send();
    }

    public function removerAcesso(int $membroId): void
    {
        $membro = Membro::query()->find($membroId);

        MembroAcessoRegional::withoutEvents(function () use ($membroId): void {
            MembroAcessoRegional::query()->where('membro_id', $membroId)->delete();
        });

        AtividadeLogService::registrar(
            'Removeu todos os acessos regionais de '.($membro?->nome ?? '#'.$membroId),
            AtividadeLogService::ACAO_CONFIG,
            $membro,
        );

        Notification::make()->title('Acesso regional removido.')->success()->send();
    }

    public function revogarAdmin(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if (Auth::id() === $user->id) {
            Notification::make()->title('Não é possível revogar seu próprio acesso.')->danger()->send();

            return;
        }

        $user->update(['is_admin' => false]);
        Notification::make()->title('Acesso de administrador removido.')->success()->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'membrosComAcesso' => Membro::query()
                ->with(['cargo', 'acessosRegionais.regional'])
                ->whereHas('acessosRegionais')
                ->orderBy('nome')
                ->get(),
            'admins' => User::query()->where('is_admin', true)->orderBy('name')->get(),
        ];
    }
}
