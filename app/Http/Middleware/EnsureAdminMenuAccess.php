<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminMenuAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->hasRole('Orang Tua')) {
            abort(403);
        }

        if ($user?->hasRole('Lembaga')) {
            if (! TenantContext::sekolahId()) {
                abort(403, 'Pilih cabang sekolah aktif terlebih dahulu.');
            }

            return $next($request);
        }

        if (! ($user?->getAttributes()['sekolah_id'] ?? null)) {
            abort(403);
        }

        if ($user->hasRole('Admin Sekolah')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (! $routeName) {
            abort(403);
        }

        if ($routeName === 'admin.settings') {
            if (\App\Support\AdminSettingsNav::canAccessHub($user)) {
                return $next($request);
            }

            abort(403);
        }

        foreach (config('admin-menu.route_permissions', []) as $pattern => $permission) {
            if (Str::is($pattern, $routeName) && $user->can($permission)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
