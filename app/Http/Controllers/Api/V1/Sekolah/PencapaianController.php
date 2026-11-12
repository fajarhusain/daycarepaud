<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PencapaianResource;
use App\Models\Pencapaian;
use App\Support\ApiResponse;
use App\Support\Pagination;
use App\Support\TanggalRentang;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PencapaianController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $anaks = $this->approvedAnaks($request);
        $anakIds = $anaks->pluck('id')->map(fn ($id) => (int) $id)->all();
        $anakId = $this->requestedAnakId($request, $anaks);

        $query = Pencapaian::query()
            ->with(['anak', 'kegiatan', 'matrikulasi', 'pengajar'])
            ->whereIn('anak_id', $anakIds)
            ->whereHas('anak', fn ($anak) => $anak
                ->where('user_id', $request->user()->id)
                ->where('sekolah_id', TenantContext::requireSekolahId())
                ->where('status', 'approved'));

        if ($anakId !== null) {
            $query->where('anak_id', $anakId);
        }

        $range = TanggalRentang::dariSampaiQuery($request, null);

        if ($range !== null) {
            $query->whereDate('created_at', '>=', $range[0])
                ->whereDate('created_at', '<=', $range[1]);
        }

        $aspek = trim((string) $request->query('aspek', ''));

        if ($aspek !== '') {
            $query->whereHas('matrikulasi', fn ($matrikulasi) => $matrikulasi->where('aspek', $aspek));
        }

        $paginator = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->cursorPaginate(Pagination::cursorLimit($request));

        return ApiResponse::success(
            PencapaianResource::collection($paginator->items())->resolve(),
            Pagination::cursorMeta($paginator),
        );
    }

    public function show(Request $request, int $sekolah, int $pencapaian): JsonResponse
    {
        $anaks = $this->approvedAnaks($request);

        $item = Pencapaian::query()
            ->with(['anak', 'kegiatan', 'matrikulasi', 'pengajar'])
            ->whereIn('anak_id', $anaks->pluck('id')->all())
            ->whereHas('anak', fn ($anak) => $anak
                ->where('user_id', $request->user()->id)
                ->where('sekolah_id', TenantContext::requireSekolahId())
                ->where('status', 'approved'))
            ->whereKey($pencapaian)
            ->first();

        if (! $item) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return ApiResponse::success((new PencapaianResource($item))->resolve());
    }
}
