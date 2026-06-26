<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class LojaRetiradaConfig
{
    /**
     * @return array<string, string>
     */
    public static function diasSemana(): array
    {
        return [
            'domingo' => 'Domingo',
            'segunda' => 'Segunda-feira',
            'terca' => 'Terça-feira',
            'quarta' => 'Quarta-feira',
            'quinta' => 'Quinta-feira',
            'sexta' => 'Sexta-feira',
            'sabado' => 'Sábado',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function ordemDias(): array
    {
        return array_keys(self::diasSemana());
    }

    public static function local(): string
    {
        $local = trim((string) (self::ler()['local'] ?? ''));

        return $local !== '' ? $local : 'Catedral';
    }

    public static function instrucoes(): string
    {
        $texto = trim((string) (self::ler()['instrucoes'] ?? ''));

        return $texto !== '' ? $texto : 'Todos os produtos devem ser retirados presencialmente na Catedral nos horários disponíveis.';
    }

    /**
     * @return array{name: string, address: string, latitude: float, longitude: float}
     */
    public static function localizacaoPadrao(): array
    {
        return [
            'name' => 'CADEL - Catedral das Assembleias de Deus em Luziânia',
            'address' => 'Av. Alfredo Nasser, 321-467 - Vila Juracy, Luziânia - GO',
            'latitude' => -16.252986803835434,
            'longitude' => -47.9354972929869,
        ];
    }

    /**
     * @return array{name: string, address: string, latitude: float, longitude: float}
     */
    public static function localizacao(): array
    {
        $data = self::ler();
        $padrao = self::localizacaoPadrao();

        return [
            'name' => trim((string) ($data['localizacao_nome'] ?? '')) ?: $padrao['name'],
            'address' => trim((string) ($data['localizacao_endereco'] ?? '')) ?: $padrao['address'],
            'latitude' => self::normalizarCoordenada($data['localizacao_latitude'] ?? null, $padrao['latitude']),
            'longitude' => self::normalizarCoordenada($data['localizacao_longitude'] ?? null, $padrao['longitude']),
        ];
    }

    public static function localizacaoConfigurada(): bool
    {
        $loc = self::localizacao();

        return $loc['latitude'] !== 0.0 || $loc['longitude'] !== 0.0;
    }

    public static function linkGoogleMaps(): string
    {
        $loc = self::localizacao();

        return 'https://www.google.com/maps?q='.$loc['latitude'].','.$loc['longitude'];
    }

    /**
     * @return array<int, array{dia: string, inicio: string, fim: string, ativo: bool}>
     */
    public static function horarios(): array
    {
        $horarios = self::ler()['horarios'] ?? [];

        if (! is_array($horarios) || $horarios === []) {
            return self::horariosPadrao();
        }

        $normalizados = [];
        foreach ($horarios as $item) {
            if (! is_array($item)) {
                continue;
            }

            $dia = (string) ($item['dia'] ?? '');
            if (! array_key_exists($dia, self::diasSemana())) {
                continue;
            }

            $normalizados[] = [
                'dia' => $dia,
                'inicio' => self::normalizarHora((string) ($item['inicio'] ?? '')),
                'fim' => self::normalizarHora((string) ($item['fim'] ?? '')),
                'ativo' => (bool) ($item['ativo'] ?? true),
            ];
        }

        if ($normalizados === []) {
            return self::horariosPadrao();
        }

        return self::ordenarHorarios($normalizados);
    }

    /**
     * @return array<int, array{dia: string, inicio: string, fim: string, ativo: bool}>
     */
    public static function horariosAtivos(): array
    {
        return array_values(array_filter(
            self::horarios(),
            fn (array $item): bool => $item['ativo'] && $item['inicio'] !== '' && $item['fim'] !== ''
        ));
    }

    /**
     * @return array{local: string, instrucoes: string, horarios: array<int, array{dia: string, inicio: string, fim: string, ativo: bool}>}
     */
    public static function paraFormulario(): array
    {
        $data = self::ler();

        $loc = self::localizacao();

        return [
            'local' => self::local(),
            'instrucoes' => self::instrucoes(),
            'horarios' => self::horarios(),
            'localizacao_nome' => $loc['name'],
            'localizacao_endereco' => $loc['address'],
            'localizacao_latitude' => (string) $loc['latitude'],
            'localizacao_longitude' => (string) $loc['longitude'],
        ];
    }

    /**
     * @param  array{local?: string, instrucoes?: string, horarios?: array<int, array<string, mixed>>}  $payload
     */
    public static function salvar(array $payload): void
    {
        $horarios = [];
        foreach ($payload['horarios'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $dia = (string) ($item['dia'] ?? '');
            if (! array_key_exists($dia, self::diasSemana())) {
                continue;
            }

            $horarios[] = [
                'dia' => $dia,
                'inicio' => self::normalizarHora((string) ($item['inicio'] ?? '')),
                'fim' => self::normalizarHora((string) ($item['fim'] ?? '')),
                'ativo' => (bool) ($item['ativo'] ?? true),
            ];
        }

        self::escrever([
            'local' => trim((string) ($payload['local'] ?? 'Catedral')) ?: 'Catedral',
            'instrucoes' => trim((string) ($payload['instrucoes'] ?? '')),
            'horarios' => self::ordenarHorarios($horarios),
            'localizacao_nome' => trim((string) ($payload['localizacao_nome'] ?? '')),
            'localizacao_endereco' => trim((string) ($payload['localizacao_endereco'] ?? '')),
            'localizacao_latitude' => trim((string) ($payload['localizacao_latitude'] ?? '')),
            'localizacao_longitude' => trim((string) ($payload['localizacao_longitude'] ?? '')),
        ]);
    }

    public static function rotuloDia(string $dia): string
    {
        return self::diasSemana()[$dia] ?? $dia;
    }

    public static function formatarHorario(array $item): string
    {
        return self::rotuloDia($item['dia']).': '.$item['inicio'].' às '.$item['fim'];
    }

    /**
     * @return array<int, string>
     */
    public static function linhasHorarios(): array
    {
        return array_map(
            fn (array $item): string => self::formatarHorario($item),
            self::horariosAtivos()
        );
    }

    public static function textoHorariosResumido(): string
    {
        $linhas = self::linhasHorarios();

        return $linhas !== [] ? implode("\n", array_map(fn (string $l): string => '• '.$l, $linhas)) : 'Consulte a equipe da loja.';
    }

    /**
     * @return array<int, array{dia: string, inicio: string, fim: string, ativo: bool}>
     */
    private static function horariosPadrao(): array
    {
        return [
            ['dia' => 'quinta', 'inicio' => '19:00', 'fim' => '21:00', 'ativo' => true],
            ['dia' => 'domingo', 'inicio' => '09:00', 'fim' => '12:00', 'ativo' => true],
        ];
    }

    /**
     * @param  array<int, array{dia: string, inicio: string, fim: string, ativo: bool}>  $horarios
     * @return array<int, array{dia: string, inicio: string, fim: string, ativo: bool}>
     */
    private static function ordenarHorarios(array $horarios): array
    {
        $ordem = array_flip(self::ordemDias());

        usort($horarios, fn (array $a, array $b): int => ($ordem[$a['dia']] ?? 99) <=> ($ordem[$b['dia']] ?? 99));

        return $horarios;
    }

    private static function normalizarHora(string $hora): string
    {
        $hora = trim($hora);
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $hora, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        return '';
    }

    private static function normalizarCoordenada(mixed $valor, float $padrao): float
    {
        if ($valor === null || $valor === '') {
            return $padrao;
        }

        $numero = is_numeric($valor) ? (float) $valor : null;

        return $numero ?? $padrao;
    }

    /**
     * @return array<string, mixed>
     */
    private static function ler(): array
    {
        $path = self::path();
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function escrever(array $payload): void
    {
        File::put(self::path(), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private static function path(): string
    {
        return storage_path('app/loja-retirada.json');
    }
}
