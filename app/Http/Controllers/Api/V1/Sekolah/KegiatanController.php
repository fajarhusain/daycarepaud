<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\KegiatanResource;
use App\Models\Kegiatan;
use App\Support\ApiResponse;
use App\Support\KegiatanCalendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $anaks = $this->approvedAnaks($request);
        $anakIds = $anaks->pluck('id')->map(fn ($id) => (int) $id)->all();

        $requestedAnakId = $this->requestedAnakId($request, $anaks);
        $selectedAnak = $requestedAnakId !== null
            ? $anaks->firstWhere('id', $requestedAnakId)
            : ($anaks->count() === 1 ? $anaks->first() : null);

        $semuaSekolah = $request->boolean('semua_sekolah');

        $month = (int) $request->query('bulan', $request->query('month', now()->month));
        $year = (int) $request->query('tahun', $request->query('year', now()->year));

        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }

        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }

        [$from, $to] = KegiatanCalendar::dateRangeForCalendar($year, $month);

        $query = Kegiatan::query()
            ->where('sekolah_id', $sekolah->id)
            ->with([
                'pengajar',
                'kelas',
                'pencapaians' => fn ($pencapaian) => $pencapaian
                    ->whereIn('anak_id', $anakIds)
                    ->with(['anak', 'matrikulasi', 'pengajar']),
            ])
            ->whereBetween('date', [$from, $to]);

        if (! $semuaSekolah) {
            if ($selectedAnak !== null) {
                if ($selectedAnak->kelas_id) {
                    $query->where('kelas_id', $selectedAnak->kelas_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $kelasIds = $anaks->pluck('kelas_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

                if ($kelasIds === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('kelas_id', $kelasIds);
                }
            }
        }

        $kegiatans = $query->orderBy('date')->orderBy('id')->get();

        $events = $kegiatans
            ->map(fn (Kegiatan $kegiatan) => (new KegiatanResource($kegiatan, $anakIds))->resolve())
            ->values()
            ->all();

        return ApiResponse::success([
            'meta' => [
                'bulan' => $month,
                'tahun' => $year,
                'tanggal_dari' => $from,
                'tanggal_sampai' => $to,
                'anak_id' => $selectedAnak?->id,
                'semua_sekolah' => $semuaSekolah,
            ],
            'anak' => $anaks->map(fn ($anak) => [
                'id' => $anak->id,
                'name' => $anak->name,
            ])->values()->all(),
            'events' => $events,
        ]);
    }

    public function show(Request $request, int $sekolah, Kegiatan $kegiatan): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $anaks = $this->approvedAnaks($request);
        $anakIds = $anaks->pluck('id')->map(fn ($id) => (int) $id)->all();
        $kelasIds = $anaks->pluck('kelas_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        $item = Kegiatan::query()
            ->where('sekolah_id', $sekolah->id)
            ->with([
                'pengajar',
                'kelas',
                'pencapaians' => fn ($pencapaian) => $pencapaian
                    ->whereIn('anak_id', $anakIds)
                    ->with(['anak', 'matrikulasi', 'pengajar']),
            ])
            ->whereKey($kegiatan->id)
            ->first();

        if (! $item) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        if ($item->kelas_id !== null && ! in_array((int) $item->kelas_id, $kelasIds, true)) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return ApiResponse::success(
            (new KegiatanResource($item, $anakIds))->resolve(),
        );
    }
}
