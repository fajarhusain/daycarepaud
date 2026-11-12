<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Anak;
use App\Models\User;
use App\Services\Api\ApiRoleService;
use App\Services\Api\ApiSchoolService;
use App\Services\Api\GoogleIdTokenVerifier;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function __construct(
        private readonly ApiRoleService $roles,
        private readonly ApiSchoolService $schools,
        private readonly GoogleIdTokenVerifier $googleVerifier,
    ) {}

    public function password(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:30'],
            'app_version' => ['nullable', 'string', 'max:30'],
        ]);

        $email = Str::lower(trim($data['email']));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ApiException::make(ApiErrorCode::KREDENSIAL_SALAH);
        }

        $mobileAliases = $this->roles->mobileAliases($user);

        if ($mobileAliases === []) {
            throw ApiException::make(ApiErrorCode::ROLE_TIDAK_SESUAI);
        }

        if ($this->roles->hasRole($user, 'Orang Tua') && ! $this->hasApprovedChild($user)) {
            throw ApiException::make(ApiErrorCode::AKUN_BELUM_AKTIF);
        }

        return $this->loginResponse(
            user: $user,
            mobileAliases: $mobileAliases,
            deviceName: $this->deviceName($request, $data['device_name'] ?? null),
            messageKey: 'login_password',
        );
    }

    public function google(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:30'],
            'app_version' => ['nullable', 'string', 'max:30'],
        ]);

        $identity = $this->googleVerifier->verify($data['id_token']);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$identity->email])
            ->first();

        $mobileAliases = $user ? $this->roles->mobileAliases($user) : [];

        // Jangan membocorkan apakah email terdaftar, punya role, atau punya anak.
        if (! $user || $mobileAliases === []) {
            throw ApiException::make(ApiErrorCode::AKUN_TIDAK_DAPAT_DIGUNAKAN);
        }

        if ($this->roles->hasRole($user, 'Orang Tua') && ! $this->hasApprovedChild($user)) {
            throw ApiException::make(ApiErrorCode::AKUN_TIDAK_DAPAT_DIGUNAKAN);
        }

        return $this->loginResponse(
            user: $user,
            mobileAliases: $mobileAliases,
            deviceName: $this->deviceName($request, $data['device_name'] ?? null),
            messageKey: 'login_google',
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }

        return ApiResponse::success(
            data: null,
            message: __('api.success.logout'),
        );
    }

    /**
     * @param  list<string>  $mobileAliases
     */
    private function loginResponse(
        User $user,
        array $mobileAliases,
        string $deviceName,
        string $messageKey,
    ): JsonResponse {
        $roleNames = $this->roles->names($user);
        $schools = $this->schools->accessible($user);
        $abilities = ['mobile'];

        foreach ($mobileAliases as $alias) {
            $abilities[] = 'role:'.$alias;
        }

        $token = $user->createToken($deviceName, array_values(array_unique($abilities)))->plainTextToken;

        return ApiResponse::success(
            data: [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => (new UserResource($user, $roleNames))->resolve(),
                'sekolah' => $this->schools->payloads($user, $schools),
            ],
            message: __('api.success.'.$messageKey),
        );
    }

    private function hasApprovedChild(User $user): bool
    {
        return Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();
    }

    private function deviceName(Request $request, ?string $deviceName): string
    {
        $name = trim((string) $deviceName);

        if ($name !== '') {
            return Str::limit($name, 255, '');
        }

        return Str::limit($request->userAgent() ?: 'Aplikasi Mobile', 255, '');
    }
}
