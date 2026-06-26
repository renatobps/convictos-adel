<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family:Arial, Helvetica, sans-serif; color:#18181b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                    @if($imagemUrl !== '')
                        <tr>
                            <td style="padding:0;">
                                <img src="{{ $imagemUrl }}" alt="{{ $appName }}" style="display:block; width:100%; max-width:600px; height:auto;">
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:32px 36px 8px; font-size:16px; line-height:1.6; color:#27272a;">
                            {!! $corpoHtml !!}
                        </td>
                    </tr>

                    @if(!empty($produtosImagens))
                        <tr>
                            <td style="padding:8px 36px 4px;">
                                <p style="margin:0 0 12px; font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#64748b; font-weight:bold;">Produtos do pedido</p>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    @foreach($produtosImagens as $produto)
                                        <tr>
                                            <td align="center" style="padding:0 0 16px;">
                                                <img src="{{ $produto['url'] }}" alt="{{ $produto['nome'] }}" style="display:block; max-width:220px; width:100%; height:auto; margin:0 auto 8px; border:1px solid #e2e8f0; border-radius:8px;">
                                                <p style="margin:0; font-size:13px; color:#475569;">{{ $produto['nome'] }} × {{ $produto['quantidade'] }}</p>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if($botaoTexto !== '' && $botaoUrl !== '')
                        <tr>
                            <td align="center" style="padding:16px 36px 32px;">
                                <a href="{{ $botaoUrl }}" target="_blank"
                                   style="display:inline-block; background-color:#CF3136; color:#ffffff; text-decoration:none; font-weight:bold; font-size:15px; padding:13px 28px; border-radius:8px;">
                                    {{ $botaoTexto }}
                                </a>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:20px 36px; background-color:#fafafa; border-top:1px solid #ededed; font-size:12px; line-height:1.5; color:#71717a;">
                            <p style="margin:0;">"Para que todos sejam um." — João 17:21</p>
                            <p style="margin:8px 0 0;">© {{ date('Y') }} {{ $appName }}. Este é um e-mail automático, por favor não responda.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
