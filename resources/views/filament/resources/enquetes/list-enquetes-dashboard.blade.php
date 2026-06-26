@php
    $enquete = $this->enqueteSelecionada();
    $stats = $this->dashboardStats();
    $respostas = $this->dashboardRespostas();
@endphp

@include('filament.partials.fi-data-table-styles')

<style>
    .enq-dash {
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        padding: 1.25rem;
    }

    .dark .enq-dash {
        background: rgb(24 24 27);
        border-color: rgb(63 63 70);
    }

    .enq-dash__header {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .enq-dash__title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .enq-dash__filter {
        min-width: 220px;
        flex: 1 1 220px;
        max-width: 420px;
    }

    .enq-dash__filter label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }

    .enq-dash__filter select {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        background: #fff;
    }

    .dark .enq-dash__filter select {
        background: rgb(39 39 42);
        border-color: rgb(63 63 70);
        color: #f4f4f5;
    }

    .enq-dash__info {
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 8px;
        background: #f9fafb;
        border: 1px solid #f3f4f6;
    }

    .dark .enq-dash__info {
        background: rgb(39 39 42);
        border-color: rgb(63 63 70);
    }

    .enq-dash__info-title {
        margin: 0 0 0.25rem;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .enq-dash__info-question {
        margin: 0;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .enq-metrics {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.65rem;
        margin-bottom: 1rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .enq-metric-card {
        flex: 1 1 0;
        min-width: 88px;
        border-radius: 8px;
        padding: 0.65rem 0.75rem;
        white-space: nowrap;
    }

    .enq-metric-card__label {
        font-size: 0.72rem;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .enq-metric-card__value {
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .enq-metric-card--total { background: #f0fdf4; border: 1px solid #bbf7d0; }
    .enq-metric-card--total .enq-metric-card__label { color: #166534; }
    .enq-metric-card--total .enq-metric-card__value { color: #14532d; }

    .enq-metric-card--envios { background: #eff6ff; border: 1px solid #bfdbfe; }
    .enq-metric-card--envios .enq-metric-card__label { color: #1e40af; }
    .enq-metric-card--envios .enq-metric-card__value { color: #1e3a8a; }

    .enq-metric-card--taxa { background: #faf5ff; border: 1px solid #e9d5ff; }
    .enq-metric-card--taxa .enq-metric-card__label { color: #7e22ce; }
    .enq-metric-card--taxa .enq-metric-card__value { color: #581c87; }

    .enq-metric-card--opcao { background: #fff; border: 1px solid #e5e7eb; }
    .enq-metric-card--opcao .enq-metric-card__label { color: #6b7280; }
    .dark .enq-metric-card--opcao { background: rgb(24 24 27); border-color: rgb(63 63 70); }

    .enq-table-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .enq-table-wrap .fi-data-table.enq-data-table {
        min-width: 580px;
        table-layout: auto;
    }

    .enq-table-wrap .fi-data-table.enq-data-table th,
    .enq-table-wrap .fi-data-table.enq-data-table td {
        word-break: keep-all;
        overflow-wrap: normal;
        white-space: nowrap;
    }

    .enq-pagination {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: center;
        justify-content: space-between;
        margin-top: 1rem;
        font-size: 0.8125rem;
        color: #6b7280;
    }

    .enq-pagination__actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .enq-pagination__btn {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        border-radius: 6px;
        padding: 0.4rem 0.75rem;
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
    }

    .enq-pagination__btn:disabled { opacity: 0.45; cursor: not-allowed; }

    .enq-mobile-cards { display: none; }

    .enq-mobile-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem 0.85rem;
        background: #fff;
    }

    .enq-mobile-card + .enq-mobile-card { margin-top: 0.65rem; }

    .enq-mobile-card__row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.35rem 0;
        font-size: 0.8125rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .enq-mobile-card__row:last-child { border-bottom: 0; }

    .enq-mobile-card__label {
        color: #6b7280;
        font-weight: 600;
        flex-shrink: 0;
    }

    .enq-mobile-card__value {
        text-align: right;
        white-space: nowrap;
    }

    @media (max-width: 639px) {
        .enq-table-wrap { display: none; }
        .enq-mobile-cards { display: block; }
    }
</style>

<section class="enq-dash">
    <div class="enq-dash__header">
        <h2 class="enq-dash__title">Dashboard</h2>
        <div class="enq-dash__filter">
            <label for="dashboard_enquete_id">Filtrar por enquete</label>
            <select id="dashboard_enquete_id" wire:model.live="dashboardEnqueteId">
                @forelse($this->enquetesParaFiltro() as $item)
                    <option value="{{ $item->id }}">
                        {{ $item->titulo }}@unless($item->ativa) (inativa)@endunless
                    </option>
                @empty
                    <option value="">Nenhuma enquete cadastrada</option>
                @endforelse
            </select>
        </div>
    </div>

    @if($enquete === null)
        <p class="fi-data-empty">Selecione uma enquete para visualizar os dados.</p>
    @else
        <div class="enq-dash__info">
            <p class="enq-dash__info-title">{{ $enquete->titulo }}</p>
            <p class="enq-dash__info-question">{{ $enquete->pergunta }}</p>
        </div>

        <div class="enq-metrics">
            <div class="enq-metric-card enq-metric-card--total">
                <div class="enq-metric-card__label">Total de respostas</div>
                <div class="enq-metric-card__value">{{ $stats['totalRespostas'] }}</div>
            </div>
            <div class="enq-metric-card enq-metric-card--envios">
                <div class="enq-metric-card__label">Envios realizados</div>
                <div class="enq-metric-card__value">{{ $stats['totalEnvios'] }}</div>
            </div>
            <div class="enq-metric-card enq-metric-card--taxa">
                <div class="enq-metric-card__label">Taxa de resposta</div>
                <div class="enq-metric-card__value">{{ number_format($stats['taxa'], 1, ',', '.') }}%</div>
            </div>
            @foreach($stats['metricas'] as $opcao => $qtd)
                <div class="enq-metric-card enq-metric-card--opcao">
                    <div class="enq-metric-card__label">{{ $opcao }}</div>
                    <div class="enq-metric-card__value">{{ $qtd }}</div>
                </div>
            @endforeach
        </div>

        <h3 style="font-size:.95rem;font-weight:700;margin:0 0 .75rem;">Últimas respostas</h3>

        @if($respostas->isEmpty())
            <p class="fi-data-empty">Nenhuma resposta registrada para esta enquete.</p>
        @else
            <div class="fi-data-table-wrap enq-table-wrap">
                <table class="fi-data-table enq-data-table">
                    <thead>
                        <tr>
                            <th class="col-date">Data</th>
                            <th class="col-phone">Destinatário</th>
                            <th class="col-name">Nome</th>
                            <th class="col-auto">Resposta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($respostas as $resposta)
                            <tr>
                                <td class="col-date">{{ \App\Support\DataHoraBr::format($resposta->created_at, 'd/m/Y') }}</td>
                                <td class="col-phone">{{ \App\Support\TelefoneBr::exibir($resposta->destinatario) }}</td>
                                <td class="col-name">{{ $resposta->nome_destinatario ?? '—' }}</td>
                                <td class="col-auto">{{ $resposta->resposta }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="enq-mobile-cards">
                @foreach($respostas as $resposta)
                    <div class="enq-mobile-card">
                        <div class="enq-mobile-card__row">
                            <span class="enq-mobile-card__label">Data</span>
                            <span class="enq-mobile-card__value">{{ \App\Support\DataHoraBr::format($resposta->created_at, 'd/m/Y') }}</span>
                        </div>
                        <div class="enq-mobile-card__row">
                            <span class="enq-mobile-card__label">Destinatário</span>
                            <span class="enq-mobile-card__value">{{ \App\Support\TelefoneBr::exibir($resposta->destinatario) }}</span>
                        </div>
                        <div class="enq-mobile-card__row">
                            <span class="enq-mobile-card__label">Nome</span>
                            <span class="enq-mobile-card__value">{{ $resposta->nome_destinatario ?? '—' }}</span>
                        </div>
                        <div class="enq-mobile-card__row">
                            <span class="enq-mobile-card__label">Resposta</span>
                            <span class="enq-mobile-card__value">{{ $resposta->resposta }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="enq-pagination">
                <span class="enq-pagination__info">
                    Exibindo {{ $respostas->firstItem() ?? 0 }}–{{ $respostas->lastItem() ?? 0 }} de {{ $respostas->total() }}
                </span>
                @if($respostas->hasPages())
                    <div class="enq-pagination__actions">
                        <button type="button" class="enq-pagination__btn" wire:click="previousPage('dashboardRespostasPage')" @disabled($respostas->onFirstPage())>‹ Anterior</button>
                        <span>{{ $respostas->currentPage() }} / {{ $respostas->lastPage() }}</span>
                        <button type="button" class="enq-pagination__btn" wire:click="nextPage('dashboardRespostasPage')" @disabled(! $respostas->hasMorePages())>Próxima ›</button>
                    </div>
                @endif
            </div>
        @endif
    @endif
</section>
