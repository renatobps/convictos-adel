<x-filament-panels::page>
    @php
        $qrCode = $this->qrCode;
        $qrMensagem = $this->qrMensagem;
        $pairingCode = $this->pairingCode;
        $conectado = $this->conectado;
        $dadosInstancia = $this->dadosInstancia;
        $erros = $this->erros;
        $atividades = $this->atividadesPaginadas;
    @endphp

    <style>
        .wpp-page {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: 100%;
        }
        .wpp-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-start;
        }
        .wpp-col {
            flex: 1 1 100%;
            min-width: 0;
        }
        .wpp-col--stack {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-self: stretch;
        }
        .wpp-col--stack > .wpp-card {
            flex: 0 0 auto;
            height: auto;
        }
        .wpp-col--stack > .wpp-card--fill {
            flex: 1 1 auto;
            min-height: 0;
        }
        @media (min-width: 992px) {
            .wpp-col--half {
                flex: 1 1 calc(50% - 0.5rem);
                max-width: calc(50% - 0.5rem);
            }
        }
        .wpp-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }
        .dark .wpp-card {
            background: rgb(24 24 27);
            border-color: rgb(63 63 70);
        }
        .wpp-card-header {
            background: #0b8a5a;
            color: #fff;
            font-weight: 600;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            margin: 0;
        }
        .wpp-card-header--plain {
            background: #fff;
            color: #212529;
            border-bottom: 1px solid #dee2e6;
        }
        .dark .wpp-card-header--plain {
            background: rgb(24 24 27);
            color: #f4f4f5;
            border-bottom-color: rgb(63 63 70);
        }
        .wpp-card-header--teste {
            background: #f1b41b;
            color: #212529;
        }
        .wpp-card-header--atividades {
            background: #f8f9fa;
            color: #374151;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .wpp-card-header--atividades {
            background: rgb(39 39 42);
            color: #e4e4e7;
            border-bottom-color: rgb(63 63 70);
        }
        .wpp-card-body {
            padding: 1.25rem;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .wpp-card-body--flush {
            padding: 0;
        }
        .wpp-alert {
            background: #fff8d8;
            border: 1px solid #f3e6a2;
            color: #7a6423;
            border-radius: 4px;
            padding: 14px 16px;
            margin: 0 0 1rem;
            font-size: 0.875rem;
            line-height: 1.45;
        }
        .wpp-alert strong { color: #4f3f14; }
        .wpp-alert--success {
            background: #d4edda;
            border-color: #b7dfc2;
            color: #155724;
        }
        .wpp-alert--success strong { color: #0f5132; }
        .wpp-instancia-info {
            margin: 0.35rem 0 0;
            font-size: 0.8125rem;
            line-height: 1.5;
        }
        .wpp-instancia-info span {
            display: block;
        }
        .wpp-alert--danger {
            background: #f8d7da;
            border-color: #f1b0b7;
            color: #721c24;
        }
        .wpp-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        .wpp-btn {
            width: 100%;
            border: 0;
            border-radius: 4px;
            padding: 10px 14px;
            color: #fff;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .wpp-btn:disabled { opacity: 0.65; cursor: wait; }
        .wpp-btn--status { background: #43b649; }
        .wpp-btn--status:hover:not(:disabled) { background: #3aa13f; }
        .wpp-btn--qr { background: #00a0e9; }
        .wpp-btn--qr:hover:not(:disabled) { background: #0090d1; }
        .wpp-btn--danger { background: #6c757d; }
        .wpp-btn--danger:hover:not(:disabled) { background: #5a6268; }
        .wpp-btn--teste {
            background: #f1b41b;
            width: 100%;
            height: 38px;
        }
        .wpp-btn--teste:hover:not(:disabled) { background: #de9f0f; }
        .wpp-qr-box {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #dee2e6;
            border-radius: 4px;
            background: #fff;
            padding: 1rem;
            text-align: center;
        }
        .dark .wpp-qr-box {
            background: rgb(24 24 27);
            border-color: rgb(63 63 70);
        }
        .wpp-qr-box--empty {
            min-height: 180px;
            flex: 1;
        }
        .wpp-qr-box img {
            display: block;
            max-width: 260px;
            width: 100%;
            height: auto;
            margin: 0 auto;
        }
        .wpp-qr-hint {
            margin: 0;
            font-size: 0.875rem;
            color: #6c757d;
            line-height: 1.5;
        }
        .dark .wpp-qr-hint { color: #a1a1aa; }
        .wpp-pairing {
            margin: 0.75rem 0 0;
            font-size: 0.875rem;
            color: #495057;
        }
        .wpp-test-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: flex-end;
        }
        .wpp-field {
            flex: 1 1 140px;
            min-width: 0;
        }
        .wpp-field--msg {
            flex: 2 1 220px;
        }
        .wpp-field--btn {
            flex: 0 0 120px;
        }
        .wpp-field-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
        }
        .dark .wpp-field-label { color: #d4d4d8; }
        .wpp-field-input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            background: #fff;
            color: #212529;
            height: 38px;
        }
        .dark .wpp-field-input {
            background: rgb(39 39 42);
            border-color: rgb(63 63 70);
            color: #f4f4f5;
        }
        .wpp-field-input:focus {
            outline: none;
            border-color: #f1b41b;
            box-shadow: 0 0 0 2px rgba(241, 180, 27, 0.25);
        }
        .wpp-atividade-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #f1f3f5;
        }
        .dark .wpp-atividade-item { border-bottom-color: rgb(39 39 42); }
        .wpp-atividade-item:last-child { border-bottom: 0; }
        .wpp-atividade-hora {
            flex-shrink: 0;
            background: #43b649;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            line-height: 1.4;
            white-space: nowrap;
        }
        .wpp-atividade-hora--erro { background: #dc3545; }
        .wpp-atividade-texto {
            font-size: 0.875rem;
            color: #374151;
            line-height: 1.45;
            word-break: break-word;
            flex: 1;
            min-width: 0;
        }
        .dark .wpp-atividade-texto { color: #d4d4d8; }
        .wpp-atividade-resumo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .wpp-atividade-resumo__label { font-weight: 600; }
        .wpp-atividade-ver-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #2563eb;
            cursor: pointer;
            flex-shrink: 0;
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .wpp-atividade-ver-btn:hover { background: #eff6ff; border-color: #93c5fd; }
        .dark .wpp-atividade-ver-btn {
            background: rgb(39 39 42);
            border-color: rgb(63 63 70);
            color: #93c5fd;
        }
        .dark .wpp-atividade-ver-btn:hover { background: rgb(30 58 138 / 0.25); }
        .wpp-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgb(0 0 0 / 0.45);
        }
        .wpp-modal {
            width: 100%;
            max-width: 32rem;
            max-height: min(85vh, 36rem);
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 20px 40px rgb(0 0 0 / 0.18);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .dark .wpp-modal {
            background: rgb(24 24 27);
            border: 1px solid rgb(63 63 70);
        }
        .wpp-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 700;
            font-size: 0.95rem;
            color: #111827;
        }
        .dark .wpp-modal__header { border-bottom-color: rgb(63 63 70); color: #f4f4f5; }
        .wpp-modal__close {
            border: 0;
            background: transparent;
            color: #6b7280;
            font-size: 1.35rem;
            line-height: 1;
            cursor: pointer;
            padding: 0.15rem 0.35rem;
            border-radius: 4px;
        }
        .wpp-modal__close:hover { background: #f3f4f6; color: #111827; }
        .dark .wpp-modal__close:hover { background: rgb(63 63 70); color: #f4f4f5; }
        .wpp-modal__body {
            padding: 1rem 1.15rem;
            overflow-y: auto;
            font-size: 0.875rem;
            line-height: 1.55;
            color: #374151;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .dark .wpp-modal__body { color: #d4d4d8; }
        .wpp-modal__destino {
            padding: 0 1.15rem 1rem;
            font-size: 0.78rem;
            color: #6b7280;
        }
        .dark .wpp-modal__destino { color: #a1a1aa; }
        .wpp-atividade-destino {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.15rem;
        }
        .wpp-empty {
            padding: 1rem 1.25rem;
            margin: 0;
            font-size: 0.875rem;
            color: #6c757d;
        }
        .wpp-pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            border-top: 1px solid #f1f3f5;
            font-size: 0.82rem;
        }
        .dark .wpp-pagination { border-top-color: rgb(39 39 42); }
        .wpp-pager {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .wpp-pager__btn {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 6px;
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
        }
        .wpp-pager__btn:hover:not(:disabled) { background: #f3f4f6; }
        .wpp-pager__btn:disabled { opacity: 0.45; cursor: not-allowed; }
        .dark .wpp-pager__btn { background: rgb(39 39 42); border-color: rgb(63 63 70); color: #e4e4e7; }
        .dark .wpp-pager__btn:hover:not(:disabled) { background: rgb(63 63 70); }
        .wpp-pager__info { font-size: 0.78rem; color: #6b7280; white-space: nowrap; }
        .dark .wpp-pager__info { color: #a1a1aa; }
        [x-cloak] { display: none !important; }
    </style>

    <div class="wpp-page">
        <div class="wpp-row">
            <div class="wpp-col wpp-col--half wpp-col--stack">
                <section class="wpp-card">
                    <h2 class="wpp-card-header">Status da Conexão</h2>
                    <div class="wpp-card-body">
                        @if(count($erros) > 0)
                            <div class="wpp-alert wpp-alert--danger">
                                @foreach($erros as $erro)
                                    <div>{{ $erro }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if($conectado)
                            <div class="wpp-alert wpp-alert--success">
                                <strong>Conectado</strong>
                                <div class="wpp-instancia-info">
                                    <span><strong>Instância:</strong> {{ $dadosInstancia['nome'] }}</span>
                                    @if(filled($dadosInstancia['perfil']))
                                        <span><strong>Perfil:</strong> {{ $dadosInstancia['perfil'] }}</span>
                                    @endif
                                    @if(filled($dadosInstancia['numero']))
                                        <span><strong>Número:</strong> {{ $dadosInstancia['numero'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="wpp-alert">
                                <strong>Desconectado</strong>
                                <div class="wpp-instancia-info">
                                    <span><strong>Instância configurada:</strong> {{ $dadosInstancia['nome'] }}</span>
                                </div>
                                Clique em "Obter QR Code" para conectar.
                            </div>
                        @endif

                        <div class="wpp-actions">
                            <button type="button" class="wpp-btn wpp-btn--status" wire:click="verificarStatus" wire:loading.attr="disabled" wire:target="verificarStatus">
                                Verificar Status
                            </button>
                            <button type="button" class="wpp-btn wpp-btn--qr" wire:click="obterQrCode" wire:loading.attr="disabled" wire:target="obterQrCode">
                                <span wire:loading.remove wire:target="obterQrCode,desconectarEGerarQr">Obter QR Code</span>
                                <span wire:loading wire:target="obterQrCode,desconectarEGerarQr">Gerando QR…</span>
                            </button>
                            @if($conectado)
                                <button type="button" class="wpp-btn wpp-btn--danger" wire:click="desconectarEGerarQr" wire:loading.attr="disabled" wire:target="desconectarEGerarQr">
                                    Desconectar e gerar QR
                                </button>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="wpp-card wpp-card--fill">
                    <h2 class="wpp-card-header wpp-card-header--plain">QR Code de Conexão</h2>
                    <div class="wpp-card-body">
                        @if($qrCode !== '')
                            <div class="wpp-qr-box">
                                <img src="{{ $qrCode }}" alt="QR Code da instância">
                            </div>
                            @if($pairingCode !== '')
                                <p class="wpp-pairing">Código de pareamento: <strong>{{ $pairingCode }}</strong></p>
                            @endif
                        @else
                            <div class="wpp-qr-box wpp-qr-box--empty">
                                <p class="wpp-qr-hint">
                                    {{ $qrMensagem !== '' ? $qrMensagem : 'Clique em "Obter QR Code" para conectar.' }}
                                </p>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="wpp-col wpp-col--half wpp-col--stack">
                <section class="wpp-card">
                    <h2 class="wpp-card-header wpp-card-header--teste">Enviar Mensagem de Teste</h2>
                    <div class="wpp-card-body">
                        <form wire:submit="enviarTeste" class="wpp-test-form">
                            <div class="wpp-field">
                                <label for="numero_teste" class="wpp-field-label">Número (com DDD)</label>
                                <input type="text" id="numero_teste" class="wpp-field-input" wire:model="numero_teste" placeholder="61993640457">
                            </div>
                            <div class="wpp-field wpp-field--msg">
                                <label for="mensagem_teste" class="wpp-field-label">Mensagem</label>
                                <input type="text" id="mensagem_teste" class="wpp-field-input" wire:model="mensagem_teste">
                            </div>
                            <div class="wpp-field wpp-field--btn">
                                <button type="submit" class="wpp-btn wpp-btn--teste" wire:loading.attr="disabled" wire:target="enviarTeste">
                                    <span wire:loading.remove wire:target="enviarTeste">Enviar</span>
                                    <span wire:loading wire:target="enviarTeste">…</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="wpp-card wpp-card--fill">
                    <h2 class="wpp-card-header wpp-card-header--atividades">
                        <span aria-hidden="true">📄</span>
                        Últimas Atividades
                    </h2>
                    <div
                        class="wpp-card-body wpp-card-body--flush"
                        x-data="{
                            modalOpen: false,
                            modalTexto: '',
                            modalDestino: '',
                            abrirMensagem(texto, destino) {
                                this.modalTexto = texto;
                                this.modalDestino = destino || '';
                                this.modalOpen = true;
                            },
                            fecharModal() { this.modalOpen = false; }
                        }"
                        @keydown.escape.window="fecharModal()"
                    >
                        @if($atividades->isEmpty())
                            <p class="wpp-empty">Nenhuma atividade registrada ainda.</p>
                        @else
                            @foreach($atividades as $atividade)
                                <div class="wpp-atividade-item">
                                    <span @class([
                                        'wpp-atividade-hora',
                                        'wpp-atividade-hora--erro' => ($atividade['status'] ?? '') === 'erro',
                                    ])>{{ $atividade['hora'] }}</span>
                                    <div class="wpp-atividade-texto">
                                        @if(($atividade['tipo'] ?? '') === 'mensagem')
                                            <div class="wpp-atividade-resumo">
                                                <span class="wpp-atividade-resumo__label">Mensagem WhatsApp</span>
                                                <button
                                                    type="button"
                                                    class="wpp-atividade-ver-btn"
                                                    title="Ver mensagem completa"
                                                    aria-label="Ver mensagem completa"
                                                    @click="abrirMensagem(@js($atividade['texto']), @js($atividade['destinatario'] ?? ''))"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            @if(filled($atividade['destinatario'] ?? null))
                                                <span class="wpp-atividade-destino">Para: {{ $atividade['destinatario'] }}</span>
                                            @endif
                                        @else
                                            {{ $atividade['texto'] }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            @if($atividades->hasPages())
                                <div class="wpp-pagination">
                                    <div>
                                        Exibindo {{ $atividades->firstItem() ?? 0 }}–{{ $atividades->lastItem() ?? 0 }} de {{ $atividades->total() }}
                                    </div>
                                    <div class="wpp-pager">
                                        <button type="button" class="wpp-pager__btn" wire:click="paginaAnteriorAtividades" @disabled($atividades->onFirstPage())>‹ Anterior</button>
                                        <span class="wpp-pager__info">Página {{ $atividades->currentPage() }} de {{ $atividades->lastPage() }}</span>
                                        <button type="button" class="wpp-pager__btn" wire:click="proximaPaginaAtividades" @disabled(! $atividades->hasMorePages())>Próxima ›</button>
                                    </div>
                                </div>
                            @endif

                            <div
                                x-show="modalOpen"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="wpp-modal-overlay"
                                @click.self="fecharModal()"
                            >
                                <div
                                    class="wpp-modal"
                                    role="dialog"
                                    aria-modal="true"
                                    aria-labelledby="wpp-modal-titulo"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    @click.stop
                                >
                                    <div class="wpp-modal__header">
                                        <span id="wpp-modal-titulo">Mensagem enviada</span>
                                        <button type="button" class="wpp-modal__close" @click="fecharModal()" aria-label="Fechar">&times;</button>
                                    </div>
                                    <div class="wpp-modal__body" x-text="modalTexto"></div>
                                    <div class="wpp-modal__destino" x-show="modalDestino !== ''">
                                        Para: <span x-text="modalDestino"></span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
