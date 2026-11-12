<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MenuMakananResource;
use App\Models\MenuMakanan;
use App\Models\MenuMakananVote;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuMakananController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $userId = (int) $request->user()->id;

        $query = $this->menuQuery((int) $sekolah->id, $userId);

        $from = $this->dateQuery($request, 'tanggal_dari', 'start_date');
        $to = $this->dateQuery($request, 'tanggal_sampai', 'end_date');

        if ($from !== null) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('date', '<=', $to);
        }

        $paginator = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->cursorPaginate(Pagination::cursorLimit($request));

        return ApiResponse::success(
            MenuMakananResource::collection($paginator->items())->resolve(),
            Pagination::cursorMeta($paginator),
        );
    }

    public function show(Request $request, int $sekolah, MenuMakanan $menu_makanan): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $menu = $this->menuQuery((int) $sekolah->id, (int) $request->user()->id)
            ->whereKey($menu_makanan->id)
            ->first();

        if (! $menu) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return ApiResponse::success((new MenuMakananResource($menu))->resolve());
    }

    public function vote(Request $request, int $sekolah, MenuMakanan $menu_makanan): JsonResponse
    {
        $sekolah = $this->sekolah($request);
        $menu = MenuMakanan::query()
            ->where('sekolah_id', $sekolah->id)
            ->whereKey($menu_makanan->id)
            ->first();

        if (! $menu) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        $validated = $request->validate([
            'vote_type' => ['required', 'string'],
        ]);

        if (! in_array($validated['vote_type'], ['like', 'dislike'], true)) {
            throw ApiException::make(ApiErrorCode::VOTE_TIDAK_VALID, fields: [
                'vote_type' => [ApiErrorCode::message(ApiErrorCode::VOTE_TIDAK_VALID)],
            ]);
        }

        MenuMakananVote::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'menu_makanan_id' => $menu->id,
            ],
            ['vote_type' => $validated['vote_type']],
        );

        $menu = $this->menuQuery((int) $sekolah->id, (int) $request->user()->id)
            ->whereKey($menu->id)
            ->firstOrFail();

        return ApiResponse::success(
            (new MenuMakananResource($menu))->resolve(),
            [],
            'Terima kasih atas penilaiannya.',
        );
    }

    public function removeVote(Request $request, int $sekolah, MenuMakanan $menu_makanan): JsonResponse
    {
        $sekolah = $this->sekolah($request);

        $menu = MenuMakanan::query()
            ->where('sekolah_id', $sekolah->id)
            ->whereKey($menu_makanan->id)
            ->first();

        if (! $menu) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        MenuMakananVote::query()
            ->where('user_id', $request->user()->id)
            ->where('menu_makanan_id', $menu->id)
            ->delete();

        $menu = $this->menuQuery((int) $sekolah->id, (int) $request->user()->id)
            ->whereKey($menu->id)
            ->firstOrFail();

        return ApiResponse::success(
            (new MenuMakananResource($menu))->resolve(),
            [],
            'Penilaian menu berhasil dihapus.',
        );
    }

    private function menuQuery(int $sekolahId, int $userId): Builder
    {
        return MenuMakanan::query()
            ->where('sekolah_id', $sekolahId)
            ->withCount([
                'votes as likes_count' => fn ($query) => $query->where('vote_type', 'like'),
                'votes as dislikes_count' => fn ($query) => $query->where('vote_type', 'dislike'),
            ])
            ->with(['votes' => fn ($query) => $query->where('user_id', $userId)]);
    }

    private function dateQuery(Request $request, string $key, string $fallback): ?string
    {
        $value = $request->query($key, $request->query($fallback));

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
