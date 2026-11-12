<?php

namespace App\Http\Controllers\OrangTua;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnakPendaftaranRequest;
use App\Models\Anak;
use App\Models\Sekolah;
use App\Services\AnakRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnakController extends Controller
{
    public function __construct(
        protected AnakRegistrationService $anakRegistration
    ) {}

    public function create(): View
    {
        $user = auth()->user();

        $registeredSekolahIds = Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->pluck('sekolah_id');

        $sekolahs = Sekolah::active()
            ->orderBy('name')
            ->get();

        return view('orangtua.anak.create', compact('sekolahs', 'registeredSekolahIds'));
    }

    public function store(StoreAnakPendaftaranRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'sekolah_id' => ['required', 'integer', 'exists:sekolahs,id'],
        ]);

        $sekolah = Sekolah::active()->findOrFail($validated['sekolah_id']);

        $this->anakRegistration->createPendingForParent($user, [
            'name' => $request->input('name'),
            'dob' => $request->input('dob'),
            'catatan_ortu' => $request->input('catatan_ortu'),
        ], (int) $sekolah->id, $request->file('photo'));

        return redirect()
            ->route('dashboard')
            ->with('status', 'Pendaftaran anak dikirim. Menunggu persetujuan admin sekolah.');
    }
}
