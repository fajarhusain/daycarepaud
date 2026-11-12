<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\KegiatanRutinResource;
use App\Models\KegiatanRutin;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KegiatanRutinController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $anaks = $this->approvedAnaks($request);
        $anakIds = $anaks->pluck('id')->map(fn ($id) => (int) $id)->all();
        $anakId = $this->requestedAnakId($request, $anaks);

        [$from, $to] = $this->dateRange($request);

        $query = KegiatanRutin::query()
            ->with(['anak', 'pengajar', 'masterKegiatanRutin'])
            ->where('sekolah_id', $sekolah->id)
            ->whereIn('anak_id', $anakIds)
            ->whereBetween('tanggal', [$from, $to]);

        if ($anakId !== null) {
            $query->where('anak_id', $anakId);
        }

        if ($request->filled('master_id')) {
            $query->where('master_kegiatan_rutin_id', (int) $request->query('master_id'));
        }

        $paginator = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->cursorPaginate(Pagination::cursorLimit($request));

        return ApiResponse::success(
            KegiatanRutinResource::collection($paginator->items())->resolve(),
            Pagination::cursorMeta($paginator) + [
                'tanggal_dari' => $from,
                'tanggal_sampai' => $to,
            ],
        );
    }

    public function show(Request $request, int $sekolah, KegiatanRutin $kegiatan_rutin): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $anaks = $this->approvedAnaks($request);

        $item = KegiatanRutin::query()
            ->with(['anak', 'pengajar', 'masterKegiatanRutin'])
            ->where('sekolah_id', $sekolah->id)
            ->whereIn('anak_id', $anaks->pluck('id')->all())
            ->whereKey($kegiatan_rutin->id)
            ->first();

        if (! $item) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return ApiResponse::success((new KegiatanRutinResource($item))->resolve());
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function dateRange(Request $request): array
    {
        $fromInput = $request->query('tanggal_dari', $request->query('mulai'));
        $toInput = $request->query('tanggal_sampai', $request->query('sampai'));

        $from = $this->parseDate($fromInput, now()->startOfMonth());
        $to = $this->parseDate($toInput, now()->endOfMonth());

        if ($fromInput !== null && $toInput === null) {
            $to = $from;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
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
