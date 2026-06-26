@php
    $statusColors = [
        \App\Models\Inscricao::STATUS_AGUARDANDO => ['bg' => '#fef3c7', 'text' => '#92400e'],
        \App\Models\Inscricao::STATUS_CONFIRMADA => ['bg' => '#dcfce7', 'text' => '#166534'],
        \App\Models\Inscricao::STATUS_CANCELADA => ['bg' => '#fee2e2', 'text' => '#991b1b'],
    ];
    $sc = $statusColors[$record->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151'];

    $labelStyle = 'font-size:0.7rem;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;color:#9ca3af;margin-bottom:2px;';
    $valueStyle = 'font-size:0.95rem;font-weight:600;color:#111827;overflow-wrap:anywhere;word-break:break-word;';
    $cardStyle = 'border:1px solid #e5e7eb;border-radius:10px;padding:10px 14px;background:#fff;min-width:0;overflow:hidden;';

    $email = \Illuminate\Support\Str::contains($record->email, '@convictos.local') ? null : $record->email;

    $itens = [
        ['WhatsApp', $record->whatsapp ?: '—'],
        ['E-mail', $email ?: '— (não informado)'],
        ['Idade', $record->idade.' anos'],
        ['Tamanho da camiseta', $record->tamanho_camiseta],
        ['Igreja', $igreja ?: '—'],
        ['Regional', $regional ?: '—'],
        ['Líder de jovens', $record->lider_jovens ? 'Sim' : 'Não'],
    ];
@endphp

<div style="display:flex;flex-direction:column;gap:16px;">
    {{-- Cabeçalho: código + status + data --}}
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-family:ui-monospace,monospace;font-size:0.85rem;font-weight:700;color:#1e3a8a;background:#eff6ff;border:1px solid #dbeafe;padding:4px 10px;border-radius:8px;">
                {{ $record->codigo }}
            </span>
            <span style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};font-size:0.72rem;font-weight:700;padding:4px 12px;border-radius:9999px;">
                {{ $statusLabel }}
            </span>
        </div>
        <span style="font-size:0.75rem;color:#9ca3af;">
            Inscrito em {{ $record->created_at?->format('d/m/Y H:i') ?? '—' }}
        </span>
    </div>

    {{-- Grade de informações --}}
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;">
        @foreach($itens as [$label, $valor])
            <div style="{{ $cardStyle }}">
                <div style="{{ $labelStyle }}">{{ $label }}</div>
                <div style="{{ $valueStyle }}">{{ $valor }}</div>
            </div>
        @endforeach
    </div>

    {{-- Destaque: camiseta retirada --}}
    @if($record->camiseta_retirada)
        <div style="display:flex;align-items:center;gap:12px;border:1px solid #bbf7d0;background:#f0fdf4;border-radius:10px;padding:12px 16px;">
            <div style="font-size:1.4rem;line-height:1;">✅</div>
            <div>
                <div style="font-size:0.9rem;font-weight:700;color:#166534;">Camiseta retirada</div>
                <div style="font-size:0.78rem;color:#15803d;">
                    em {{ $record->camiseta_retirada_em?->format('d/m/Y \à\s H:i') ?? '—' }}
                    @if($record->camiseta_retirada_por)
                        · por <strong>{{ $record->camiseta_retirada_por }}</strong>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div style="display:flex;align-items:center;gap:12px;border:1px solid #e5e7eb;background:#f9fafb;border-radius:10px;padding:12px 16px;">
            <div style="font-size:1.4rem;line-height:1;">⏳</div>
            <div style="font-size:0.9rem;font-weight:600;color:#6b7280;">Camiseta ainda não retirada</div>
        </div>
    @endif
</div>
