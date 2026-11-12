<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PresensiResource;
use App\Models\Presensi;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $anaks = $this->approvedAnaks($request);
        $anakIds = $anaks->pluck('id')->map(fn ($id) => (int) $id)->all();
        $anakId = $this->requestedAnakId($request, $anaks);

        [$periode, $from, $to] = $this->resolvePeriode($request);

        $base = Presensi::query()
            ->where('sekolah_id', $sekolah->id)
            ->whereIn('anak_id', $anakIds)
            ->whereBetween('tanggal', [$from, $to]);

        if ($anakId !== null) {
            $base->where('anak_id', $anakId);
        }

        $ringkasan = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'total' => 0,
        ];

        foreach ((clone $base)->get(['status', 'hadir']) as $row) {
            $status = strtolower(trim((string) $row->status));

            if (! in_array($status, Presensi::statusOptions(), true)) {
                $status = $row->hadir ? 'hadir' : 'alpha';
            }

            $ringkasan[$status]++;
            $ringkasan['total']++;
        }

        $paginator = (clone $base)
            ->with('anak')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->cursorPaginate(Pagination::cursorLimit($request));

        return ApiResponse::success(
            PresensiResource::collection($paginator->items())->resolve(),
            Pagination::cursorMeta($paginator) + [
                'periode' => $periode,
                'tanggal_dari' => $from,
                'tanggal_sampai' => $to,
                'ringkasan' => $ringkasan,
            ],
        );
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolvePeriode(Request $request): array
    {
        $raw = strtolower(trim((string) $request->query('periode', '')));

        $periode = match ($raw) {
            'harian', 'hari' => 'harian',
            'mingguan', 'minggu' => 'mingguan',
            'bulanan', 'bulan' => 'bulanan',
            'kustom', 'custom' => 'kustom',
            default => null,
        };

        if ($periode === null) {
            if ($request->filled('tanggal_dari') || $request->filled('tanggal_sampai')) {
                $periode = 'kustom';
            } elseif ($request->filled('tanggal')) {
                $periode = 'harian';
            } else {
                $periode = 'bulanan';
            }
        }

        return match ($periode) {
            'harian' => $this->harian($request),
            'mingguan' => $this->mingguan($request),
            'kustom' => $this->kustom($request),
            default => $this->bulanan($request),
        };
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function harian(Request $request): array
    {
        $date = $this->parseDate($request->query('tanggal'), now());

        return ['harian', $date, $date];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function mingguan(Request $request): array
    {
        $anchor = Carbon::parse($this->parseDate($request->query('tanggal'), now()));
        $from = $anchor->copy()->startOfWeek(CarbonInterface::MONDAY);
        $to = $anchor->copy()->endOfWeek(CarbonInterface::SUNDAY);

        return ['mingguan', $from->toDateString(), $to->toDateString()];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function bulanan(Request $request): array
    {
        $anchor = Carbon::parse($this->parseDate($request->query('tanggal'), now()));

        $month = (int) $request->query('bulan', 0);
        $year = (int) $request->query('tahun', 0);

        if ($month < 1 || $month > 12) {
            $month = (int) $anchor->month;
        }

        if ($year < 2000 || $year > 2100) {
            $year = (int) $anchor->year;
        }

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return ['bulanan', $from->toDateString(), $to->toDateString()];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function kustom(Request $request): array
    {
        $from = Carbon::parse($this->parseDate($request->query('tanggal_dari'), now()->startOfMonth()));
        $to = Carbon::parse($this->parseDate($request->query('tanggal_sampai'), now()->endOfMonth()));

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return ['kustom', $from->toDateString(), $to->toDateString()];
    }

    private function parseDate(mixed $value, Carbon $default): string
    {
        if (! is_string($value) || trim($value) === '') {
            return $default->toDateString();
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return $default->toDateString();
        }
    }
}
