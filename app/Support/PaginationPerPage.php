<?php

namespace App\Support;

use Illuminate\Http\Request;

class PaginationPerPage
{
    /**
     * @return list<int>
     */
    public static function allowed(): array
    {
        return [10, 30, 50, 100];
    }

    public static function resolve(Request $request, string $key = 'per_page', int $default = 10): int
    {
        $perPage = $request->integer($key, $default);

        return in_array($perPage, self::allowed(), true) ? $perPage : $default;
    }
}
