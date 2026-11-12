<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MonevSummaryResource;
use App\Models\MonevSummary;
use App\Services\MonevPdfService;
use App\Support\ApiResponse;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MonevController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $anaks = $this->approvedAnaks($request);
        $anakIds = $anaks->pluck('id')->map(fn ($id) => (int) $id)->all();
        $anakId = $this->requestedAnakId($request, $anaks);

        $query = MonevSummary::query()
            ->with('anak')
            ->whereIn('anak_id', $anakIds)
            ->whereHas('anak', fn ($anak) => $anak
                ->where('user_id', $request->user()->id)
                ->where('sekolah_id', TenantContext::requireSekolahId())
                ->where('status', 'approved'));

        if ($anakId !== null) {
            $query->where('anak_id', $anakId);
        }

        $bulan = (int) $request->query('bulan', 0);
        $tahun = (int) $request->query('tahun', 0);

        if ($bulan >= 1 && $bulan <= 12) {
            $query->where('bulan', $bulan);
        }

        if ($tahun >= 2000 && $tahun <= 2100) {
            $query->where('tahun', $tahun);
        }

        $items = $query
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderByDesc('id')
            ->get();

        $data = $items
            ->map(fn (MonevSummary $item) => (new MonevSummaryResource($item))->resolve())
            ->values()
            ->all();

        return ApiResponse::success(
            $data,
            ['total' => $items->count()],
        );
    }

    public function show(Request $request, int $sekolah, int $monev): JsonResponse
    {
        $item = $this->findSummary($request, $monev);

        return ApiResponse::success(
            (new MonevSummaryResource($item, true))->resolve(),
        );
    }

    public function pdf(Request $request, int $sekolah, int $monev, MonevPdfService $pdf): Response
    {
        $item = $this->findSummary($request, $monev);
        $item->anak->loadMissing('kelas', 'sekolah');

        try {
            return $pdf->download($item->anak, $item);
        } catch (\Throwable $exception) {
            report($exception);

            throw ApiException::make(ApiErrorCode::PDF_TIDAK_TERSEDIA);
        }
    }

    private function findSummary(Request $request, int $id): MonevSummary
    {
        $anaks = $this->approvedAnaks($request);

        $item = MonevSummary::query()
            ->with('anak')
            ->whereIn('anak_id', $anaks->pluck('id')->all())
            ->whereHas('anak', fn ($anak) => $anak
                ->where('user_id', $request->user()->id)
                ->where('sekolah_id', TenantContext::requireSekolahId())
                ->where('status', 'approved'))
            ->whereKey($id)
            ->first();

        if (! $item) {
            throw ApiException::make(ApiErrorCode::LAPORAN_BELUM_TERSEDIA);
        }

        return $item;
    }
}
