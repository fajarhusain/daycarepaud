<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

final class PresensiPeriodeFilter
{
    /**
     * @return array{from: string, to: string, periode: string, tahun: int, bulan: int, minggu: string|null, week_date: string|null, label: string}
     */
    public static function resolve(Request $request): array
    {
        $periode = $request->input('periode', 'bulan');
        if (! in_array($periode, ['bulan', 'minggu'], true)) {
            $periode = 'bulan';
        }

        if ($periode === 'minggu') {
            $anchor = self::resolveWeekAnchorDate($request);
            $isoY = $anchor->isoWeekYear();
            $isoW = $anchor->isoWeek();
            $from = Carbon::now()->setISODate($isoY, $isoW)->startOfWeek();
            $to = (clone $from)->endOfWeek();
            $weekInput = sprintf('%d-W%02d', $isoY, $isoW);

            return [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'periode' => 'minggu',
                'tahun' => $isoY,
                'bulan' => (int) $from->month,
                'minggu' => $weekInput,
                'week_date' => $anchor->toDateString(),
                'label' => $from->translatedFormat('d M').' – '.$to->translatedFormat('d M Y'),
            ];
        }

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = (clone $from)->endOfMonth();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'periode' => 'bulan',
            'tahun' => $year,
            'bulan' => $month,
            'minggu' => null,
            'week_date' => null,
            'label' => $from->translatedFormat('F Y'),
        ];
    }

    private static function resolveWeekAnchorDate(Request $request): Carbon
    {
        $weekDate = $request->input('week_date');
        if (is_string($weekDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekDate)) {
            try {
                return Carbon::parse($weekDate)->startOfDay();
            } catch (\Exception) {
                // fall through
            }
        }

        $weekInput = (string) $request->input('week', '');
        if (preg_match('/^(\d{4})-W(\d{1,2})$/', $weekInput, $m)) {
            $isoY = (int) $m[1];
            $isoW = (int) $m[2];
            if ($isoW >= 1 && $isoW <= 53) {
                try {
                    return Carbon::now()->setISODate($isoY, $isoW)->startOfWeek();
                } catch (\Exception) {
                    // fall through
                }
            }
        }

        return Carbon::now()->startOfDay();
    }
}
