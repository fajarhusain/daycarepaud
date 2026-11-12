<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AnakResource;
use App\Http\Traits\CanUploadImage;
use App\Models\Anak;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AnakController extends Controller
{
    use CanUploadImage;
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $anaks = $this->approvedAnaks($request);

        return ApiResponse::success(
            AnakResource::collection($anaks)->resolve(),
            ['total' => $anaks->count()],
        );
    }

    public function show(Request $request, int $sekolah, Anak $anak): JsonResponse
    {
        $model = $this->findAnak($request, (int) $anak->id);

        return ApiResponse::success((new AnakResource($model))->resolve());
    }

    public function update(Request $request, int $sekolah, Anak $anak): JsonResponse
    {
        $model = $this->findAnak($request, (int) $anak->id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'nickname' => ['sometimes', 'nullable', 'string', 'max:50'],
            'dob' => ['sometimes', 'nullable', 'date', 'before:today'],
            'nik' => ['sometimes', 'nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['sometimes', 'nullable', Rule::in(['Laki-laki', 'Perempuan', 'laki-laki', 'perempuan'])],
            'alamat' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'photo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ], [
            'dob.before' => 'Tanggal lahir harus sebelum hari ini.',
        ]);

        if (array_key_exists('name', $validated)) {
            $validated['name'] = trim((string) $validated['name']);
        }

        if (array_key_exists('nickname', $validated)) {
            $validated['nickname'] = filled(trim((string) $validated['nickname']))
                ? trim((string) $validated['nickname'])
                : null;
        }

        if (array_key_exists('jenis_kelamin', $validated) && $validated['jenis_kelamin'] !== null) {
            $validated['jenis_kelamin'] = ucfirst(strtolower((string) $validated['jenis_kelamin']));
        }

        unset($validated['photo']);

        $oldPhoto = $model->photo;
        $newPhoto = null;

        if ($request->hasFile('photo')) {
            $newPhoto = $this->uploadImage($request->file('photo'), 'anak');
            $validated['photo'] = $newPhoto;
        }

        try {
            $model->update($validated);
        } catch (\Throwable $exception) {
            if ($newPhoto !== null) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto !== null && filled($oldPhoto) && $oldPhoto !== $newPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $model->load('kelas');

        return ApiResponse::success(
            (new AnakResource($model))->resolve(),
            [],
            'Data anak berhasil diperbarui.',
        );
    }
}
