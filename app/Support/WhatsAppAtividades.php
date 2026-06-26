<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WhatsAppAtividades
{
    /**
     * @return array<int, array{hora: string, tipo: string, texto: string, status: string, destinatario: string|null}>
     */
    public static function listar(int $limit = 12): array
    {
        return array_slice(self::coletar(), 0, $limit);
    }

    public static function paginar(int $page = 1, int $perPage = 5): LengthAwarePaginator
    {
        $itens = self::coletar();
        $total = count($itens);
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        return new Paginator(
            array_slice($itens, $offset, $perPage),
            $total,
            $perPage,
            $page,
        );
    }

    /**
     * @return array<int, array{hora: string, tipo: string, texto: string, status: string, destinatario: string|null}>
     */
    private static function coletar(): array
    {
        $itens = [];

        foreach (self::lerSistema() as $row) {
            $itens[] = [
                'timestamp' => self::parseTimestamp((string) ($row['data'] ?? ''), (string) ($row['hora'] ?? '')),
                'hora' => (string) ($row['hora'] ?? '--:--:--'),
                'tipo' => (string) ($row['tipo'] ?? 'sistema'),
                'texto' => (string) ($row['mensagem'] ?? ''),
                'status' => (string) ($row['status'] ?? 'ok'),
                'destinatario' => null,
            ];
        }

        foreach (NotificacaoHistorico::listar() as $row) {
            try {
                $dt = Carbon::parse((string) ($row['data'] ?? ''));
            } catch (\Throwable) {
                continue;
            }

            $itens[] = [
                'timestamp' => $dt,
                'hora' => $dt->format('H:i:s'),
                'tipo' => 'mensagem',
                'texto' => (string) ($row['mensagem'] ?? ''),
                'status' => (string) ($row['status'] ?? 'enviada'),
                'destinatario' => (string) ($row['destinatario'] ?? ''),
            ];
        }

        usort($itens, fn (array $a, array $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_map(
            fn (array $item) => [
                'hora' => $item['hora'],
                'tipo' => $item['tipo'],
                'texto' => $item['texto'],
                'status' => $item['status'],
                'destinatario' => $item['destinatario'],
            ],
            $itens
        );
    }

    public static function registrar(string $tipo, string $mensagem, string $status = 'ok'): void
    {
        $rows = self::lerSistema();

        $rows[] = [
            'data' => now()->format('Y-m-d'),
            'hora' => now()->format('H:i:s'),
            'tipo' => $tipo,
            'mensagem' => $mensagem,
            'status' => $status,
        ];

        $rows = array_slice($rows, -200);
        self::escreverSistema($rows);
    }

    private static function parseTimestamp(string $data, string $hora): Carbon
    {
        if ($data !== '' && $hora !== '') {
            try {
                return Carbon::createFromFormat('Y-m-d H:i:s', "{$data} {$hora}");
            } catch (\Throwable) {
            }
        }

        if ($hora !== '' && $hora !== '--:--:--') {
            try {
                return Carbon::createFromFormat('H:i:s', $hora)->setDateFrom(now());
            } catch (\Throwable) {
            }
        }

        return now()->subYear();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function lerSistema(): array
    {
        $path = self::pathSistema();
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded)
            ? array_values(array_filter($decoded, fn ($row) => is_array($row)))
            : [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private static function escreverSistema(array $rows): void
    {
        File::put(self::pathSistema(), json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private static function pathSistema(): string
    {
        return storage_path('app/wpp-atividades.json');
    }

    public static function rotuloTipo(string $tipo): string
    {
        return match ($tipo) {
            'mensagem' => 'Mensagem',
            'status' => 'Status',
            'qrcode' => 'QR Code',
            'teste' => 'Teste',
            default => Str::title($tipo),
        };
    }
}
