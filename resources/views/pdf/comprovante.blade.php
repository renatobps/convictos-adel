<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    @php
        $statusLabel = \App\Models\Inscricao::statusOptions()[$inscricao->status] ?? ucfirst((string) $inscricao->status);
        $statusCor = match ($inscricao->status) {
            \App\Models\Inscricao::STATUS_CONFIRMADA => '#16a34a',
            \App\Models\Inscricao::STATUS_CANCELADA => '#dc2626',
            default => '#d97706',
        };
        $igrejaNome = $inscricao->igreja ?: ($inscricao->igrejaRel?->nomeNoFormulario() ?? '—');
        $regional = $inscricao->igrejaRel?->regional?->nome;
    @endphp
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; padding: 0; color: #1f2937; font-size: 12px; }
        .sheet { padding: 28px 32px; }
        .card { border: 1px solid #e5e7eb; border-radius: 10px; }
        .head { background-color: #0b1f4b; color: #ffffff; padding: 16px 20px; }
        .head-event { font-size: 20px; font-weight: bold; margin: 0; }
        .head-sub { font-size: 11px; color: #cbd5e1; margin: 3px 0 0; }
        .badge { color: #ffffff; font-size: 11px; font-weight: bold; padding: 5px 12px; border-radius: 20px; }
        .section-title { font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #94a3b8; font-weight: bold; margin: 0 0 2px; }
        .value { font-size: 14px; font-weight: bold; color: #111827; margin: 0; }
        .code-box { background-color: #f4f6fb; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; text-align: center; }
        .code { font-size: 26px; font-weight: bold; letter-spacing: 3px; color: #0b1f4b; margin: 4px 0 0; }
        .qr { width: 200px; height: 200px; }
        .foot { text-align: center; color: #6b7280; font-size: 10px; padding: 14px 20px; border-top: 1px dashed #e5e7eb; }
        .verse { font-style: italic; color: #0b1f4b; margin: 6px 0 0; }
        td { vertical-align: top; }
    </style>
</head>
<body>
<div class="sheet">
    <div class="card">
        <table width="100%" cellpadding="0" cellspacing="0" class="head">
            <tr>
                <td width="56" style="vertical-align: middle;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="" style="height: 42px;">
                    @endif
                </td>
                <td style="vertical-align: middle;">
                    <p class="head-event">CONVICTOS UM 2027</p>
                    <p class="head-sub">Conferência de Jovens · Comprovante de inscrição</p>
                </td>
                <td align="right" style="vertical-align: middle;">
                    <span class="badge" style="background-color: {{ $statusCor }};">{{ strtoupper($statusLabel) }}</span>
                </td>
            </tr>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="padding: 20px;">
            <tr>
                <td width="230" align="center" style="padding-right: 18px;">
                    <div class="code-box">
                        <img src="{{ $qrDataUri }}" class="qr" alt="QR Code">
                        <p class="section-title" style="margin-top: 10px;">Código do ingresso</p>
                        <p class="code">{{ $inscricao->codigo }}</p>
                    </div>
                    <p style="font-size: 10px; color: #6b7280; margin: 8px 0 0;">Apresente este QR Code na entrada do evento.</p>
                </td>
                <td>
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding-bottom: 14px;">
                                <p class="section-title">Participante</p>
                                <p class="value">{{ $inscricao->nome }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 14px;">
                                <p class="section-title">Igreja</p>
                                <p class="value">{{ $igrejaNome }}</p>
                            </td>
                        </tr>
                        @if($regional)
                            <tr>
                                <td style="padding-bottom: 14px;">
                                    <p class="section-title">Regional</p>
                                    <p class="value">{{ $regional }}</p>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="padding-bottom: 14px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="50%">
                                            <p class="section-title">Camiseta</p>
                                            <p class="value">{{ $inscricao->tamanho_camiseta ?: '—' }}</p>
                                        </td>
                                        <td width="50%">
                                            <p class="section-title">Idade</p>
                                            <p class="value">{{ $inscricao->idade ?: '—' }}</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="section-title">Emitido em</p>
                                <p class="value">{{ $inscricao->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="foot">
            <p style="margin: 0;">Este comprovante é pessoal e intransferível. Guarde-o até o dia do evento.</p>
            <p class="verse">"Para que todos sejam um." — João 17:21</p>
        </div>
    </div>
</div>
</body>
</html>
