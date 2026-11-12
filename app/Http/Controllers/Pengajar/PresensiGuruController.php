<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Controller;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Services\PresensiPengajarService;
use Illuminate\Http\Request;

class PresensiGuruController extends Controller
{
    public function __construct(private readonly PresensiPengajarService $presensi) {}

    public function index(Request $request)
    {
        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();
        $sekolah = Sekolah::findOrFail($pengajar->sekolah_id);

        $status = $this->presensi->statusHariIni($pengajar, $sekolah, now());

        return view('pengajar.presensi-guru.index', [
            'pengajar' => $pengajar,
            'sekolah' => $sekolah,
            'status' => $status,
        ]);
    }

    public function checkIn(Request $request)
    {
        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();
        $sekolah = Sekolah::findOrFail($pengajar->sekolah_id);

        $data = $this->validasiLokasi($request);

        $this->presensi->checkIn($pengajar, $sekolah, $data);

        return redirect()
            ->route('pengajar.presensi-guru.index')
            ->with('success', 'Check-in berhasil. Selamat bertugas!');
    }

    public function checkOut(Request $request)
    {
        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();
        $sekolah = Sekolah::findOrFail($pengajar->sekolah_id);

        $data = $this->validasiLokasi($request);

        $this->presensi->checkOut($pengajar, $sekolah, $data);

        return redirect()
            ->route('pengajar.presensi-guru.index')
            ->with('success', 'Check-out berhasil. Terima kasih!');
    }

    /**
     * @return array{latitude: float, longitude: float, accuracy: float|null}
     */
    private function validasiLokasi(Request $request): array
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ], [
            'latitude.required' => 'Lokasi tidak terdeteksi. Izinkan akses lokasi lalu coba lagi.',
            'longitude.required' => 'Lokasi tidak terdeteksi. Izinkan akses lokasi lalu coba lagi.',
        ]);

        return [
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'accuracy' => isset($validated['accuracy']) ? (float) $validated['accuracy'] : null,
        ];
    }
}
