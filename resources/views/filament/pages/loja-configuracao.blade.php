<x-filament-panels::page>
    {{ $this->configForm }}

    <div class="mt-4">
        <x-filament::button wire:click="salvarConfig" wire:loading.attr="disabled" icon="heroicon-o-check">
            <span wire:loading.remove wire:target="salvarConfig">Salvar configuração</span>
            <span wire:loading wire:target="salvarConfig">Salvando...</span>
        </x-filament::button>
    </div>
</x-filament-panels::page>
