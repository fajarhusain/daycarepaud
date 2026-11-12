<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KomponenGajiRequest;
use App\Models\GajiPengajar;
use App\Models\KomponenGaji;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class KomponenGajiController extends Controller
{
    private function sekolahId(): ?int
    {
        $id = auth()->user()->sekolah_id;

        return $id !== null ? (int) $id : null;
    }

    public function index(Request $request)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403, 'Akun tidak terikat sekolah.');

        $komponens = KomponenGaji::query()
            ->where('sekolah_id', $sekolahId)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->paginate(PaginationPerPage::resolve($request))
            ->withQueryString();

        return view('admin.gaji-guru.komponen.index', compact('komponens'));
    }

    public function store(KomponenGajiRequest $request)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        KomponenGaji::create([
            'sekolah_id' => $sekolahId,
            'nama' => $request->string('nama')->toString(),
            'jenis' => $request->string('jenis')->toString(),
            'nominal_default' => (float) $request->input('nominal_default', 0),
            'urutan' => (int) $request->input('urutan', 0),
            'is_aktif' => $request->boolean('is_aktif', true),
        ]);

        return redirect()->route('admin.gaji-guru.komponen.index')
            ->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    public function update(KomponenGajiRequest $request, KomponenGaji $komponen_gaji)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $komponen_gaji->sekolah_id !== $sekolahId, 403);

        $komponen_gaji->update([
            'nama' => $request->string('nama')->toString(),
            'jenis' => $request->string('jenis')->toString(),
            'nominal_default' => (float) $request->input('nominal_default', 0),
            'urutan' => (int) $request->input('urutan', 0),
            'is_aktif' => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('admin.gaji-guru.komponen.index')
            ->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    public function destroy(KomponenGaji $komponen_gaji)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $komponen_gaji->sekolah_id !== $sekolahId, 403);

        $nama = $komponen_gaji->nama;

        $inUse = GajiPengajar::query()
            ->where('sekolah_id', $sekolahId)
            ->where('status', GajiPengajar::STATUS_APPROVED)
            ->get()
            ->contains(function (GajiPengajar $gaji) use ($nama) {
                return collect($gaji->komponen ?? [])
                    ->contains(fn ($row) => ($row['nama'] ?? '') === $nama);
            });

        if ($inUse) {
            return redirect()->route('admin.gaji-guru.komponen.index')
                ->withErrors(['delete' => 'Komponen masih tercatat di slip gaji yang sudah disetujui. Nonaktifkan saja.']);
        }

        $komponen_gaji->delete();

        return redirect()->route('admin.gaji-guru.komponen.index')
            ->with('success', 'Komponen gaji berhasil dihapus.');
    }
}
