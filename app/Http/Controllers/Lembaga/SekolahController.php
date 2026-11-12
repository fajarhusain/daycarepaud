<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Models\KomponenGaji;
use App\Models\MonevGuruKriteria;
use App\Models\Sekolah;
use App\Models\SkalaPencapaian;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index(Request $request)
    {
        $lembaga_id = auth()->user()->lembaga_id;
        $sekolahs = Sekolah::where('lembaga_id', $lembaga_id)->latest()->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('lembaga.sekolah.index', compact('sekolahs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $sekolah = Sekolah::create([
            'lembaga_id' => auth()->user()->lembaga_id,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'status' => Sekolah::STATUS_ACTIVE,
        ]);

        SkalaPencapaian::seedDefaultsForSekolah($sekolah->id);
        MonevGuruKriteria::seedDefaultsForSekolah($sekolah->id);
        KomponenGaji::seedDefaultsForSekolah($sekolah->id);

        return redirect()->route('lembaga.sekolah.index')->with('success', 'Sekolah berhasil ditambahkan.');
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        abort_if($sekolah->lembaga_id !== auth()->user()->lembaga_id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $sekolah->update($request->only('name', 'address', 'phone'));

        return redirect()->route('lembaga.sekolah.index')->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        abort_if($sekolah->lembaga_id !== auth()->user()->lembaga_id, 403);
        $sekolah->delete();

        return redirect()->route('lembaga.sekolah.index')->with('success', 'Sekolah berhasil dihapus.');
    }
}
