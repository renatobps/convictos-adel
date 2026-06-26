<x-filament-panels::page>
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
        }

        .wa-bubble {
            max-width: 320px;
            margin-inline-start: auto;
            background: #005c4b;
            border-radius: 8px 8px 8px 2px;
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

    @include('filament.partials.fi-data-table-styles')

    <div class="enq-top-grid" style="margin-top: 2rem;">
        <x-filament::section heading="Últimos envios">
            @if($envios->isEmpty())
                <p class="fi-data-empty">Nenhum envio registrado.</p>
            @else
                <div class="fi-data-table-wrap">
                    <table class="fi-data-table" style="min-width: 640px;">
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
                                    <td class="col-date">{{ $envio->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="col-phone">{{ $envio->destinatario }}</td>
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
            @endif
        </x-filament::section>

        <x-filament::section heading="Respostas">
            @if($respostas->isEmpty())
                <p class="fi-data-empty">Nenhuma resposta registrada.</p>
            @else
                <div class="fi-data-table-wrap">
                    <table class="fi-data-table" style="min-width: 520px;">
                        <thead>
                            <tr>
                                <th class="col-date">Data</th>
                                <th class="col-phone">Destinatário</th>
                                <th class="col-auto">Resposta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($respostas as $resposta)
                                <tr>
                                    <td class="col-date">{{ $resposta->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="col-phone">{{ $resposta->destinatario }}</td>
                                    <td class="col-auto">{{ $resposta->resposta }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
