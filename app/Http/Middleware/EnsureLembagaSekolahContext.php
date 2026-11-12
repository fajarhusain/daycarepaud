<?php

namespace App\Http\Middleware;

use App\Models\Sekolah;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLembagaSekolahContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->hasRole('Lembaga')) {
            return $next($request);
        }

        $activeId = session('active_sekolah_id');

        if (! $activeId) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'Pilih cabang sekolah aktif terlebih dahulu.');
        }

        $belongs = Sekolah::where('id', $activeId)
            ->where('lembaga_id', $user->lembaga_id)
            ->exists();

        if (! $belongs) {
            session()->forget('active_sekolah_id');

            return redirect()
                ->route('dashboard')
                ->with('warning', 'Cabang sekolah tidak valid. Silakan pilih ulang.');
        }

        return $next($request);
    }
}
