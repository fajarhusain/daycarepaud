<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\User;
use App\Services\Api\ApiRoleService;
use App\Services\Api\ApiSchoolService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __construct(
        private readonly ApiRoleService $roles,
        private readonly ApiSchoolService $schools,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $schools = $this->schools->accessible($user);

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $this->roles->names($user),
            'mobile_roles' => $this->roles->mobileAliases($user),
            'default_sekolah_id' => $schools->first()?->id,
            'sekolah' => $this->schools->payloads($user, $schools),
            'orang_tua' => $this->parentData($user),
        ]);
    }

    public function updateOrangTua(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'parent_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nama_bapak' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nik_bapak' => ['sometimes', 'nullable', 'string', 'max:50'],
            'nama_ibu' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nik_ibu' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $anaks = Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->get();

        foreach ($anaks as $anak) {
            $anak->update($validated);
        }

        return ApiResponse::success(
            $this->parentData($user),
            [],
            'Data orang tua berhasil diperbarui.',
        );
    }

    /**
     * @return array<string, string|null>|null
     */
    private function parentData(User $user): ?array
    {
        $anaks = Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->orderBy('id')
            ->get();

        if ($anaks->isEmpty()) {
            return null;
        }

        $anak = $anaks->first(fn (Anak $row) => filled($row->parent_name)
            || filled($row->nama_bapak)
            || filled($row->nama_ibu)
            || filled($row->nik_bapak)
            || filled($row->nik_ibu)) ?? $anaks->first();

        return [
            'parent_name' => $anak->parent_name,
            'nama_bapak' => $anak->nama_bapak,
            'nik_bapak' => $anak->nik_bapak,
            'nama_ibu' => $anak->nama_ibu,
            'nik_ibu' => $anak->nik_ibu,
        ];
    }
}
