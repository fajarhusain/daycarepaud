<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class ApiStorageUrl
{
    public static function optional(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return url(Storage::url($path));
    }
}
