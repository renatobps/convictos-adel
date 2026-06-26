<x-filament-panels::page>
    {{ $this->metasForm }}

    <div class="mt-4">
        <x-filament::button wire:click="salvarMetas" wire:loading.attr="disabled" icon="heroicon-o-check">
            <span wire:loading.remove wire:target="salvarMetas">Salvar metas</span>
            <span wire:loading wire:target="salvarMetas">Salvando...</span>
        </x-filament::button>
    </div>
</x-filament-panels::page>
