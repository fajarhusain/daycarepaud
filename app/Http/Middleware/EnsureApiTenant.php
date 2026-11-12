<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Models\Sekolah;
use App\Services\Api\ApiRoleService;
use App\Services\Api\ApiTenantAccessService;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTenant
{
    public function __construct(
        private readonly ApiRoleService $roles,
        private readonly ApiTenantAccessService $access,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            throw ApiException::make(ApiErrorCode::SESI_BERAKHIR);
        }

        $previousTenantId = TenantContext::sekolahId();
        $previousBypass = TenantContext::isBypassed();
        $previousTeamId = app(PermissionRegistrar::class)->getPermissionsTeamId();

        // Pastikan pengecekan akses tidak mewarisi tenant dari request sebelumnya.
        TenantContext::reset();

        try {
            $rawSekolahId = $request->route('sekolah');

            if (! is_numeric($rawSekolahId) || (int) $rawSekolahId < 1) {
                throw ApiException::make(ApiErrorCode::SEKOLAH_TIDAK_DITEMUKAN);
            }

            /** @var Sekolah|null $sekolah */
            $sekolah = Sekolah::query()->find((int) $rawSekolahId);

            // 404 untuk sekolah yang tidak ada maupun yang tidak boleh diakses,
            // supaya client tidak bisa menebak keberadaan sekolah lain.
            if (! $sekolah || ! $this->access->allows($user, $sekolah)) {
                throw ApiException::make(ApiErrorCode::SEKOLAH_TIDAK_DITEMUKAN);
            }

            if (! $sekolah->isOperational()) {
                throw ApiException::make(ApiErrorCode::SEKOLAH_TIDAK_AKTIF);
            }

            TenantContext::setSekolahId((int) $sekolah->id);

            $bypassRoles = config('api.tenant_bypass_roles', []);
            if ($this->roles->hasAnyRole($user, $bypassRoles)) {
                TenantContext::bypassScope();
            }

            app(PermissionRegistrar::class)->setPermissionsTeamId((int) $sekolah->id);
            $request->attributes->set('sekolah', $sekolah);

            return $next($request);
        } finally {
            TenantContext::reset();

            if ($previousTenantId !== null) {
                TenantContext::setSekolahId($previousTenantId);
            }

            if ($previousBypass) {
                TenantContext::bypassScope();
            }

            app(PermissionRegistrar::class)->setPermissionsTeamId($previousTeamId);
        }
    }
}
