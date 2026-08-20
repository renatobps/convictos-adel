<?php

namespace App\Support;

class TelefoneBr
{
    public static function digitos(?string $numero): string
    {
        return preg_replace('/\D+/', '', (string) $numero) ?: '';
    }

    /**
     * Chave estável para comparar celulares BR (DDD + 9 dígitos),
     * independente de máscara ou DDI 55.
     */
    public static function chaveComparacao(?string $numero): ?string
    {
        $digits = self::digitos($numero);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10) {
            $digits = substr($digits, 0, 2).'9'.substr($digits, 2);
        }

        return strlen($digits) === 11 ? $digits : null;
    }

    public static function exibir(?string $numero): string
    {
        if ($numero === null || trim($numero) === '') {
            return '—';
        }

        $digits = self::digitos($numero);

        if (str_starts_with($digits, '55') && strlen($digits) > 11) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7, 4));
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
        }

        return $digits !== '' ? $digits : $numero;
    }
}
