<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class Pagination
{
    public static function cursorLimit(Request $request, int $default = 20): int
    {
        $limit = (int) $request->query('limit', $default);
        $max = (int) config('api.pagination.max_per_page', 100);

        if ($limit < 1) {
            $limit = $default;
        }

        return min($limit, $max);
    }

    /**
     * @return array{next_cursor: string|null, has_more: bool, limit: int}
     */
    public static function cursorMeta(CursorPaginator $paginator): array
    {
        return [
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'has_more' => $paginator->hasMorePages(),
            'limit' => $paginator->perPage(),
        ];
    }

    public static function offsetPerPage(Request $request, int $default = 20): int
    {
        $perPage = (int) $request->query('per_page', $default);

        if ($perPage < 1) {
            $perPage = $default;
        }

        return min($perPage, (int) config('api.pagination.max_per_page', 100));
    }

    /**
     * @return array{current_page: int, per_page: int, total: int, last_page: int}
     */
    public static function offsetMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    /**
     * @return array{first: string|null, prev: string|null, next: string|null}
     */
    public static function offsetLinks(LengthAwarePaginator $paginator): array
    {
        return [
            'first' => $paginator->url(1),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];
    }
}
