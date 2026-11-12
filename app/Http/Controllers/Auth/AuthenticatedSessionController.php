<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Anak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if ($user && $user->hasRole('Lembaga')) {
            $user->loadMissing('lembaga');
            if (! $user->lembaga?->isActive()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = $user->lembaga?->status === 'rejected'
                    ? 'Pendaftaran lembaga ditolak. Hubungi admin platform untuk informasi lebih lanjut.'
                    : 'Akun lembaga menunggu persetujuan superadmin.';

                return redirect()->route('login')->withErrors(['email' => $message]);
            }
        }

        if ($user && $user->hasRole('Admin Sekolah')) {
            $user->loadMissing('sekolah');
            if (! $user->sekolah?->isOperational()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = $user->sekolah?->status === 'rejected'
                    ? 'Pendaftaran sekolah ditolak. Hubungi admin platform untuk informasi lebih lanjut.'
                    : 'Akun sekolah menunggu persetujuan superadmin.';

                return redirect()->route('login')->withErrors(['email' => $message]);
            }
        }

        if ($user && $user->hasRole('Orang Tua')) {
            $hasApprovedAnak = Anak::withoutSekolahScope()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->exists();

            if (! $hasApprovedAnak) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda belum aktif. Silakan tunggu persetujuan dari Admin Sekolah.',
                ]);
            }
        }

        $request->session()->regenerate();

        $homeRoute = match (true) {
            $user?->hasRole('Superadmin') => 'superadmin.dashboard',
            $user?->hasRole('Admin Sekolah') => 'admin.dashboard',
            default => 'dashboard',
        };

        return redirect()->intended(route($homeRoute, absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
