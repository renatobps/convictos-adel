<?php

namespace App\Support;

class TelefoneBr
{
    public static function exibir(?string $numero): string
    {
        if ($numero === null || trim($numero) === '') {
            return '—';
        }

        $digits = preg_replace('/\D+/', '', $numero) ?: '';

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
