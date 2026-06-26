@extends('layouts.site')

@section('title', 'Ingresso digital — '.$inscricao->nome)
@section('description', 'Ingresso digital da Conferência Convictos UM 2027.')

@php
    $statusLabel = \App\Models\Inscricao::statusOptions()[$inscricao->status] ?? ucfirst((string) $inscricao->status);
    $statusCor = match ($inscricao->status) {
        \App\Models\Inscricao::STATUS_CONFIRMADA => '#16a34a',
        \App\Models\Inscricao::STATUS_CANCELADA => '#dc2626',
        default => '#d97706',
    };
@endphp

@section('content')
<section class="ticket-page">
    <div class="ticket-wrap">
        @if(session('inscricao_success'))
            <div class="ticket-flash">{{ session('inscricao_success') }}</div>
        @endif

        <div class="ticket">
            <div class="ticket-head">
                <img src="{{ asset('assets/logos/um-escudo-azul.png') }}" alt="Convictos UM 2027" class="ticket-logo">
                <div>
                    <p class="ticket-event">CONVICTOS UM 2027</p>
                    <p class="ticket-sub">Conferência de Jovens · Ingresso digital</p>
                </div>
                <span class="ticket-status" style="background: {{ $statusCor }};">{{ $statusLabel }}</span>
            </div>

            <div class="ticket-body">
                <div class="ticket-qr">
                    <img src="{{ $qrDataUri }}" alt="QR Code do ingresso">
                    <p class="ticket-code">{{ $inscricao->codigo }}</p>
                    <p class="ticket-code-label">Apresente este código na entrada</p>
                </div>

                <div class="ticket-info">
                    <div class="ticket-field">
                        <span class="ticket-field-label">Participante</span>
                        <span class="ticket-field-value">{{ $inscricao->nome }}</span>
                    </div>
                    <div class="ticket-field">
                        <span class="ticket-field-label">Igreja</span>
                        <span class="ticket-field-value">{{ $inscricao->igreja ?: ($inscricao->igrejaRel?->nomeNoFormulario() ?? '—') }}</span>
                    </div>
                    @if($inscricao->igrejaRel?->regional)
                        <div class="ticket-field">
                            <span class="ticket-field-label">Regional</span>
                            <span class="ticket-field-value">{{ $inscricao->igrejaRel->regional->nome }}</span>
                        </div>
                    @endif
                    <div class="ticket-grid">
                        <div class="ticket-field">
                            <span class="ticket-field-label">Camiseta</span>
                            <span class="ticket-field-value">{{ $inscricao->tamanho_camiseta ?: '—' }}</span>
                        </div>
                        <div class="ticket-field">
                            <span class="ticket-field-label">Idade</span>
                            <span class="ticket-field-value">{{ $inscricao->idade ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ticket-foot">
                <p>Guarde este ingresso. Você pode salvá-lo nos favoritos ou tirar um print.</p>
                <p class="ticket-verse">"Para que todos sejam um." — João 17:21</p>
            </div>
        </div>

        <div class="ticket-actions">
            <a href="{{ route('home') }}" class="btn-outline">Voltar ao site</a>
            <a href="{{ route('ingresso.pdf', ['inscricao' => $inscricao->codigo]) }}" class="btn-primary" target="_blank" rel="noopener">Imprimir / Salvar PDF</a>
        </div>
    </div>
</section>

<style>
    .ticket-page { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 100px 20px 60px; background: #0b0b10; }
    .ticket-wrap { width: 100%; max-width: 540px; }
    .ticket-flash { background: rgba(22,163,74,.15); border: 1px solid #16a34a; color: #86efac; padding: 14px 18px; border-radius: 12px; margin-bottom: 18px; font-size: .95rem; text-align: center; }
    .ticket { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,.45); color: #18181b; }
    .ticket-head { display: flex; align-items: center; gap: 14px; padding: 22px 24px; background: linear-gradient(135deg, #0b1f4b, #15306e); color: #fff; position: relative; }
    .ticket-logo { height: 46px; width: auto; }
    .ticket-event { font-family: 'Anton', sans-serif; font-size: 1.25rem; letter-spacing: .5px; margin: 0; }
    .ticket-sub { font-size: .78rem; opacity: .8; margin: 2px 0 0; }
    .ticket-status { position: absolute; top: 16px; right: 18px; color: #fff; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; padding: 5px 12px; border-radius: 999px; }
    .ticket-body { display: flex; gap: 20px; padding: 26px 24px; flex-wrap: wrap; }
    .ticket-qr { text-align: center; flex: 0 0 auto; margin: 0 auto; }
    .ticket-qr img { width: 180px; height: 180px; border-radius: 12px; border: 1px solid #e5e7eb; }
    .ticket-code { font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 1.5rem; letter-spacing: 2px; margin: 12px 0 2px; color: #0b1f4b; }
    .ticket-code-label { font-size: .72rem; color: #6b7280; margin: 0; }
    .ticket-info { flex: 1 1 200px; display: flex; flex-direction: column; gap: 14px; min-width: 200px; }
    .ticket-field { display: flex; flex-direction: column; }
    .ticket-field-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .6px; color: #9ca3af; font-weight: 600; }
    .ticket-field-value { font-size: 1.02rem; font-weight: 600; color: #1f2937; }
    .ticket-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ticket-foot { border-top: 2px dashed #e5e7eb; padding: 18px 24px; text-align: center; font-size: .82rem; color: #6b7280; }
    .ticket-verse { font-style: italic; margin: 8px 0 0; color: #0b1f4b; }
    .ticket-actions { display: flex; gap: 12px; justify-content: center; margin-top: 22px; }
    @media print {
        .ticket-page { background: #fff; padding: 0; }
        .ticket-actions, nav, footer { display: none !important; }
        .ticket { box-shadow: none; }
    }
</style>
@endsection
