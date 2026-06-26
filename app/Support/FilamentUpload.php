<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FilamentUpload
{
    public static function resolve(mixed $arquivo): ?UploadedFile
    {
        if ($arquivo instanceof TemporaryUploadedFile) {
            return $arquivo;
        }

        if ($arquivo instanceof UploadedFile) {
            return $arquivo;
        }

        if (is_string($arquivo) && $arquivo !== '') {
            return self::fromStoredPath($arquivo);
        }

        if (! is_array($arquivo) || empty($arquivo)) {
            return null;
        }

        $first = Arr::first($arquivo);

        if ($first instanceof TemporaryUploadedFile || $first instanceof UploadedFile) {
            return $first;
        }

        if (is_string($first) && $first !== '') {
            return self::fromStoredPath($first);
        }

        return null;
    }

    public static function fromStoredPath(string $storedPath): ?UploadedFile
    {
        if ($storedPath === '') {
            return null;
        }

        $fullPath = Storage::disk('public')->path($storedPath);
        if (! is_file($fullPath)) {
            return null;
        }

        return new UploadedFile(
            $fullPath,
            basename($fullPath),
            mime_content_type($fullPath) ?: null,
            null,
            true,
        );
    }
}
