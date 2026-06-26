<x-filament-panels::page>
    <x-filament::section heading="Acesso regional de líderes" description="Conceda a membros o acesso às inscrições de uma ou mais regionais.">
        {{ $this->acessoForm }}

        <div class="mt-4">
            <x-filament::button wire:click="atribuirAcesso" wire:loading.attr="disabled" icon="heroicon-o-user-plus">
                <span wire:loading.remove wire:target="atribuirAcesso">Atribuir acesso regional</span>
                <span wire:loading wire:target="atribuirAcesso">Salvando...</span>
            </x-filament::button>
        </div>
    </x-filament::section>

    @if($membrosComAcesso->isNotEmpty())
        <x-filament::section heading="Líderes regionais">
            <div class="space-y-3">
                @foreach($membrosComAcesso as $membro)
                    <div class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <div>
                            <div class="font-medium">{{ $membro->nome }}</div>
                            <div class="text-sm text-gray-500">{{ $membro->email }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $membro->acessosRegionais->map(fn ($a) => $a->regional?->nome)->filter()->join(', ') }}
                            </div>
                        </div>
                        <x-filament::button color="danger" size="sm" wire:click="removerAcesso({{ $membro->id }})">
                            Remover acesso
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    <x-filament::section heading="Administradores" description="Promova membros a administradores do painel.">
        {{ $this->adminForm }}

        <div class="mt-4">
            <x-filament::button wire:click="promoverAdmin" wire:loading.attr="disabled" icon="heroicon-o-shield-check">
                <span wire:loading.remove wire:target="promoverAdmin">Promover a administrador</span>
                <span wire:loading wire:target="promoverAdmin">Salvando...</span>
            </x-filament::button>
        </div>
    </x-filament::section>

    @if($admins->isNotEmpty())
        <x-filament::section heading="Administradores atuais">
            <div class="space-y-3">
                @foreach($admins as $admin)
                    <div class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                        <div>
                            <div class="font-medium">{{ $admin->name }}</div>
                            <div class="text-sm text-gray-500">{{ $admin->email }}</div>
                        </div>
                        <x-filament::button color="danger" size="sm" wire:click="revogarAdmin({{ $admin->id }})">
                            Revogar admin
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
