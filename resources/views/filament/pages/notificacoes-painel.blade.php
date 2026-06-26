<x-filament-panels::page>
    @php($historico = $historico ?? collect())

    @include('filament.partials.fi-data-table-styles')

    <div class="grid gap-8 lg:grid-cols-2">
        <div>{{ $this->grupoForm }}</div>
        <div>{{ $this->manualForm }}</div>
    </div>

    <x-filament::section heading="Histórico de envios" class="mt-8">
        <div class="fi-data-filter">
            <select wire:model.live="statusHistorico" aria-label="Filtrar por status">
                <option value="todos">Todos os status</option>
                <option value="enviada">Enviadas</option>
                <option value="erro">Erros</option>
            </select>
        </div>

        @if($historico->isEmpty())
            <p class="fi-data-empty">Nenhum registro encontrado.</p>
        @else
            <div class="fi-data-table-wrap">
                <table class="fi-data-table" style="min-width: 760px;">
                    <thead>
                        <tr>
                            <th class="col-date">Data</th>
                            <th class="col-phone">Destinatário</th>
                            <th class="col-grupo">Grupo</th>
                            <th class="col-tipo">Tipo</th>
                            <th class="col-status">Status</th>
                            <th class="col-auto">Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historico as $item)
                            <tr>
                                <td class="col-date">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="col-phone">{{ $item->destinatario }}</td>
                                <td class="col-grupo">{{ $item->grupo?->nome ?? '—' }}</td>
                                <td class="col-tipo">
                                    <span class="fi-data-badge fi-data-badge--info">
                                        {{ ucfirst($item->tipo_envio ?? '—') }}
                                    </span>
                                </td>
                                <td class="col-status">
                                    <span @class([
                                        'fi-data-badge',
                                        'fi-data-badge--ok' => $item->status === 'enviada',
                                        'fi-data-badge--erro' => $item->status === 'erro',
                                    ])>
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="col-auto">{{ $item->mensagem ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="fi-data-pagination">{{ $historico->links() }}</div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
