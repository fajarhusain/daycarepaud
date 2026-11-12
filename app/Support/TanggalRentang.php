<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

final class TanggalRentang
{
    /**
     * Rentang tanggal inklusif (Y-m-d) untuk filter tanggal input (mis. created_at).
     *
     * @param  string|null  $whenEmpty  'today' = default hari ini–hari ini; null = tidak ada filter (hanya jika tidak ada query sama sekali)
     * @return array{0: string, 1: string}|null null berarti jangan terapkan where tanggal
     */
    public static function dariSampaiQuery(Request $request, ?string $whenEmpty = 'today'): ?array
    {
        $today = date('Y-m-d');
        $legacy = $request->input('tanggal');
        $dari = $request->input('tanggal_dari');
        $sampai = $request->input('tanggal_sampai');

        $hasExplicit = $request->filled('tanggal_dari') || $request->filled('tanggal_sampai');
        $hasLegacy = $request->filled('tanggal');

        if ($whenEmpty === null && ! $hasExplicit && ! $hasLegacy) {
            return null;
        }

        if ($hasLegacy && ! $hasExplicit) {
            try {
                $t = Carbon::parse((string) $legacy)->format('Y-m-d');
            } catch (\Throwable) {
                $t = $today;
            }

            return [$t, $t];
        }

        if (! $hasExplicit && $whenEmpty === 'today') {
            return [$today, $today];
        }

        if (! $hasExplicit) {
            return null;
        }

        try {
            $dariNorm = $request->filled('tanggal_dari') ? Carbon::parse($dari)->format('Y-m-d') : null;
        } catch (\Throwable) {
            $dariNorm = $today;
        }
        try {
            $sampaiNorm = $request->filled('tanggal_sampai') ? Carbon::parse($sampai)->format('Y-m-d') : null;
        } catch (\Throwable) {
            $sampaiNorm = $today;
        }

        if ($dariNorm === null && $sampaiNorm === null) {
            return $whenEmpty === 'today' ? [$today, $today] : null;
        }
        if ($dariNorm === null) {
            $dariNorm = $sampaiNorm;
        }
        if ($sampaiNorm === null) {
            $sampaiNorm = $dariNorm;
        }
        if ($dariNorm > $sampaiNorm) {
            return [$sampaiNorm, $dariNorm];
        }

        return [$dariNorm, $sampaiNorm];
    }

    /** Query string untuk redirect setelah simpan/hapus */
    public static function toQueryParams(string $dari, string $sampai): array
    {
        return [
            'tanggal_dari' => $dari,
            'tanggal_sampai' => $sampai,
        ];
    }
}
