<x-filament-panels::page>
    @include('filament.partials.fi-data-table-styles')

    <div>{{ $this->templatesForm }}</div>

    <div class="mt-8">{{ $this->novoTemplateForm }}</div>

    <x-filament::section heading="Templates salvos" class="mt-8">
        @if($templatesDb->isEmpty())
            <p class="fi-data-empty">Nenhum template adicional cadastrado.</p>
        @else
            <div class="fi-data-table-wrap">
                <table class="fi-data-table" style="min-width: 720px;">
                    <thead>
                        <tr>
                            <th class="col-name">Título</th>
                            <th class="col-chave">Chave</th>
                            <th class="col-auto">Conteúdo</th>
                            <th class="col-actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templatesDb as $template)
                            <tr>
                                <td class="col-name" style="font-weight: 600;">{{ $template->titulo }}</td>
                                <td class="col-chave">{{ $template->chave }}</td>
                                <td class="col-auto">{{ \Illuminate\Support\Str::limit($template->conteudo, 100) }}</td>
                                <td class="col-actions">
                                    <button type="button" wire:click="excluirTemplate({{ $template->id }})" class="text-danger-600 text-sm font-semibold">
                                        Excluir
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
