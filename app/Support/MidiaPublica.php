<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MidiaPublica
{
    public static function caminhoLocal(?string $referencia): ?string
    {
        if (blank($referencia)) {
            return null;
        }

        if (self::ehUrlExterna($referencia)) {
            return null;
        }

        $referencia = ltrim($referencia, '/');

        if (str_starts_with($referencia, 'assets/')) {
            $path = public_path($referencia);

            return is_readable($path) ? $path : null;
        }

        foreach ([
            storage_path('app/public/'.$referencia),
            public_path('storage/'.$referencia),
        ] as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    public static function urlPublica(?string $referencia): string
    {
        if (blank($referencia)) {
            return '';
        }

        if (self::ehUrlExterna($referencia)) {
            return $referencia;
        }

        $referencia = ltrim($referencia, '/');

        if (str_starts_with($referencia, 'assets/')) {
            return self::urlAbsoluta(asset($referencia));
        }

        return self::urlAbsoluta(Storage::disk('public')->url($referencia));
    }

    /**
     * URL para exibir imagem em e-mail: incorpora base64 quando o arquivo é local.
     */
    public static function srcEmail(?string $referencia): string
    {
        if (blank($referencia)) {
            return '';
        }

        if (self::ehUrlExterna($referencia)) {
            return $referencia;
        }

        $path = self::caminhoLocal($referencia);
        if ($path !== null) {
            return self::dataUriFromPath($path);
        }

        return self::urlPublica($referencia);
    }

    public static function dataUriFromPath(string $path): string
    {
        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }

    public static function urlAcessivelExternamente(string $url): bool
    {
        if (! self::ehUrlExterna($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';

        return ! in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    public static function urlAbsoluta(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (self::ehUrlExterna($url)) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return url('/'.ltrim($url, '/'));
    }

    private static function ehUrlExterna(string $valor): bool
    {
        return str_starts_with($valor, 'http://') || str_starts_with($valor, 'https://');
    }
}
