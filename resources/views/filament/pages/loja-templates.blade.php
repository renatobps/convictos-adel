<x-filament-panels::page>
    {{ $this->templatesForm }}

    <div class="mt-4">
        <x-filament::button wire:click="salvarTemplates" wire:loading.attr="disabled" icon="heroicon-o-check">
            <span wire:loading.remove wire:target="salvarTemplates">Salvar templates</span>
            <span wire:loading wire:target="salvarTemplates">Salvando...</span>
        </x-filament::button>
    </div>
</x-filament-panels::page>
