<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Services\Api\ApiRoleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiRole
{
    public function __construct(private readonly ApiRoleService $roles) {}

    public function handle(Request $request, Closure $next, string ...$roleAliases): Response
    {
        $user = $request->user();

        if (! $user) {
            throw ApiException::make(ApiErrorCode::SESI_BERAKHIR);
        }

        $roleAliases = array_values(array_filter($roleAliases));

        if ($roleAliases === []) {
            Log::error('API role middleware dipanggil tanpa alias role.');

            throw ApiException::make(ApiErrorCode::KESALAHAN_SERVER);
        }

        $roleNames = [];

        foreach ($roleAliases as $alias) {
            $roleName = $this->roles->roleNameForAlias($alias);

            if ($roleName === null) {
                Log::error('Alias role API tidak ditemukan di config/api.php.', [
                    'alias' => $alias,
                ]);

                throw ApiException::make(ApiErrorCode::KESALAHAN_SERVER);
            }

            $roleNames[] = $roleName;
        }

        if (! $this->roles->hasAnyRole($user, $roleNames)) {
            throw ApiException::make(ApiErrorCode::ROLE_TIDAK_SESUAI);
        }

        return $next($request);
    }
}
