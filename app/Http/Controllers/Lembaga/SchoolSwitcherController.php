<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolSwitcherController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->hasRole('Lembaga'), 403);

        $validated = $request->validate([
            'sekolah_id' => ['required', 'integer', 'exists:sekolahs,id'],
        ]);

        $sekolah = Sekolah::findOrFail($validated['sekolah_id']);
        abort_if($sekolah->lembaga_id !== $user->lembaga_id, 403);

        session(['active_sekolah_id' => $sekolah->id]);

        ActivityLogger::log('Cabang aktif diubah', $sekolah, ['sekolah_id' => $sekolah->id]);

        return back()->with('success', 'Cabang aktif: '.$sekolah->name);
    }
}
