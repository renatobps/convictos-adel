<x-filament-panels::page>
    @php
        $qrCode = $this->qrCode;
        $qrMensagem = $this->qrMensagem;
        $pairingCode = $this->pairingCode;
        $conectado = $this->conectado;
        $dadosInstancia = $this->dadosInstancia;
        $erros = $this->erros;
        $atividades = $this->atividades;
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
            align-items: stretch;
        }
        .wpp-col {
            flex: 1 1 100%;
            min-width: 0;
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
            height: 100%;
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
            flex: 1;
            display: flex;
            flex-direction: column;
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
        }
        .dark .wpp-atividade-texto { color: #d4d4d8; }
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
    </style>

    <div class="wpp-page">
        {{-- Linha 1: Status + QR --}}
        <div class="wpp-row">
            <div class="wpp-col wpp-col--half">
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
            </div>

            <div class="wpp-col wpp-col--half">
                <section class="wpp-card">
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
        </div>

        {{-- Linha 2: Teste --}}
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

        {{-- Linha 3: Atividades --}}
        <section class="wpp-card">
            <h2 class="wpp-card-header wpp-card-header--atividades">
                <span aria-hidden="true">📄</span>
                Últimas Atividades
            </h2>
            <div class="wpp-card-body wpp-card-body--flush">
                @if(count($atividades) === 0)
                    <p class="wpp-empty">Nenhuma atividade registrada ainda.</p>
                @else
                    @foreach($atividades as $atividade)
                        <div class="wpp-atividade-item">
                            <span @class([
                                'wpp-atividade-hora',
                                'wpp-atividade-hora--erro' => ($atividade['status'] ?? '') === 'erro',
                            ])>{{ $atividade['hora'] }}</span>
                            <div class="wpp-atividade-texto">
                                {{ $atividade['texto'] }}
                                @if(($atividade['tipo'] ?? '') === 'mensagem' && filled($atividade['destinatario'] ?? null))
                                    <span class="wpp-atividade-destino">Para: {{ $atividade['destinatario'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
    </div>
</x-filament-panels::page>
