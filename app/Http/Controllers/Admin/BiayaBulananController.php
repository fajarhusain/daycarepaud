<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\BiayaBulananSekolah;
use App\Models\BiayaBulananSiswa;
use App\Models\Kelas;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class BiayaBulananController extends Controller
{
    use DownloadsExcel;
    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;

        $biayaAktif = BiayaBulananSekolah::where('sekolah_id', $sekolahId)
            ->where('is_aktif', true)
            ->orderBy('nama_biaya')
            ->get();

        $biayaTerpilih = $biayaAktif->first();
        if ($request->filled('biaya_id')) {
            $biayaTerpilih = $biayaAktif->firstWhere('id', (int) $request->biaya_id) ?? $biayaTerpilih;
        }

        $kelasId = $request->input('kelas_id');

        $siswaTerassign = collect();
        $anakSudahAssignIds = collect();

        if ($biayaTerpilih) {
            $siswaQuery = BiayaBulananSiswa::query()
                ->where('biaya_bulanan_sekolah_id', $biayaTerpilih->id)
                ->with(['anak.kelas'])
                ->whereHas('anak');

            if ($kelasId) {
                $siswaQuery->whereHas('anak', fn ($q) => $q->where('kelas_id', (int) $kelasId));
            }

            $siswaTerassign = $siswaQuery
                ->join('anaks', 'biaya_bulanan_siswas.anak_id', '=', 'anaks.id')
                ->orderBy('anaks.name')
                ->select('biaya_bulanan_siswas.*')
                ->paginate(PaginationPerPage::resolve($request))
                ->withQueryString();

            $anakSudahAssignIds = BiayaBulananSiswa::where('biaya_bulanan_sekolah_id', $biayaTerpilih->id)
                ->pluck('anak_id');
        }

        $semuaAnak = Anak::where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->with('kelas')
            ->orderBy('name')
            ->get()
            ->map(fn (Anak $anak) => [
                'id' => $anak->id,
                'name' => $anak->name,
                'kelas_id' => $anak->kelas_id,
                'kelas_name' => $anak->kelas->name ?? '-',
                'sudah_assign' => $anakSudahAssignIds->contains($anak->id),
            ]);

        $kelas = Kelas::where('sekolah_id', $sekolahId)->orderBy('name')->get();
        $semuaBiaya = BiayaBulananSekolah::where('sekolah_id', $sekolahId)
            ->orderBy('nama_biaya')
            ->get();

        return view('admin.biaya-bulanan.index', compact(
            'siswaTerassign', 'biayaTerpilih', 'biayaAktif', 'kelas', 'kelasId', 'semuaBiaya', 'semuaAnak'
        ));
    }

    public function export(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $biayaAktif = BiayaBulananSekolah::where('sekolah_id', $sekolahId)->where('is_aktif', true)->orderBy('nama_biaya')->get();
        $biayaTerpilih = $biayaAktif->first();
        if ($request->filled('biaya_id')) {
            $biayaTerpilih = $biayaAktif->firstWhere('id', (int) $request->biaya_id) ?? $biayaTerpilih;
        }
        $kelasId = $request->input('kelas_id');

        $siswaTerassign = collect();
        if ($biayaTerpilih) {
            $siswaTerassign = BiayaBulananSiswa::where('biaya_bulanan_sekolah_id', $biayaTerpilih->id)
                ->with(['anak.kelas'])
                ->get()
                ->sortBy(fn (BiayaBulananSiswa $bs) => $bs->anak->name ?? '');
            if ($kelasId) {
                $siswaTerassign = $siswaTerassign->filter(
                    fn (BiayaBulananSiswa $bs) => $bs->anak && (int) $bs->anak->kelas_id === (int) $kelasId
                );
            }
        }

        $rows = $siswaTerassign->map(fn (BiayaBulananSiswa $bs) => [
            $bs->anak?->name ?? '-',
            $bs->anak?->kelas?->name ?? '-',
            (float) ($bs->biaya_bulanan ?? $biayaTerpilih?->nominal_default ?? 0),
        ])->values()->all();

        return $this->downloadExcel(
            ['Siswa', 'Kelas', 'Biaya Bulanan (Rp)'],
            $rows,
            'biaya-bulanan-'.now()->format('Y-m-d').'.xlsx',
            'Biaya Bulanan',
            [2],
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_biaya' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        BiayaBulananSekolah::create([
            'sekolah_id' => auth()->user()->sekolah_id,
            'nama_biaya' => $request->nama_biaya,
            'nominal_default' => $request->nominal_default ?? 0,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.biaya-bulanan.index')
            ->with('success', 'Jenis biaya berhasil ditambahkan.');
    }

    public function update(Request $request, BiayaBulananSekolah $biayaBulanan)
    {
        abort_if($biayaBulanan->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'nama_biaya' => 'required|string|max:255',
            'nominal_default' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
            'is_aktif' => 'boolean',
        ]);

        $biayaBulanan->update([
            'nama_biaya' => $request->nama_biaya,
            'nominal_default' => $request->nominal_default ?? 0,
            'keterangan' => $request->keterangan,
            'is_aktif' => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('admin.biaya-bulanan.index', ['biaya_id' => $biayaBulanan->id])
            ->with('success', 'Jenis biaya berhasil diperbarui.');
    }

    public function destroy(BiayaBulananSekolah $biayaBulanan)
    {
        abort_if($biayaBulanan->sekolah_id !== auth()->user()->sekolah_id, 403);

        if ($biayaBulanan->pembayaran()->exists()) {
            $biayaBulanan->update(['is_aktif' => false]);

            return redirect()->route('admin.biaya-bulanan.index')
                ->with('success', 'Jenis biaya tidak dapat dihapus karena sudah ada pembayaran. Status dinonaktifkan.');
        }

        BiayaBulananSiswa::where('biaya_bulanan_sekolah_id', $biayaBulanan->id)->delete();
        $biayaBulanan->delete();

        return redirect()->route('admin.biaya-bulanan.index')
            ->with('success', 'Jenis biaya berhasil dihapus.');
    }

    public function addSiswa(Request $request, BiayaBulananSekolah $biayaBulanan)
    {
        abort_if($biayaBulanan->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'anak_ids' => 'required|array|min:1',
            'anak_ids.*' => 'integer|exists:anaks,id',
        ]);

        $sekolahId = auth()->user()->sekolah_id;
        $validAnakIds = Anak::where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->whereIn('id', $request->anak_ids)
            ->pluck('id');

        $added = 0;
        foreach ($validAnakIds as $anakId) {
            $created = BiayaBulananSiswa::firstOrCreate(
                [
                    'anak_id' => $anakId,
                    'biaya_bulanan_sekolah_id' => $biayaBulanan->id,
                ],
                [
                    'sekolah_id' => $sekolahId,
                    'biaya_bulanan' => 0,
                ]
            );

            if ($created->wasRecentlyCreated) {
                $added++;
            }
        }

        $message = $added > 0
            ? "{$added} siswa berhasil ditambahkan."
            : 'Siswa yang dipilih sudah ada di daftar.';

        return redirect()->route('admin.biaya-bulanan.index', [
            'biaya_id' => $biayaBulanan->id,
            'kelas_id' => $request->kelas_id,
        ])->with('success', $message);
    }

    public function removeSiswa(BiayaBulananSekolah $biayaBulanan, Anak $anak)
    {
        abort_if($biayaBulanan->sekolah_id !== auth()->user()->sekolah_id, 403);
        abort_if($anak->sekolah_id !== auth()->user()->sekolah_id, 403);

        BiayaBulananSiswa::where('anak_id', $anak->id)
            ->where('biaya_bulanan_sekolah_id', $biayaBulanan->id)
            ->delete();

        return redirect()->route('admin.biaya-bulanan.index', [
            'biaya_id' => $biayaBulanan->id,
            'kelas_id' => request('kelas_id'),
        ])->with('success', 'Siswa berhasil dihapus dari daftar.');
    }

    public function updateSiswaBiayaBulanan(Request $request, BiayaBulananSekolah $biayaBulanan)
    {
        abort_if($biayaBulanan->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'biaya_bulanan' => 'required|array',
            'biaya_bulanan.*' => 'nullable|numeric|min:0',
        ]);

        $sekolahId = auth()->user()->sekolah_id;
        $anakIds = Anak::where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->pluck('id')
            ->all();

        foreach ($request->biaya_bulanan as $anakId => $biayaBulananVal) {
            $anakId = (int) $anakId;
            if (! in_array($anakId, $anakIds, true)) {
                continue;
            }

            $siswaBiaya = BiayaBulananSiswa::where('anak_id', $anakId)
                ->where('biaya_bulanan_sekolah_id', $biayaBulanan->id)
                ->first();

            if (! $siswaBiaya) {
                continue;
            }

            if ($biayaBulananVal === null || $biayaBulananVal === '') {
                $siswaBiaya->update(['biaya_bulanan' => 0]);

                continue;
            }

            $siswaBiaya->update(['biaya_bulanan' => $biayaBulananVal]);
        }

        return redirect()->route('admin.biaya-bulanan.index', [
            'biaya_id' => $biayaBulanan->id,
            'kelas_id' => $request->kelas_id,
        ])->with('success', 'Biaya bulanan siswa berhasil disimpan.');
    }
}
