<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class NotificacaoHistorico
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array{destinatario: string, mensagem: string, status: string, data: string}>
     */
    public static function listar(array $filters = []): array
    {
        $statusFilter = strtolower((string) ($filters['status'] ?? 'todos'));
        $dataInicio = (string) ($filters['data_inicio'] ?? '');
        $dataFim = (string) ($filters['data_fim'] ?? '');

        $rows = self::ler();

        $rows = array_filter($rows, function (array $item) use ($statusFilter, $dataInicio, $dataFim): bool {
            if ($statusFilter !== 'todos' && $statusFilter !== '' && ($item['status'] ?? '') !== $statusFilter) {
                return false;
            }

            try {
                $dt = Carbon::parse((string) ($item['data'] ?? ''));
            } catch (\Throwable) {
                return false;
            }

            if ($dataInicio !== '') {
                try {
                    $inicio = Carbon::createFromFormat('Y-m-d', $dataInicio)->startOfDay();
                    if ($dt->lt($inicio)) {
                        return false;
                    }
                } catch (\Throwable) {
                }
            }

            if ($dataFim !== '') {
                try {
                    $fim = Carbon::createFromFormat('Y-m-d', $dataFim)->endOfDay();
                    if ($dt->gt($fim)) {
                        return false;
                    }
                } catch (\Throwable) {
                }
            }

            return true;
        });

        return array_values(array_reverse($rows));
    }

    public static function registrar(string $destinatario, string $mensagem, string $status): void
    {
        $rows = self::ler();

        $rows[] = [
            'destinatario' => $destinatario,
            'mensagem' => $mensagem,
            'status' => strtolower($status) === 'erro' ? 'erro' : 'enviada',
            'data' => now()->format('Y-m-d H:i:s'),
        ];

        $rows = array_slice($rows, -2000);
        self::escrever($rows);
    }

    /**
     * @return array<int, array{destinatario: string, mensagem: string, status: string, data: string}>
     */
    private static function ler(): array
    {
        $path = self::path();
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, fn ($row) => is_array($row)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private static function escrever(array $rows): void
    {
        File::put(self::path(), json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private static function path(): string
    {
        return storage_path('app/notificacoes-historico.json');
    }
}
