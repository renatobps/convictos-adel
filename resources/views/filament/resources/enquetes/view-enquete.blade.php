<x-filament-panels::page wire:poll.5s>
    <style>
        .enq-form { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.25rem; }
        .dark .enq-form { background: rgb(24 24 27); border-color: rgb(63 63 70); }
        .enq-form h3 { font-size: 1rem; font-weight: 700; margin: 0 0 0.25rem; }
        .enq-form p.desc { font-size: 0.85rem; color: #6b7280; margin: 0 0 1rem; }
        .enq-field { margin-bottom: 1rem; }
        .enq-field label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.35rem; }
        .enq-field select, .enq-field input[type="text"] {
            width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px;
            padding: 0.5rem 0.75rem; font-size: 0.875rem; background: #fff;
        }
        .dark .enq-field select, .dark .enq-field input[type="text"] {
            background: rgb(39 39 42); border-color: rgb(63 63 70); color: #f4f4f5;
        }
        .enq-error { color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem; }
        .enq-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem; }
        .enq-tag {
            display: inline-flex; align-items: center; gap: 0.35rem; background: #fef3c7;
            color: #92400e; border-radius: 999px; padding: 0.25rem 0.65rem; font-size: 0.8rem;
        }
        .enq-tag button { border: 0; background: transparent; cursor: pointer; color: inherit; font-weight: 700; }
        .enq-num-row { display: flex; gap: 0.5rem; }
        .enq-num-row input { flex: 1; }
        .enq-btn-secondary {
            border: 1px solid #d1d5db; background: #fff; border-radius: 6px; padding: 0.5rem 0.75rem;
            font-size: 0.875rem; font-weight: 600; cursor: pointer; white-space: nowrap;
        }
        .enq-btn-primary {
            width: 100%; border: 0; border-radius: 6px; padding: 0.65rem 1rem; font-size: 0.875rem;
            font-weight: 700; cursor: pointer; background: #16a34a; color: #fff; margin-top: 0.5rem;
        }
        .enq-btn-primary:disabled { opacity: 0.6; cursor: wait; }
        .enq-btn-primary:hover:not(:disabled) { background: #15803d; }

        /* Prévia WhatsApp */
        .wa-chat {
            background: #0b141a;
            border-radius: 12px;
            padding: 1.25rem 1rem 1.5rem;
            min-height: 280px;
            display: flex;
            justify-content: center;
        }

        .wa-bubble {
            max-width: 320px;
            width: 100%;
            background: #005c4b;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
        }

        .wa-bubble__body {
            padding: 0.55rem 0.65rem 0.35rem;
        }

        .wa-bubble__title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #e9edef;
            line-height: 1.35;
            margin: 0 0 0.2rem;
        }

        .wa-bubble__question {
            font-size: 0.9375rem;
            color: #e9edef;
            line-height: 1.4;
            margin: 0;
            white-space: pre-wrap;
        }

        .wa-bubble__meta-row {
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            gap: 0.35rem;
            margin-top: 0.35rem;
        }

        .wa-bubble__source {
            flex: 1;
            font-size: 0.6875rem;
            color: rgba(233, 237, 239, 0.55);
            line-height: 1.2;
        }

        .wa-bubble__time {
            font-size: 0.6875rem;
            color: rgba(233, 237, 239, 0.55);
            white-space: nowrap;
        }

        .wa-bubble__checks {
            color: #53bdeb;
            font-size: 0.75rem;
            letter-spacing: -0.12em;
        }

        .wa-bubble__options {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .wa-option {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: transparent;
            color: #00a884;
            font-size: 0.9375rem;
            font-weight: 500;
            padding: 0.75rem 0.65rem;
            cursor: default;
        }

        .wa-bubble__options .wa-option:first-child {
            border-top: 0;
        }

        .wa-option__icon {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
            color: #00a884;
        }

        .wa-info {
            margin-top: 1rem;
            font-size: 0.8125rem;
            color: #6b7280;
        }

        .wa-info strong {
            color: #374151;
            font-weight: 600;
        }

        .wa-limit-note {
            margin-top: 0.75rem;
            font-size: 0.75rem;
            color: #8696a0;
            text-align: center;
        }

        .enq-top-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: stretch;
        }

        @media (min-width: 1024px) {
            .enq-top-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .enq-top-grid > .fi-section,
        .enq-top-grid > .enq-form {
            height: 100%;
        }

        .enq-top-grid > .enq-form {
            display: flex;
            flex-direction: column;
        }
    </style>

    @include('filament.partials.fi-data-table-styles')

    <style>
        .enq-top-grid {
            min-width: 0;
        }

        .enq-top-grid > .fi-section {
            min-width: 0;
            max-width: 100%;
        }

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

        .enq-table-wrap .fi-data-table.enq-data-table .col-date {
            width: 100px;
        }

        .enq-table-wrap .fi-data-table.enq-data-table .col-phone {
            width: 150px;
        }

        .enq-table-wrap .fi-data-table.enq-data-table .col-name {
            width: 100px;
        }

        .enq-table-wrap .fi-data-table.enq-data-table .col-status,
        .enq-table-wrap .fi-data-table.enq-data-table .col-auto {
            width: 90px;
        }

        .enq-mobile-cards {
            display: none;
        }

        .enq-mobile-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 0.85rem;
            background: #fff;
        }

        .dark .enq-mobile-card {
            background: rgb(24 24 27);
            border-color: rgb(63 63 70);
        }

        .enq-mobile-card + .enq-mobile-card {
            margin-top: 0.65rem;
        }

        .enq-mobile-card__row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.35rem 0;
            font-size: 0.8125rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .dark .enq-mobile-card__row {
            border-bottom-color: rgb(39 39 42);
        }

        .enq-mobile-card__row:last-child {
            border-bottom: 0;
        }

        .enq-mobile-card__label {
            color: #6b7280;
            font-weight: 600;
            flex-shrink: 0;
        }

        .dark .enq-mobile-card__label {
            color: #a1a1aa;
        }

        .enq-mobile-card__value {
            text-align: right;
            white-space: nowrap;
            min-width: 0;
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

        .enq-pagination__btn:hover:not(:disabled) {
            background: #f3f4f6;
        }

        .enq-pagination__btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .dark .enq-pagination__btn {
            background: rgb(39 39 42);
            border-color: rgb(63 63 70);
            color: #e4e4e7;
        }

        .enq-pagination__page {
            white-space: nowrap;
            font-weight: 600;
            color: #374151;
        }

        .dark .enq-pagination__page {
            color: #e4e4e7;
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

        .enq-metric-card--total {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .enq-metric-card--total .enq-metric-card__label { color: #166534; }
        .enq-metric-card--total .enq-metric-card__value { color: #14532d; }

        .enq-metric-card--envios {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }

        .enq-metric-card--envios .enq-metric-card__label { color: #1e40af; }
        .enq-metric-card--envios .enq-metric-card__value { color: #1e3a8a; }

        .enq-metric-card--opcao {
            background: #fff;
            border: 1px solid #e5e7eb;
        }

        .enq-metric-card--opcao .enq-metric-card__label { color: #6b7280; }
        .dark .enq-metric-card--opcao {
            background: rgb(24 24 27);
            border-color: rgb(63 63 70);
        }

        @media (max-width: 639px) {
            .enq-table-wrap {
                display: none;
            }

            .enq-mobile-cards {
                display: block;
            }
        }
    </style>

    <div class="enq-top-grid">
        <x-filament::section heading="Dados da enquete">
            @php
                $opcoesPreview = collect($record->opcoes ?? [])
                    ->map(fn (mixed $opcao) => trim(is_string($opcao) ? $opcao : (string) ($opcao['label'] ?? $opcao['name'] ?? '')))
                    ->filter()
                    ->values();
            @endphp

            <div class="wa-chat">
                <div class="wa-bubble">
                    <div class="wa-bubble__body">
                        <p class="wa-bubble__title">{{ $record->titulo }}</p>
                        <p class="wa-bubble__question">{{ $record->pergunta }}</p>
                        <div class="wa-bubble__meta-row">
                            <span class="wa-bubble__source">CONVICTOS UM 2027</span>
                            <span class="wa-bubble__time">{{ now()->format('H:i') }}</span>
                            <span class="wa-bubble__checks" aria-hidden="true">✓✓</span>
                        </div>
                    </div>

                    @if($opcoesPreview->isNotEmpty())
                        <div class="wa-bubble__options">
                            @foreach($opcoesPreview->take(3) as $opcao)
                                <div class="wa-option">
                                    <svg class="wa-option__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M9 14L4 9l5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20 20v-7a4 4 0 0 0-4-4H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span>{{ $opcao }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($opcoesPreview->count() > 3)
                    <p class="wa-limit-note">Apenas as 3 primeiras opções são enviadas como botão (limite do WhatsApp).</p>
                @endif
            </div>

            <p class="wa-info">
                <strong>Grupo padrão:</strong> {{ $record->grupo?->nome ?? '—' }}
            </p>
        </x-filament::section>

        <section class="enq-form">
            <h3>Enviar enquete</h3>
            <p class="desc">A enquete será enviada como botões clicáveis no WhatsApp (Evolution API).</p>

            <form wire:submit="enviarEnquete">
                <div class="enq-field">
                    <label for="tipo_destino">Enviar para</label>
                    <select id="tipo_destino" wire:model.live="tipo_destino">
                        @foreach($destinoOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('tipo_destino')<div class="enq-error">{{ $message }}</div>@enderror
                </div>

                @if($tipo_destino === 'grupo')
                    <div class="enq-field">
                        <label for="notificacao_grupo_id">Grupo</label>
                        <select id="notificacao_grupo_id" wire:model="notificacao_grupo_id">
                            <option value="">Selecione…</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}">{{ $grupo->nome }} ({{ $grupo->contarDestinatarios() }} inscritos)</option>
                            @endforeach
                        </select>
                        @error('notificacao_grupo_id')<div class="enq-error">{{ $message }}</div>@enderror
                    </div>
                @endif

                @if($tipo_destino === 'inscrito')
                    <div class="enq-field">
                        <label for="inscricao_id">Inscrito</label>
                        <select id="inscricao_id" wire:model="inscricao_id">
                            <option value="">Selecione…</option>
                            @foreach($inscritos as $inscrito)
                                <option value="{{ $inscrito->id }}">
                                    {{ $inscrito->nome }} — {{ $inscrito->igrejaRel?->bairro ?? $inscrito->igreja }} ({{ $inscrito->whatsapp }})
                                </option>
                            @endforeach
                        </select>
                        @error('inscricao_id')<div class="enq-error">{{ $message }}</div>@enderror
                    </div>
                @endif

                @if($tipo_destino === 'numeros')
                    <div class="enq-field">
                        <label for="numero_input">Números (DDD + número)</label>
                        <div class="enq-num-row">
                            <input type="text" id="numero_input" wire:model="numero_input" placeholder="61993640457" wire:keydown.enter.prevent="adicionarNumero">
                            <button type="button" class="enq-btn-secondary" wire:click="adicionarNumero">Adicionar</button>
                        </div>
                        @if(count($numeros) > 0)
                            <div class="enq-tags">
                                @foreach($numeros as $index => $numero)
                                    <span class="enq-tag">
                                        {{ $numero }}
                                        <button type="button" wire:click="removerNumero({{ $index }})" aria-label="Remover">×</button>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @error('numeros')<div class="enq-error">{{ $message }}</div>@enderror
                    </div>
                @endif

                <button type="submit" class="enq-btn-primary" wire:loading.attr="disabled" wire:target="enviarEnquete">
                    <span wire:loading.remove wire:target="enviarEnquete">Enviar enquete</span>
                    <span wire:loading wire:target="enviarEnquete">Enviando…</span>
                </button>
            </form>
        </section>
    </div>

    <div class="enq-top-grid" style="margin-top: 2rem;">
        <x-filament::section heading="Últimos envios">
            @if($envios->isEmpty())
                <p class="fi-data-empty">Nenhum envio registrado.</p>
            @else
                <div class="fi-data-table-wrap enq-table-wrap">
                    <table class="fi-data-table enq-data-table">
                        <thead>
                            <tr>
                                <th class="col-date">Data</th>
                                <th class="col-phone">Destinatário</th>
                                <th class="col-name">Nome</th>
                                <th class="col-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($envios as $envio)
                                <tr>
                                    <td class="col-date">{{ \App\Support\DataHoraBr::format($envio->created_at, 'd/m/Y') }}</td>
                                    <td class="col-phone">{{ \App\Support\TelefoneBr::exibir($envio->destinatario) }}</td>
                                    <td class="col-name">{{ $envio->nome_destinatario ?? '—' }}</td>
                                    <td class="col-status">
                                        <span @class([
                                            'fi-data-badge',
                                            'fi-data-badge--ok' => $envio->status === 'enviada',
                                            'fi-data-badge--erro' => $envio->status === 'erro',
                                        ])>
                                            {{ ucfirst($envio->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="enq-mobile-cards">
                    @foreach($envios as $envio)
                        <div class="enq-mobile-card">
                            <div class="enq-mobile-card__row">
                                <span class="enq-mobile-card__label">Data</span>
                                <span class="enq-mobile-card__value">{{ \App\Support\DataHoraBr::format($envio->created_at, 'd/m/Y') }}</span>
                            </div>
                            <div class="enq-mobile-card__row">
                                <span class="enq-mobile-card__label">Destinatário</span>
                                <span class="enq-mobile-card__value">{{ \App\Support\TelefoneBr::exibir($envio->destinatario) }}</span>
                            </div>
                            <div class="enq-mobile-card__row">
                                <span class="enq-mobile-card__label">Nome</span>
                                <span class="enq-mobile-card__value">{{ $envio->nome_destinatario ?? '—' }}</span>
                            </div>
                            <div class="enq-mobile-card__row">
                                <span class="enq-mobile-card__label">Status</span>
                                <span class="enq-mobile-card__value">
                                    <span @class([
                                        'fi-data-badge',
                                        'fi-data-badge--ok' => $envio->status === 'enviada',
                                        'fi-data-badge--erro' => $envio->status === 'erro',
                                    ])>
                                        {{ ucfirst($envio->status) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="enq-pagination">
                    <span class="enq-pagination__info">
                        Exibindo {{ $envios->firstItem() ?? 0 }}–{{ $envios->lastItem() ?? 0 }} de {{ $envios->total() }}
                    </span>
                    @if($envios->hasPages())
                        <div class="enq-pagination__actions">
                            <button type="button" class="enq-pagination__btn" wire:click="previousPage('enviosPage')" @disabled($envios->onFirstPage())>‹ Anterior</button>
                            <span class="enq-pagination__page">{{ $envios->currentPage() }} / {{ $envios->lastPage() }}</span>
                            <button type="button" class="enq-pagination__btn" wire:click="nextPage('enviosPage')" @disabled(! $envios->hasMorePages())>Próxima ›</button>
                        </div>
                    @endif
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="Respostas">
            @if($totalRespostas > 0)
                <div class="enq-metrics">
                    <div class="enq-metric-card enq-metric-card--total">
                        <div class="enq-metric-card__label">Total de respostas</div>
                        <div class="enq-metric-card__value">{{ $totalRespostas }}</div>
                    </div>
                    <div class="enq-metric-card enq-metric-card--envios">
                        <div class="enq-metric-card__label">Envios realizados</div>
                        <div class="enq-metric-card__value">{{ $totalEnvios }}</div>
                    </div>
                    @foreach($metricas as $opcao => $qtd)
                        <div class="enq-metric-card enq-metric-card--opcao">
                            <div class="enq-metric-card__label">{{ $opcao }}</div>
                            <div class="enq-metric-card__value">{{ $qtd }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($respostas->isEmpty())
                <p class="fi-data-empty">Nenhuma resposta registrada.</p>
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
                            <button type="button" class="enq-pagination__btn" wire:click="previousPage('respostasPage')" @disabled($respostas->onFirstPage())>‹ Anterior</button>
                            <span class="enq-pagination__page">{{ $respostas->currentPage() }} / {{ $respostas->lastPage() }}</span>
                            <button type="button" class="enq-pagination__btn" wire:click="nextPage('respostasPage')" @disabled(! $respostas->hasMorePages())>Próxima ›</button>
                        </div>
                    @endif
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
