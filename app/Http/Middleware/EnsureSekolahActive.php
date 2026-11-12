<?php

namespace App\Http\Middleware;

use App\Models\Sekolah;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSekolahActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (TenantContext::isBypassed()) {
            return $next($request);
        }

        $sekolahId = TenantContext::sekolahId();

        if (! $sekolahId) {
            return $next($request);
        }

        $sekolah = Sekolah::query()->find($sekolahId);

        if (! $sekolah || ! $sekolah->isOperational()) {
            abort(403, 'Sekolah tidak aktif atau sedang ditangguhkan.');
        }

        return $next($request);
    }
}
