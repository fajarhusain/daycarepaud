<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolSwitcherController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->hasRole('Orang Tua'), 403);

        $validated = $request->validate([
            'sekolah_id' => ['required', 'integer', 'exists:sekolahs,id'],
        ]);

        $sekolahId = (int) $validated['sekolah_id'];

        $hasAnak = Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->exists();

        abort_unless($hasAnak, 403);

        $sekolah = Sekolah::query()->findOrFail($sekolahId);
        abort_unless($sekolah->isOperational(), 403);

        session(['ortu_active_sekolah_id' => $sekolahId]);

        return back()->with('success', 'Sekolah aktif: '.$sekolah->name);
    }
}
