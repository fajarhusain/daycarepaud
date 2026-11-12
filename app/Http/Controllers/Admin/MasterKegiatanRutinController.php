<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Http\Traits\StoresDocumentationMedia;
use App\Models\Anak;
use App\Models\KegiatanRutin;
use App\Models\Kelas;
use App\Models\MasterKegiatanRutin;
use App\Models\Matrikulasi;
use App\Models\Pengajar;
use App\Support\DocumentationMedia;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterKegiatanRutinController extends Controller
{
    use DownloadsExcel;
    use StoresDocumentationMedia;

    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $waliKelasIds = $this->waliKelasIds();

        $masters = MasterKegiatanRutin::with(['kelas', 'matrikulasi'])
            ->where('sekolah_id', $sekolah_id)
            ->when($waliKelasIds !== null, function ($query) use ($waliKelasIds) {
                $query->whereHas('kelas', fn ($q) => $q->whereIn('kelas.id', $waliKelasIds));
            })
            ->latest()
            ->paginate(PaginationPerPage::resolve($request))
            ->withQueryString();

        return view('pengajar.master-kegiatan-rutin.index', compact('masters'));
    }

    public function export()
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $waliKelasIds = $this->waliKelasIds();

        $rows = MasterKegiatanRutin::with(['kelas', 'matrikulasi'])
            ->where('sekolah_id', $sekolah_id)
            ->when($waliKelasIds !== null, function ($query) use ($waliKelasIds) {
                $query->whereHas('kelas', fn ($q) => $q->whereIn('kelas.id', $waliKelasIds));
            })
            ->latest()
            ->get()
            ->map(fn (MasterKegiatanRutin $m) => [
                $m->nama_kegiatan,
                $m->aspek ?? '-',
                $m->matrikulasi?->indicator ?? '-',
                $m->kelas->pluck('name')->join(', ') ?: '-',
            ])
            ->all();

        return $this->downloadExcel(
            ['Nama Kegiatan', 'Aspek', 'Matrikulasi', 'Peserta (Kelas)'],
            $rows,
            'kegiatan-rutin-master-'.now()->format('Y-m-d').'.xlsx',
            'Master Kegiatan Rutin'
        );
    }

    public function create()
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        $classList = $this->kelasOptions($sekolah_id, $this->waliKelasIds());
        $matrikulasiList = Matrikulasi::where('sekolah_id', $sekolah_id)->get();

        return view('pengajar.master-kegiatan-rutin.create', compact('classList', 'matrikulasiList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'aspek' => 'required|string|max:255',
            'matrikulasi_id' => 'nullable|exists:matrikulasis,id',
            'kelas_ids' => 'required|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $this->assertKelasIdsInScope($request->kelas_ids);

        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;
        $pengajar_id = Pengajar::where('sekolah_id', $sekolah_id)->first()->id ?? null;
        if (! $pengajar_id) {
            return back()->withErrors(['error' => 'Tambahkan minimal 1 data pengajar terlebih dahulu agar bisa membuat Master Kegiatan Rutin.']);
        }

        $master = MasterKegiatanRutin::create([
            'sekolah_id' => $sekolah_id,
            'pengajar_id' => $pengajar_id,
            'nama_kegiatan' => $request->nama_kegiatan,
            'aspek' => $request->aspek,
            'matrikulasi_id' => $request->matrikulasi_id,
        ]);

        $master->kelas()->sync($request->kelas_ids);

        return redirect()->route('admin.master-kegiatan-rutin.index')->with('success', 'Master Kegiatan Rutin berhasil ditambahkan.');
    }

    public function edit(MasterKegiatanRutin $masterKegiatanRutin)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        if ($masterKegiatanRutin->sekolah_id !== $sekolah_id) {
            abort(403);
        }

        $this->assertMasterInScope($masterKegiatanRutin);

        $classList = $this->kelasOptions($sekolah_id, $this->waliKelasIds());
        $matrikulasiList = Matrikulasi::where('sekolah_id', $sekolah_id)->get();

        return view('pengajar.master-kegiatan-rutin.edit', compact('masterKegiatanRutin', 'classList', 'matrikulasiList'));
    }

    public function update(Request $request, MasterKegiatanRutin $masterKegiatanRutin)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        if ($masterKegiatanRutin->sekolah_id !== $sekolah_id) {
            abort(403);
        }

        $this->assertMasterInScope($masterKegiatanRutin);

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'aspek' => 'required|string|max:255',
            'matrikulasi_id' => 'nullable|exists:matrikulasis,id',
            'kelas_ids' => 'required|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $this->assertKelasIdsInScope($request->kelas_ids);

        $masterKegiatanRutin->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'aspek' => $request->aspek,
            'matrikulasi_id' => $request->matrikulasi_id,
        ]);

        $masterKegiatanRutin->kelas()->sync($request->kelas_ids);

        return redirect()->route('admin.master-kegiatan-rutin.index')->with('success', 'Master Kegiatan Rutin berhasil diperbarui.');
    }

    public function show(Request $request, MasterKegiatanRutin $masterKegiatanRutin)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        if ($masterKegiatanRutin->sekolah_id !== $sekolah_id) {
            abort(403);
        }

        $this->assertMasterInScope($masterKegiatanRutin);

        $waliKelasIds = $this->waliKelasIds();
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kelasId = $request->input('kelas_id');
        $masterKelasIds = $masterKegiatanRutin->kelas->pluck('id')->toArray();
        $availableKelasIds = $waliKelasIds !== null
            ? array_values(array_intersect($masterKelasIds, $waliKelasIds))
            : $masterKelasIds;

        if (! $kelasId && ! empty($availableKelasIds)) {
            $kelasId = $availableKelasIds[0];
        }

        if ($kelasId && ! in_array((int) $kelasId, $masterKelasIds, true)) {
            abort(403, 'Kelas tidak ditautkan ke kegiatan ini.');
        }

        if ($kelasId && $waliKelasIds !== null && ! in_array((int) $kelasId, $waliKelasIds, true)) {
            abort(403, 'Kelas tidak dalam cakupan Anda.');
        }

        $anaks = $kelasId ? Anak::where('kelas_id', $kelasId)->get() : collect();
        $rutins = $kelasId ? KegiatanRutin::where('kelas_id', $kelasId)
            ->where('master_kegiatan_rutin_id', $masterKegiatanRutin->id)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('anak_id') : collect();

        $classList = $masterKegiatanRutin->kelas()
            ->when($waliKelasIds !== null, fn ($q) => $q->whereIn('kelas.id', $waliKelasIds))
            ->get();

        return view('pengajar.master-kegiatan-rutin.show', compact('masterKegiatanRutin', 'classList', 'anaks', 'rutins', 'tanggal', 'kelasId'));
    }

    public function storeRutin(Request $request, MasterKegiatanRutin $masterKegiatanRutin)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'anak_id' => 'required|exists:anaks,id',
            'status_pencapaian' => 'required|string',
            'keterangan' => 'nullable|string',
            'photo' => DocumentationMedia::singleFileRules(),
        ]);

        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        if ($masterKegiatanRutin->sekolah_id !== $sekolah_id) {
            abort(403);
        }

        $this->assertMasterInScope($masterKegiatanRutin);
        $this->assertKelasIdInScope((int) $request->kelas_id);

        $pengajar_id = $masterKegiatanRutin->pengajar_id ?? (Pengajar::where('sekolah_id', $sekolah_id)->first()->id ?? null);

        $rutin = KegiatanRutin::where([
            'sekolah_id' => $sekolah_id,
            'kelas_id' => $request->kelas_id,
            'anak_id' => $request->anak_id,
            'master_kegiatan_rutin_id' => $masterKegiatanRutin->id,
            'tanggal' => $request->tanggal,
        ])->first();

        $data = [
            'pengajar_id' => $pengajar_id,
            'aspek' => $masterKegiatanRutin->aspek,
            'kegiatan' => $masterKegiatanRutin->nama_kegiatan,
            'status_pencapaian' => $request->status_pencapaian,
            'keterangan' => $request->keterangan,
        ];

        if ($request->hasFile('photo')) {
            if ($rutin && $rutin->photo) {
                Storage::disk('public')->delete($rutin->photo);
            }
            $data['photo'] = $this->storeDocumentationMedia($request->file('photo'), 'kegiatan-rutin', 'kegiatan-rutin-videos');
        }

        if ($rutin) {
            $rutin->update($data);
        } else {
            KegiatanRutin::create(array_merge($data, [
                'sekolah_id' => $sekolah_id,
                'kelas_id' => $request->kelas_id,
                'anak_id' => $request->anak_id,
                'master_kegiatan_rutin_id' => $masterKegiatanRutin->id,
                'tanggal' => $request->tanggal,
            ]));
        }

        return back()->with('success', 'Data pencapaian berhasil disimpan.');
    }

    public function detail(Request $request, MasterKegiatanRutin $masterKegiatanRutin, Anak $anak)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        $this->assertMasterInScope($masterKegiatanRutin);
        $this->assertAnakInScope($anak);

        $mulai = $request->query('mulai', date('Y-m-01'));
        $sampai = $request->query('sampai', date('Y-m-t'));

        $rutins = KegiatanRutin::where('anak_id', $anak->id)
            ->where('sekolah_id', $sekolah_id)
            ->where('master_kegiatan_rutin_id', $masterKegiatanRutin->id)
            ->whereBetween('tanggal', [$mulai, $sampai])
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'tanggal_formatted' => $q->tanggal->format('d M Y'),
                    'tanggal' => $q->tanggal->format('Y-m-d'),
                    'status_pencapaian' => $q->status_pencapaian,
                    'keterangan' => $q->keterangan,
                    'photo_url' => $q->photo ? Storage::url($q->photo) : null,
                ];
            });

        return response()->json($rutins);
    }

    public function destroy(MasterKegiatanRutin $masterKegiatanRutin)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        if ($masterKegiatanRutin->sekolah_id !== $sekolah_id) {
            abort(403);
        }

        $this->assertMasterInScope($masterKegiatanRutin);

        foreach ($masterKegiatanRutin->kegiatanRutins as $qr) {
            if ($qr->photo) {
                Storage::disk('public')->delete($qr->photo);
            }
        }

        $masterKegiatanRutin->delete();

        return redirect()->route('admin.master-kegiatan-rutin.index')->with('success', 'Master Kegiatan Rutin berhasil dihapus.');
    }

    public function destroyRutinRecord(KegiatanRutin $kegiatanRutin)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        if ($kegiatanRutin->sekolah_id !== $sekolah_id) {
            abort(403);
        }

        $this->assertKelasIdInScope((int) $kegiatanRutin->kelas_id);

        if ($kegiatanRutin->photo) {
            Storage::disk('public')->delete($kegiatanRutin->photo);
        }

        $kegiatanRutin->delete();

        return back()->with('success', 'Catatan pencapaian berhasil dihapus.');
    }

    /** @return list<int>|null */
    private function waliKelasIds(): ?array
    {
        return auth()->user()->scopedKelasIds();
    }

    /** @param  list<int>|null  $waliKelasIds */
    private function kelasOptions(int $sekolahId, ?array $waliKelasIds)
    {
        $query = Kelas::where('sekolah_id', $sekolahId)->orderBy('name');
        if ($waliKelasIds !== null) {
            $query->whereIn('id', $waliKelasIds);
        }

        return $query->get();
    }

    private function assertMasterInScope(MasterKegiatanRutin $master): void
    {
        $waliKelasIds = $this->waliKelasIds();
        if ($waliKelasIds === null) {
            return;
        }

        abort_if($waliKelasIds === [], 403);

        $linked = $master->kelas()->whereIn('kelas.id', $waliKelasIds)->exists();
        abort_unless($linked, 403);
    }

    /** @param  list<int>  $kelasIds */
    private function assertKelasIdsInScope(array $kelasIds): void
    {
        $waliKelasIds = $this->waliKelasIds();
        if ($waliKelasIds === null) {
            return;
        }

        abort_if($waliKelasIds === [], 403);

        foreach ($kelasIds as $kelasId) {
            abort_unless(in_array((int) $kelasId, $waliKelasIds, true), 403);
        }
    }

    private function assertKelasIdInScope(int $kelasId): void
    {
        $waliKelasIds = $this->waliKelasIds();
        if ($waliKelasIds === null) {
            return;
        }

        abort_if($waliKelasIds === [], 403);
        abort_unless(in_array($kelasId, $waliKelasIds, true), 403);
    }

    private function assertAnakInScope(Anak $anak): void
    {
        $this->assertKelasIdInScope((int) $anak->kelas_id);
    }
}
