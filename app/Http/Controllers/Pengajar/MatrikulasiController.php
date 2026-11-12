<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Controller;
use App\Models\Matrikulasi;
use App\Models\Pengajar;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class MatrikulasiController extends Controller
{
    private function getSekolahId()
    {
        return Pengajar::where('user_id', auth()->id())->value('sekolah_id');
    }

    public function index(Request $request)
    {
        $sekolah_id = $this->getSekolahId();
        $matrikulasis = Matrikulasi::where('sekolah_id', $sekolah_id)->latest()->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('pengajar.matrikulasi.index', compact('matrikulasis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'aspek' => 'nullable|string|max:255',
            'indicator' => 'required|string|max:255',
            'description' => 'required|string',
            'tujuan' => 'nullable|string',
            'strategi' => 'nullable|string',
        ]);

        Matrikulasi::create([
            'sekolah_id' => $this->getSekolahId(),
            'aspek' => $request->aspek,
            'indicator' => $request->indicator,
            'description' => $request->description,
            'tujuan' => $request->tujuan,
            'strategi' => $request->strategi,
        ]);

        return redirect()->route('pengajar.matrikulasi.index')->with('success', 'Indikator Matrikulasi berhasil ditambahkan.');
    }

    public function update(Request $request, Matrikulasi $matrikulasi)
    {
        abort_if($matrikulasi->sekolah_id !== $this->getSekolahId(), 403);

        $request->validate([
            'aspek' => 'nullable|string|max:255',
            'indicator' => 'required|string|max:255',
            'description' => 'required|string',
            'tujuan' => 'nullable|string',
            'strategi' => 'nullable|string',
        ]);

        $matrikulasi->update([
            'aspek' => $request->aspek,
            'indicator' => $request->indicator,
            'description' => $request->description,
            'tujuan' => $request->tujuan,
            'strategi' => $request->strategi,
        ]);

        return redirect()->route('pengajar.matrikulasi.index')->with('success', 'Indikator Matrikulasi berhasil diperbarui.');
    }

    public function destroy(Matrikulasi $matrikulasi)
    {
        abort_if($matrikulasi->sekolah_id !== $this->getSekolahId(), 403);
        $matrikulasi->delete();

        return redirect()->route('pengajar.matrikulasi.index')->with('success', 'Indikator Matrikulasi berhasil dihapus.');
    }
}
