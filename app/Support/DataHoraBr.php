<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;

class DataHoraBr
{
    public static function format(?DateTimeInterface $data, string $format = 'd/m/Y H:i'): string
    {
        if ($data === null) {
            return '—';
        }

        $carbon = $data instanceof CarbonInterface
            ? $data
            : Carbon::parse($data);

        return self::normalizar($carbon)->format($format);
    }

    public static function normalizar(CarbonInterface $data): CarbonInterface
    {
        $fuso = config('app.timezone', 'America/Sao_Paulo');
        $raw = $data->format('Y-m-d H:i:s');

        $comoLocal = Carbon::parse($raw, $fuso);
        $comoUtc = Carbon::parse($raw, 'UTC')->timezone($fuso);

        // Registros antigos (app em UTC) ficam ~3h à frente ao ler como horário local.
        if ($comoLocal->gt(now($fuso)->addMinutes(30)) && $comoUtc->lte(now($fuso)->addMinutes(5))) {
            return $comoUtc;
        }

        return $comoLocal;
    }
}
