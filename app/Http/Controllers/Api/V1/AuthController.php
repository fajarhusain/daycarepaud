<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'sekolah_id' => ['nullable', 'integer', 'exists:sekolahs,id'],
        ]);

        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->hasRole('Orang Tua') && ! Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists()) {
            return response()->json([
                'message' => 'Akun Anda belum aktif. Silakan tunggu persetujuan dari Admin Sekolah.',
            ], 403);
        }

        $device = $data['device_name'] ?? ($request->userAgent() ?: 'api');
        $token = $user->createToken($device)->plainTextToken;

        if ($user->hasRole('Orang Tua') && ! empty($data['sekolah_id'])) {
            $sekolahId = (int) $data['sekolah_id'];
            if (in_array($sekolahId, $user->approvedSekolahIds(), true)) {
                session(['ortu_active_sekolah_id' => $sekolahId]);
            }
        }

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($this->userPayload($request->user()));
    }

    public function updateActiveSekolah(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('Orang Tua'), 403);

        $validated = $request->validate([
            'sekolah_id' => ['required', 'integer', 'exists:sekolahs,id'],
        ]);

        $sekolahId = (int) $validated['sekolah_id'];
        abort_unless(in_array($sekolahId, $user->approvedSekolahIds(), true), 403);

        session(['ortu_active_sekolah_id' => $sekolahId]);
        TenantContext::setSekolahId($sekolahId);

        return response()->json($this->userPayload($user));
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        $user->loadMissing(['sekolah', 'kelas']);

        $sekolahs = [];
        $activeSekolahId = null;

        if ($user->hasRole('Orang Tua')) {
            $sekolahs = Sekolah::query()
                ->whereIn('id', $user->approvedSekolahIds())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
                ->values()
                ->all();
            $activeSekolahId = TenantContext::sekolahId() ?? session('ortu_active_sekolah_id');
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'sekolah_id' => $user->sekolah_id,
            'active_sekolah_id' => $activeSekolahId ? (int) $activeSekolahId : null,
            'kelas_id' => $user->kelas_id,
            'roles' => $user->getRoleNames()->values()->all(),
            'sekolahs' => $sekolahs,
            'sekolah' => $user->sekolah ? [
                'id' => $user->sekolah->id,
                'name' => $user->sekolah->name,
            ] : null,
        ];
    }
}
