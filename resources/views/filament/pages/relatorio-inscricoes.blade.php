<x-filament-panels::page>
    @php
        $resumo = $this->resumo;
        $regionaisCards = $this->regionaisCards;
        $metasRegionais = $this->metasRegionais;
        $igrejasPorRegional = $this->igrejasPorRegional;
        $regionaisFiltro = $this->regionaisFiltro;
        $igrejasFiltro = $this->igrejasFiltro;
        $inscricoes = $this->inscricoesRecentes;
    @endphp

    <style>
        .rel-page { display: flex; flex-direction: column; gap: 1.25rem; }
        .rel-filtros {
            display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;
            padding: 1rem 1.25rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
        }
        .dark .rel-filtros { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .rel-field { flex: 1 1 180px; min-width: 0; }
        .rel-field label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
        .dark .rel-field label { color: #d4d4d8; }
        .rel-select, .rel-input {
            width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px;
            padding: 0.5rem 0.75rem; font-size: 0.875rem; background: #fff; color: #111827;
        }
        .dark .rel-select, .dark .rel-input { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #f4f4f5; }
        .rel-btn {
            border: 0; border-radius: 6px; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600;
            cursor: pointer; background: #6b7280; color: #fff;
        }
        .rel-btn:hover { background: #4b5563; }
        .rel-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; }
        .rel-kpi {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem 1.15rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .dark .rel-kpi { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .rel-kpi__label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .03em; }
        .rel-kpi__value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; margin-top: 0.25rem; color: #111827; }
        .dark .rel-kpi__value { color: #f4f4f5; }
        .rel-kpi__sub { font-size: 0.78rem; color: #6b7280; margin-top: 0.2rem; }
        .rel-kpi--green .rel-kpi__value { color: #16a34a; }
        .rel-kpi--amber .rel-kpi__value { color: #d97706; }
        .rel-kpi--red .rel-kpi__value { color: #dc2626; }
        .rel-kpi--blue .rel-kpi__value { color: #2563eb; }
        .rel-card {
            background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .dark .rel-card { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .rel-card__header {
            padding: 0.875rem 1.25rem; font-weight: 700; font-size: 0.95rem;
            border-bottom: 1px solid #e5e7eb; background: #f9fafb; color: #111827;
        }
        .dark .rel-card__header { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #f4f4f5; }
        .rel-card__body { padding: 1rem 1.25rem; }
        .rel-card__body--flush { padding: 0; }
        .rel-meta-bar {
            height: 14px; border-radius: 999px; overflow: hidden; background: #e5e7eb; margin-top: 0.5rem;
        }
        .rel-meta-bar__fill {
            height: 100%; background: linear-gradient(90deg, #3b82f6, #1d4ed8); border-radius: 999px;
            transition: width .4s ease; min-width: 0;
        }
        .rel-meta-bar__fill--ok { background: linear-gradient(90deg, #22c55e, #16a34a); }
        .rel-meta-bar__fill--warn { background: linear-gradient(90deg, #fbbf24, #d97706); }
        .rel-status-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .rel-status-chip {
            display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.75rem;
            border-radius: 999px; font-size: 0.8rem; font-weight: 600; border: 1px solid transparent;
        }
        .rel-status-chip--aguardando { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .rel-status-chip--confirmada { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .rel-status-chip--cancelada { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .rel-reg-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 1rem; }
        .rel-reg-card {
            position: relative; border: 1px solid #e5e7eb; border-radius: 14px; background: #fff;
            padding: 1.15rem 1.15rem 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.06);
            transition: transform .18s ease, box-shadow .18s ease; overflow: hidden;
        }
        .rel-reg-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,.10); }
        .rel-reg-card::before {
            content: ''; position: absolute; inset: 0 0 auto 0; height: 5px;
            background: linear-gradient(90deg, #3b82f6, #6366f1);
        }
        .dark .rel-reg-card { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .rel-reg-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .rel-reg-card__title { font-weight: 800; font-size: 1rem; margin: 0; color: #111827; letter-spacing: .01em; }
        .dark .rel-reg-card__title { color: #f4f4f5; }
        .rel-reg-card__pastor { font-size: 0.74rem; color: #9ca3af; margin: 0.15rem 0 0; display: flex; align-items: center; gap: 0.3rem; }
        .rel-reg-ring {
            flex: 0 0 auto; width: 52px; height: 52px; border-radius: 50%;
            display: grid; place-items: center; font-size: 0.8rem; font-weight: 800; color: #1d4ed8;
        }
        .dark .rel-reg-ring { color: #93c5fd; }
        .rel-reg-card__total { display: flex; align-items: baseline; gap: 0.4rem; margin: 0.9rem 0 0.15rem; }
        .rel-reg-card__total b { font-size: 2rem; font-weight: 800; line-height: 1; color: #111827; }
        .dark .rel-reg-card__total b { color: #f4f4f5; }
        .rel-reg-card__total span { font-size: 0.78rem; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }
        .rel-reg-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; margin: 0.85rem 0; }
        .rel-reg-chip {
            display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.28rem 0.6rem;
            border-radius: 999px; font-size: 0.74rem; font-weight: 700;
        }
        .rel-reg-chip b { font-weight: 800; }
        .rel-reg-chip--c { background: #dcfce7; color: #166534; }
        .rel-reg-chip--a { background: #fef3c7; color: #92400e; }
        .rel-reg-chip--x { background: #fee2e2; color: #991b1b; }
        .rel-reg-card__foot {
            display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
            margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed #e5e7eb;
        }
        .dark .rel-reg-card__foot { border-top-color: rgb(63 63 70); }
        .rel-reg-card__foot small { font-size: 0.72rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }
        .rel-reg-card__foot b { font-size: 1rem; font-weight: 800; color: #047857; }
        .dark .rel-reg-card__foot b { color: #34d399; }
        .rel-table-wrap { overflow-x: auto; }
        .rel-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
        .rel-table th, .rel-table td { padding: 0.65rem 1rem; text-align: left; border-bottom: 1px solid #f3f4f6; }
        .dark .rel-table th, .dark .rel-table td { border-bottom-color: rgb(39 39 42); }
        .rel-table th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; background: #f9fafb; font-weight: 700; }
        .dark .rel-table th { background: rgb(39 39 42); color: #a1a1aa; }
        .rel-table tbody tr:hover { background: #f9fafb; }
        .dark .rel-table tbody tr:hover { background: rgb(39 39 42); }
        .rel-table .num { text-align: right; font-variant-numeric: tabular-nums; }
        .rel-table .regional-row td {
            background: #eff6ff; font-weight: 700; color: #1e40af; border-bottom: 1px solid #dbeafe;
        }
        .dark .rel-table .regional-row td { background: rgb(30 58 138 / 0.25); color: #93c5fd; border-bottom-color: rgb(30 58 138 / 0.4); }
        .rel-badge {
            display: inline-block; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700;
        }
        .rel-badge--aguardando { background: #fef3c7; color: #92400e; }
        .rel-badge--confirmada { background: #dcfce7; color: #166534; }
        .rel-badge--cancelada { background: #fee2e2; color: #991b1b; }
        .rel-empty { padding: 1.5rem; text-align: center; color: #6b7280; font-size: 0.875rem; }
        .rel-meta-list { display: grid; gap: 0.65rem; }
        @media (min-width: 992px) { .rel-meta-list { grid-template-columns: repeat(2, 1fr); } }
        .rel-meta-item { border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.75rem; }
        .dark .rel-meta-item { border-color: rgb(63 63 70); }
        .rel-meta-item__top { display: flex; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.4rem; }
        .rel-meta-item__nome { font-weight: 600; font-size: 0.88rem; margin: 0; }
        .rel-meta-item__pct { font-weight: 700; font-size: 0.85rem; color: #2563eb; }
        .rel-pagination { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; border-top: 1px solid #e5e7eb; font-size: 0.82rem; }
        .dark .rel-pagination { border-top-color: rgb(63 63 70); }
        .rel-pager { display: inline-flex; align-items: center; gap: 0.5rem; }
        .rel-pager__btn {
            border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 6px;
            padding: 0.4rem 0.8rem; font-size: 0.8rem; font-weight: 600; cursor: pointer;
        }
        .rel-pager__btn:hover:not(:disabled) { background: #f3f4f6; }
        .rel-pager__btn:disabled { opacity: 0.45; cursor: not-allowed; }
        .dark .rel-pager__btn { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #e4e4e7; }
        .dark .rel-pager__btn:hover:not(:disabled) { background: rgb(63 63 70); }
        .rel-pager__info { font-size: 0.78rem; color: #6b7280; white-space: nowrap; }
        .dark .rel-pager__info { color: #a1a1aa; }
    </style>

    <div class="rel-page">
        {{-- Filtros --}}
        <section class="rel-filtros">
            <div class="rel-field">
                <label for="filtro_regional">Regional</label>
                <select id="filtro_regional" class="rel-select" wire:model.live="filtro_regional_id">
                    <option value="">Todas as regionais</option>
                    @foreach($regionaisFiltro as $regional)
                        <option value="{{ $regional->id }}">{{ $regional->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rel-field">
                <label for="filtro_igreja">Igreja</label>
                <select id="filtro_igreja" class="rel-select" wire:model.live="filtro_igreja_id">
                    <option value="">Todas as igrejas</option>
                    @foreach($igrejasFiltro as $igreja)
                        <option value="{{ $igreja->id }}">{{ $igreja->bairro }} ({{ $igreja->regional?->nome }})</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="rel-btn" wire:click="limparFiltros">Limpar filtros</button>
        </section>

        {{-- KPIs --}}
        <section class="rel-kpis">
            <div class="rel-kpi rel-kpi--blue">
                <div class="rel-kpi__label">Total de inscrições</div>
                <div class="rel-kpi__value">{{ number_format($resumo['total'], 0, ',', '.') }}</div>
                <div class="rel-kpi__sub">Meta: {{ number_format($resumo['meta_total'], 0, ',', '.') }} ({{ $resumo['percentual_meta'] }}%)</div>
            </div>
            <div class="rel-kpi rel-kpi--green">
                <div class="rel-kpi__label">Confirmadas</div>
                <div class="rel-kpi__value">{{ number_format($resumo['confirmadas'], 0, ',', '.') }}</div>
                <div class="rel-kpi__sub">{{ $resumo['percentual_confirmadas'] }}% do total</div>
            </div>
            <div class="rel-kpi rel-kpi--amber">
                <div class="rel-kpi__label">Aguardando</div>
                <div class="rel-kpi__value">{{ number_format($resumo['aguardando'], 0, ',', '.') }}</div>
            </div>
            <div class="rel-kpi rel-kpi--red">
                <div class="rel-kpi__label">Canceladas</div>
                <div class="rel-kpi__value">{{ number_format($resumo['canceladas'], 0, ',', '.') }}</div>
            </div>
            <div class="rel-kpi">
                <div class="rel-kpi__label">Valor arrecadado</div>
                <div class="rel-kpi__value" style="font-size:1.35rem">R$ {{ number_format($resumo['valor_arrecadado'], 2, ',', '.') }}</div>
                <div class="rel-kpi__sub">R$ {{ number_format($resumo['valor_inscricao'], 2, ',', '.') }} por inscrição</div>
            </div>
        </section>

        {{-- Meta global --}}
        <section class="rel-card">
            <div class="rel-card__header">Progresso da meta global</div>
            <div class="rel-card__body">
                <div style="display:flex;justify-content:space-between;font-size:.875rem;margin-bottom:.25rem">
                    <span><strong>{{ number_format($resumo['total'], 0, ',', '.') }}</strong> de <strong>{{ number_format($resumo['meta_total'], 0, ',', '.') }}</strong> inscrições</span>
                    <span><strong>{{ $resumo['percentual_meta'] }}%</strong></span>
                </div>
                <div class="rel-meta-bar">
                    <div @class([
                        'rel-meta-bar__fill',
                        'rel-meta-bar__fill--ok' => $resumo['percentual_meta'] >= 100,
                        'rel-meta-bar__fill--warn' => $resumo['percentual_meta'] >= 50 && $resumo['percentual_meta'] < 100,
                    ]) style="width: {{ max(2, $resumo['percentual_meta']) }}%"></div>
                </div>
                @if(count($metasRegionais) > 0)
                    <div class="rel-meta-list" style="margin-top:1rem">
                        @foreach($metasRegionais as $meta)
                            <div class="rel-meta-item">
                                <div class="rel-meta-item__top">
                                    <p class="rel-meta-item__nome">{{ $meta['regional']->nome }}</p>
                                    <span class="rel-meta-item__pct">{{ $meta['percentual'] }}%</span>
                                </div>
                                <div style="font-size:.78rem;color:#6b7280;margin-bottom:.35rem">
                                    {{ number_format($meta['inscricoes_atual'], 0, ',', '.') }} / {{ number_format($meta['meta'], 0, ',', '.') }} inscrições
                                </div>
                                <div class="rel-meta-bar">
                                    <div @class([
                                        'rel-meta-bar__fill',
                                        'rel-meta-bar__fill--ok' => $meta['percentual'] >= 100,
                                        'rel-meta-bar__fill--warn' => $meta['percentual'] >= 50 && $meta['percentual'] < 100,
                                    ]) style="width: {{ max(2, $meta['percentual']) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- Status --}}
        <section class="rel-card">
            <div class="rel-card__header">Resumo por status</div>
            <div class="rel-card__body">
                <div class="rel-status-grid">
                    @foreach($resumo['por_status'] as $item)
                        <span @class([
                            'rel-status-chip',
                            'rel-status-chip--'.$item['status'] => in_array($item['status'], ['aguardando','confirmada','cancelada'], true),
                        ])>
                            {{ $item['label'] }}: <strong>{{ number_format($item['total'], 0, ',', '.') }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Cards por regional --}}
        @if(count($regionaisCards) > 0)
            <section class="rel-card">
                <div class="rel-card__header">Inscrições por regional</div>
                <div class="rel-card__body">
                    <div class="rel-reg-grid">
                        @foreach($regionaisCards as $card)
                            @php
                                $pct = (int) $card['percentual_confirmadas'];
                                $ringColor = $pct >= 50 ? '#16a34a' : ($pct >= 25 ? '#d97706' : '#dc2626');
                            @endphp
                            <div class="rel-reg-card">
                                <div class="rel-reg-card__head">
                                    <div style="min-width:0">
                                        <p class="rel-reg-card__title">{{ $card['regional']->nome }}</p>
                                        <p class="rel-reg-card__pastor">👤 {{ $card['regional']->pastor_responsavel ?: '—' }}</p>
                                    </div>
                                    <div class="rel-reg-ring" style="background: conic-gradient({{ $ringColor }} {{ $pct * 3.6 }}deg, #e5e7eb 0); color: {{ $ringColor }};">
                                        <span style="width:38px;height:38px;border-radius:50%;background:#fff;display:grid;place-items:center;">{{ $pct }}%</span>
                                    </div>
                                </div>

                                <div class="rel-reg-card__total">
                                    <b>{{ $card['total'] }}</b>
                                    <span>inscrições</span>
                                </div>

                                <div class="rel-reg-chips">
                                    <span class="rel-reg-chip rel-reg-chip--c">✔ <b>{{ $card['confirmadas'] }}</b> confirmadas</span>
                                    <span class="rel-reg-chip rel-reg-chip--a">⏳ <b>{{ $card['aguardando'] }}</b> aguardando</span>
                                    <span class="rel-reg-chip rel-reg-chip--x">✕ <b>{{ $card['canceladas'] }}</b> canceladas</span>
                                </div>

                                <div class="rel-reg-card__foot">
                                    <small>Arrecadado</small>
                                    <b>R$ {{ number_format($card['valor_arrecadado'], 2, ',', '.') }}</b>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Tabela por igreja / regional --}}
        <section class="rel-card">
            <div class="rel-card__header">Detalhamento por igreja e regional</div>
            <div class="rel-card__body rel-card__body--flush">
                @if(count($igrejasPorRegional) === 0)
                    <p class="rel-empty">Nenhuma igreja encontrada para os filtros selecionados.</p>
                @else
                    <div class="rel-table-wrap">
                        <table class="rel-table">
                            <thead>
                                <tr>
                                    <th>Igreja / Regional</th>
                                    <th>Dirigente</th>
                                    <th class="num">Total</th>
                                    <th class="num">Confirmadas</th>
                                    <th class="num">Aguardando</th>
                                    <th class="num">Canceladas</th>
                                    <th class="num">Arrecadado</th>
                                    <th class="num">% conf.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($igrejasPorRegional as $grupo)
                                    <tr class="regional-row">
                                        <td colspan="2">{{ $grupo['regional_nome'] }}</td>
                                        <td class="num"><strong>{{ $grupo['total'] }}</strong></td>
                                        <td class="num"><strong>{{ $grupo['confirmadas'] }}</strong></td>
                                        <td class="num"><strong>{{ $grupo['aguardando'] }}</strong></td>
                                        <td class="num"><strong>{{ $grupo['canceladas'] }}</strong></td>
                                        <td class="num"><strong>R$ {{ number_format($grupo['valor_arrecadado'], 2, ',', '.') }}</strong></td>
                                        <td class="num">—</td>
                                    </tr>
                                    @foreach($grupo['igrejas'] as $igreja)
                                        <tr>
                                            <td style="padding-left:1.75rem">{{ $igreja['bairro'] }}</td>
                                            <td>{{ $igreja['dirigente'] }}</td>
                                            <td class="num">{{ $igreja['total'] }}</td>
                                            <td class="num">{{ $igreja['confirmadas'] }}</td>
                                            <td class="num">{{ $igreja['aguardando'] }}</td>
                                            <td class="num">{{ $igreja['canceladas'] }}</td>
                                            <td class="num">R$ {{ number_format($igreja['valor_arrecadado'], 2, ',', '.') }}</td>
                                            <td class="num">{{ $igreja['percentual_confirmadas'] }}%</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        {{-- Inscrições recentes --}}
        <section class="rel-card">
            <div class="rel-card__header">Inscrições recentes</div>
            <div class="rel-card__body rel-card__body--flush">
                @if($inscricoes->isEmpty())
                    <p class="rel-empty">Nenhuma inscrição encontrada.</p>
                @else
                    <div class="rel-table-wrap">
                        <table class="rel-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Igreja</th>
                                    <th>Regional</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inscricoes as $inscricao)
                                    <tr>
                                        <td>{{ $inscricao->nome }}</td>
                                        <td>{{ $inscricao->igrejaRel?->bairro ?? $inscricao->igreja }}</td>
                                        <td>{{ $inscricao->igrejaRel?->regional?->nome ?? '—' }}</td>
                                        <td>
                                            <span @class(['rel-badge', 'rel-badge--'.$inscricao->status])>
                                                {{ \App\Models\Inscricao::statusOptions()[$inscricao->status] ?? $inscricao->status }}
                                            </span>
                                        </td>
                                        <td>{{ $inscricao->created_at?->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="rel-pagination">
                        <div>
                            Exibindo {{ $inscricoes->firstItem() ?? 0 }}–{{ $inscricoes->lastItem() ?? 0 }} de {{ $inscricoes->total() }}
                        </div>
                        <div style="display:flex;gap:.5rem;align-items:center">
                            <select class="rel-select" style="width:auto" wire:model.live="perPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <div class="rel-pager">
                                <button type="button" class="rel-pager__btn" wire:click="previousPage('page')" @disabled($inscricoes->onFirstPage())>‹ Anterior</button>
                                <span class="rel-pager__info">Página {{ $inscricoes->currentPage() }} de {{ $inscricoes->lastPage() }}</span>
                                <button type="button" class="rel-pager__btn" wire:click="nextPage('page')" @disabled(! $inscricoes->hasMorePages())>Próxima ›</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-filament-panels::page>
