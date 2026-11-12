<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MasukanResource;
use App\Http\Traits\CanUploadImage;
use App\Models\KritikSaran;
use App\Support\ApiResponse;
use App\Support\Pagination;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasukanController extends Controller
{
    use CanUploadImage;
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $query = KritikSaran::query()
            ->with('sekolah')
            ->where('user_id', $request->user()->id)
            ->where('sekolah_id', TenantContext::requireSekolahId());

        $status = trim((string) $request->query('status', ''));

        if ($status !== '') {
            $query->where('status', $status);
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(Pagination::cursorLimit($request));

        return ApiResponse::success(
            MasukanResource::collection($paginator->items())->resolve(),
            Pagination::cursorMeta($paginator),
        );
    }

    public function show(Request $request, int $sekolah, int $masukan): JsonResponse
    {
        return ApiResponse::success(
            (new MasukanResource($this->findMasukan($request, $masukan)))->resolve(),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        $data = [
            'sekolah_id' => TenantContext::requireSekolahId(),
            'user_id' => $request->user()->id,
            'message' => trim((string) $validated['message']),
            'status' => 'Terkirim',
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploadImage($request->file('photo'), 'kritik-saran');
        }

        $item = KritikSaran::create($data);
        $item->load('sekolah');

        return ApiResponse::success(
            (new MasukanResource($item))->resolve(),
            [],
            'Saran dan kritik berhasil dikirim.',
            201,
        );
    }

    public function update(Request $request, int $sekolah, int $masukan): JsonResponse
    {
        $item = $this->findMasukan($request, $masukan);

        if ($item->status !== 'Terkirim') {
            throw ApiException::make(ApiErrorCode::MASUKAN_SUDAH_DIPROSES);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        $oldPhoto = $item->photo;
        $newPhoto = null;
        $data = ['message' => trim((string) $validated['message'])];

        if ($request->hasFile('photo')) {
            $newPhoto = $this->uploadImage($request->file('photo'), 'kritik-saran');
            $data['photo'] = $newPhoto;
        }

        try {
            $item->update($data);
        } catch (\Throwable $exception) {
            if ($newPhoto !== null) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto !== null && filled($oldPhoto) && $oldPhoto !== $newPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $item->load('sekolah');

        return ApiResponse::success(
            (new MasukanResource($item))->resolve(),
            [],
            'Masukan berhasil diperbarui.',
        );
    }

    public function destroy(Request $request, int $sekolah, int $masukan): JsonResponse
    {
        $item = $this->findMasukan($request, $masukan);

        if ($item->status !== 'Terkirim') {
            throw ApiException::make(ApiErrorCode::MASUKAN_SUDAH_DIPROSES);
        }

        $photo = $item->photo;
        $item->delete();

        if (filled($photo)) {
            Storage::disk('public')->delete($photo);
        }

        return ApiResponse::success(null, [], 'Masukan berhasil dihapus.');
    }

    private function findMasukan(Request $request, int $id): KritikSaran
    {
        $item = KritikSaran::query()
            ->with('sekolah')
            ->where('user_id', $request->user()->id)
            ->where('sekolah_id', TenantContext::requireSekolahId())
            ->whereKey($id)
            ->first();

        if (! $item) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return $item;
    }
}
