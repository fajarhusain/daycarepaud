<?php

namespace App\Support;

final class GuestAscent
{
    public static function asset(string $path): string
    {
        return asset('ascent/assets/'.ltrim($path, '/'));
    }
}
