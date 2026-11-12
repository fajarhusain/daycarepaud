<?php

namespace App\Support;

use App\Models\Pencapaian;
use Illuminate\Support\Collection;

final class FilterAspekPencapaian
{
    /** Nilai khusus query untuk aspek kosong / null. */
    public const UMUM = '__umum__';

    public static function rowMatches(?string $filter, Pencapaian $p): bool
    {
        if ($filter === null || $filter === '') {
            return true;
        }

        if ($filter === self::UMUM) {
            if ($p->matrikulasi_id === null) {
                return true;
            }
            $aspek = $p->matrikulasi?->aspek;

            return $aspek === null || $aspek === '';
        }

        return (string) ($p->matrikulasi?->aspek) === $filter;
    }

    public static function groupHasMatch(?string $filter, Collection $rows): bool
    {
        if ($filter === null || $filter === '') {
            return $rows->isNotEmpty();
        }

        return $rows->contains(fn (Pencapaian $p) => self::rowMatches($filter, $p));
    }
}
