<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; padding: 0; color: #1f2937; }
        .sheet { padding: 18px 22px; }
        h1 { font-size: 16px; margin: 0 0 2px; color: #0b1f4b; }
        .sub { font-size: 10px; color: #6b7280; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 5px 6px; font-size: 9px; text-align: left; }
        th { background-color: #0b1f4b; color: #ffffff; text-transform: uppercase; font-size: 8px; }
        tr:nth-child(even) td { background-color: #f9fafb; }
    </style>
</head>
<body>
    <div class="sheet">
        <h1>Inscrições — Convictos UM 2027</h1>
        <p class="sub">Gerado em {{ now()->format('d/m/Y H:i') }} · Total: {{ $linhas->count() }} inscrição(ões)</p>

        <table>
            <thead>
                <tr>
                    @foreach($colunas as $coluna)
                        <th>{{ $coluna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($linhas as $linha)
                    <tr>
                        @foreach($linha as $valor)
                            <td>{{ $valor }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($colunas) }}" style="text-align:center;padding:16px;">Nenhuma inscrição encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
