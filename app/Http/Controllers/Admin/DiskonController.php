<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Diskon;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class DiskonController extends Controller
{
    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $diskons = Diskon::where('sekolah_id', $sekolah_id)
            ->orderBy('created_at', 'desc')
            ->paginate(PaginationPerPage::resolve($request))
            ->withQueryString();

        return view('admin.diskon.index', compact('diskons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_diskon' => 'required|string|max:255',
            'tipe' => 'required|in:persentase,nominal',
            'nilai' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        Diskon::create([
            'sekolah_id' => auth()->user()->sekolah_id,
            'nama_diskon' => $request->nama_diskon,
            'tipe' => $request->tipe,
            'nilai' => $request->nilai,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.diskon.index')->with('success', 'Diskon berhasil ditambahkan.');
    }

    public function update(Request $request, Diskon $diskon)
    {
        abort_if($diskon->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'nama_diskon' => 'required|string|max:255',
            'tipe' => 'required|in:persentase,nominal',
            'nilai' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'is_aktif' => 'boolean',
        ]);

        $diskon->update([
            'nama_diskon' => $request->nama_diskon,
            'tipe' => $request->tipe,
            'nilai' => $request->nilai,
            'keterangan' => $request->keterangan,
            'is_aktif' => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('admin.diskon.index')->with('success', 'Diskon berhasil diperbarui.');
    }

    public function destroy(Diskon $diskon)
    {
        abort_if($diskon->sekolah_id !== auth()->user()->sekolah_id, 403);
        $diskon->delete();

        return redirect()->route('admin.diskon.index')->with('success', 'Diskon berhasil dihapus.');
    }
}
