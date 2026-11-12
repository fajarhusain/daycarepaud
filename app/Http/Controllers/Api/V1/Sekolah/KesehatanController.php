<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\KesehatanResource;
use App\Models\Kesehatan;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KesehatanController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $anaks = $this->approvedAnaks($request);
        $anakIds = $anaks->pluck('id')->map(fn ($id) => (int) $id)->all();
        $anakId = $this->requestedAnakId($request, $anaks);

        $query = $this->queryForChildren($sekolah->id, $anakIds);

        if ($anakId !== null) {
            $query->where('anak_id', $anakId);
        }

        $paginator = $query
            ->with('anak')
            ->orderByDesc('tanggal_pemeriksaan')
            ->orderByDesc('id')
            ->cursorPaginate(Pagination::cursorLimit($request));

        return ApiResponse::success(
            KesehatanResource::collection($paginator->items())->resolve(),
            Pagination::cursorMeta($paginator),
        );
    }

    public function show(Request $request, int $sekolah, int $kesehatan): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $anaks = $this->approvedAnaks($request);
        $anakIds = $anaks->pluck('id')->map(fn ($id) => (int) $id)->all();

        $item = $this->queryForChildren($sekolah->id, $anakIds)
            ->with('anak')
            ->whereKey($kesehatan)
            ->first();

        if (! $item) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return ApiResponse::success((new KesehatanResource($item))->resolve());
    }

    /**
     * @param  array<int, int>  $anakIds
     */
    private function queryForChildren(int $sekolahId, array $anakIds): Builder
    {
        return Kesehatan::query()
            ->whereHas('anak', function ($anak) use ($sekolahId, $anakIds) {
                $anak->whereIn('id', $anakIds)
                    ->where('sekolah_id', $sekolahId);
            });
    }
}
