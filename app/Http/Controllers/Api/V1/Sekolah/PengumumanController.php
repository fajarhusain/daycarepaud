<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PengumumanResource;
use App\Models\Pengumuman;
use App\Models\PengumumanRead;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $userId = (int) $request->user()->id;
        $kelasIds = $this->kelasIds($request);

        $query = $this->visibleQuery((int) $sekolah->id, $kelasIds, $userId);

        $status = trim((string) $request->query('status', 'semua'));

        if ($status === 'belum_dibaca') {
            $query->whereDoesntHave('reads', fn ($read) => $read->where('user_id', $userId));
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(Pagination::cursorLimit($request));

        return ApiResponse::success(
            PengumumanResource::collection($paginator->items())->resolve(),
            Pagination::cursorMeta($paginator),
        );
    }

    public function show(Request $request, int $sekolah, Pengumuman $pengumuman): JsonResponse
    {
        return ApiResponse::success(
            (new PengumumanResource($this->findVisible($request, (int) $pengumuman->id)))->resolve(),
        );
    }

    public function markAsRead(Request $request, int $sekolah, Pengumuman $pengumuman): JsonResponse
    {
        $item = $this->findVisible($request, (int) $pengumuman->id);

        PengumumanRead::firstOrCreate(
            [
                'pengumuman_id' => $item->id,
                'user_id' => $request->user()->id,
            ],
            ['read_at' => now()],
        );

        $item->load(['kelas', 'reads' => fn ($read) => $read->where('user_id', $request->user()->id)]);

        return ApiResponse::success(
            (new PengumumanResource($item))->resolve(),
            [],
            'Pengumuman ditandai sudah dibaca.',
        );
    }

    /**
     * @return array<int, int>
     */
    private function kelasIds(Request $request): array
    {
        return $this->approvedAnaks($request)
            ->pluck('kelas_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int>  $kelasIds
     */
    private function visibleQuery(int $sekolahId, array $kelasIds, int $userId): Builder
    {
        return Pengumuman::query()
            ->with(['kelas', 'reads' => fn ($read) => $read->where('user_id', $userId)])
            ->where('sekolah_id', $sekolahId)
            ->active()
            ->forKelasIds($kelasIds);
    }

    private function findVisible(Request $request, int $id): Pengumuman
    {
        $sekolah = $this->sekolah($request);

        $item = $this->visibleQuery(
            (int) $sekolah->id,
            $this->kelasIds($request),
            (int) $request->user()->id,
        )->whereKey($id)->first();

        if (! $item) {
            throw ApiException::make(ApiErrorCode::PENGUMUMAN_TIDAK_TERSEDIA);
        }

        return $item;
    }
}
