<?php

namespace App\Http\Middleware;

use App\Models\Anak;
use App\Models\Sekolah;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        TenantContext::reset();

        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->hasRole('Superadmin')) {
            TenantContext::bypassScope();

            return $next($request);
        }

        // hasRole() di atas memuat roles saat team masih null (pivot sekolah_id null).
        $rolesAtNullTeam = $user->roles->count();

        $sekolahId = $this->resolveSekolahId($user);

        if ($sekolahId !== null) {
            TenantContext::setSekolahId($sekolahId);
            app(PermissionRegistrar::class)->setPermissionsTeamId($sekolahId);

            // Pivot terisi (mis. Admin Kelas) baru ketemu setelah team = sekolah.
            // Jangan buang cache kalau role global (pivot null) sudah ketemu, supaya Admin Sekolah/Lembaga tetap jalan.
            if ($rolesAtNullTeam === 0) {
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');
            }
        }

        return $next($request);
    }

    private function resolveSekolahId($user): ?int
    {
        if ($user->hasRole('Lembaga')) {
            $active = session('active_sekolah_id');

            return $active ? (int) $active : null;
        }

        if ($user->hasRole('Orang Tua')) {
            return $this->resolveOrangTuaSekolahId($user);
        }

        $raw = $user->getAttributes()['sekolah_id'] ?? null;

        return $raw !== null ? (int) $raw : null;
    }

    private function resolveOrangTuaSekolahId($user): ?int
    {
        $sessionId = session('ortu_active_sekolah_id');
        if ($sessionId && $this->userHasApprovedAnakInSekolah($user, (int) $sessionId)) {
            return (int) $sessionId;
        }

        $firstSekolahId = Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->orderBy('id')
            ->value('sekolah_id');

        if ($firstSekolahId) {
            session(['ortu_active_sekolah_id' => (int) $firstSekolahId]);

            return (int) $firstSekolahId;
        }

        return null;
    }

    private function userHasApprovedAnakInSekolah($user, int $sekolahId): bool
    {
        return Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->exists();
    }
}
