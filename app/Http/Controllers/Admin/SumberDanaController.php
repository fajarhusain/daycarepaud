<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\SumberDana;
use Illuminate\Http\Request;

class SumberDanaController extends Controller
{
    use DownloadsExcel;
    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $query = SumberDana::query()
            ->where('sekolah_id', $sekolahId)
            ->with('akun')
            ->orderBy('urutan');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', '%'.$search.'%')
                    ->orWhere('nama', 'like', '%'.$search.'%');
            });
        }

        $status = (string) $request->input('status', '');
        if ($status === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        $sumberDanas = $query->get();
        $akunOptions = Akun::where('sekolah_id', $sekolahId)->aktif()->orderBy('kode')->get();

        return view('admin.sumber-dana.index', compact('sumberDanas', 'akunOptions'));
    }

    public function export()
    {
        $rows = SumberDana::where('sekolah_id', auth()->user()->sekolah_id)
            ->with('akun')
            ->orderBy('urutan')
            ->get()
            ->map(fn (SumberDana $s) => [
                $s->kode,
                $s->nama,
                $s->akun ? $s->akun->kode.'  '.$s->akun->nama : '-',
                $s->urutan,
                $s->is_aktif ? 'Aktif' : 'Nonaktif',
            ])
            ->all();

        return $this->downloadExcel(
            ['Kode', 'Nama', 'Akun', 'Urutan', 'Status'],
            $rows,
            'sumber-dana-'.now()->format('Y-m-d').'.xlsx',
            'Sumber Dana'
        );
    }

    public function store(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $request->validate([
            'kode' => 'required|string|max:20',
            'nama' => 'required|string|max:255',
            'akun_id' => 'nullable|integer|exists:akuns,id',
            'urutan' => 'nullable|integer|min:0',
        ]);
        $this->assertAkunSekolah($sekolahId, $request->input('akun_id'));

        SumberDana::create([
            'sekolah_id' => $sekolahId,
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'akun_id' => $request->input('akun_id') ?: null,
            'urutan' => $request->input('urutan', 99),
            'is_aktif' => true,
        ]);

        return redirect()->route('admin.sumber-dana.index')->with('success', 'Sumber dana berhasil ditambahkan.');
    }

    public function update(Request $request, SumberDana $sumberDana)
    {
        abort_if($sumberDana->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'kode' => 'required|string|max:20',
            'nama' => 'required|string|max:255',
            'akun_id' => 'nullable|integer|exists:akuns,id',
            'urutan' => 'nullable|integer|min:0',
            'is_aktif' => 'boolean',
        ]);
        $this->assertAkunSekolah($sumberDana->sekolah_id, $request->input('akun_id'));

        $sumberDana->update([
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
            'akun_id' => $request->input('akun_id') ?: null,
            'urutan' => $request->input('urutan', 0),
            'is_aktif' => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('admin.sumber-dana.index')->with('success', 'Sumber dana berhasil diperbarui.');
    }

    public function destroy(SumberDana $sumberDana)
    {
        abort_if($sumberDana->sekolah_id !== auth()->user()->sekolah_id, 403);
        $sumberDana->delete();

        return redirect()->route('admin.sumber-dana.index')->with('success', 'Sumber dana berhasil dihapus.');
    }

    private function assertAkunSekolah(int $sekolahId, mixed $akunId): void
    {
        if (! $akunId) {
            return;
        }

        abort_unless(
            Akun::where('sekolah_id', $sekolahId)->whereKey($akunId)->exists(),
            422,
            'Akun tidak termasuk sekolah ini.'
        );
    }
}
