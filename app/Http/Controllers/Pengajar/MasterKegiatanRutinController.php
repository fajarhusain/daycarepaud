<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Controller;
use App\Http\Traits\StoresDocumentationMedia;
use App\Models\Anak;
use App\Models\KegiatanRutin;
use App\Models\MasterKegiatanRutin;
use App\Models\Matrikulasi;
use App\Models\Pengajar;
use App\Support\DocumentationMedia;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterKegiatanRutinController extends Controller
{
    use StoresDocumentationMedia;

    public function index(Request $request)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();

        $masters = MasterKegiatanRutin::with(['kelas', 'matrikulasi'])
            ->where('sekolah_id', $pengajar->sekolah_id)
            ->whereHas('kelas', function ($q) use ($pengajar) {
                $q->whereIn('kelas.id', $pengajar->accessibleKelasIds());
            })
            ->latest()
            ->paginate(PaginationPerPage::resolve($request))
            ->withQueryString();

        return view('pengajar.master-kegiatan-rutin.index', compact('masters'));
    }

    public function create()
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();

        $classList = $pengajar->accessibleKelas();
        $matrikulasiList = Matrikulasi::where('sekolah_id', $pengajar->sekolah_id)->get();

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

        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();

        $master = MasterKegiatanRutin::create([
            'sekolah_id' => $pengajar->sekolah_id,
            'pengajar_id' => $pengajar->id,
            'nama_kegiatan' => $request->nama_kegiatan,
            'aspek' => $request->aspek,
            'matrikulasi_id' => $request->matrikulasi_id,
        ]);

        $master->kelas()->sync($request->kelas_ids);

        return redirect()->route('pengajar.master-kegiatan-rutin.index')->with('success', 'Master Kegiatan Rutin berhasil ditambahkan.');
    }

    public function edit(MasterKegiatanRutin $masterKegiatanRutin)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();

        if ($masterKegiatanRutin->pengajar_id !== $pengajar->id) {
            abort(403);
        }

        $classList = $pengajar->accessibleKelas();
        $matrikulasiList = Matrikulasi::where('sekolah_id', $pengajar->sekolah_id)->get();

        return view('pengajar.master-kegiatan-rutin.edit', compact('masterKegiatanRutin', 'classList', 'matrikulasiList'));
    }

    public function update(Request $request, MasterKegiatanRutin $masterKegiatanRutin)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();

        if ($masterKegiatanRutin->pengajar_id !== $pengajar->id) {
            abort(403);
        }

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'aspek' => 'required|string|max:255',
            'matrikulasi_id' => 'nullable|exists:matrikulasis,id',
            'kelas_ids' => 'required|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $masterKegiatanRutin->update([
            'nama_kegiatan' => $request->nama_kegiatan,
            'aspek' => $request->aspek,
            'matrikulasi_id' => $request->matrikulasi_id,
        ]);

        $masterKegiatanRutin->kelas()->sync($request->kelas_ids);

        return redirect()->route('pengajar.master-kegiatan-rutin.index')->with('success', 'Master Kegiatan Rutin berhasil diperbarui.');
    }

    public function show(Request $request, MasterKegiatanRutin $masterKegiatanRutin)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $teacherKelasIds = $pengajar->accessibleKelasIds();

        // Check if this master activity is linked to any of the teacher's classes
        $linkedToTeacher = $masterKegiatanRutin->kelas()->whereIn('kelas.id', $teacherKelasIds)->exists();

        if ($masterKegiatanRutin->pengajar_id !== $pengajar->id && ! $linkedToTeacher) {
            abort(403);
        }

        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kelasId = $request->input('kelas_id');
        $availableKelasIds = $masterKegiatanRutin->kelas()->whereIn('kelas.id', $teacherKelasIds)->pluck('kelas.id')->toArray();

        if (! $kelasId && ! empty($availableKelasIds)) {
            $kelasId = $availableKelasIds[0];
        }

        if ($kelasId && ! in_array($kelasId, $availableKelasIds)) {
            abort(403, 'Kelas tidak ditautkan ke kegiatan ini atau Anda tidak mengampu kelas ini.');
        }

        $anaks = $kelasId ? Anak::where('kelas_id', $kelasId)->get() : collect();
        $rutins = $kelasId ? KegiatanRutin::where('kelas_id', $kelasId)
            ->where('master_kegiatan_rutin_id', $masterKegiatanRutin->id)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('anak_id') : collect();

        $classList = $masterKegiatanRutin->kelas()->whereIn('kelas.id', $teacherKelasIds)->get();

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
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $teacherKelasIds = $pengajar->accessibleKelasIds();

        // Check if this master activity is linked to any of the teacher's classes
        $linkedToTeacher = $masterKegiatanRutin->kelas()->whereIn('kelas.id', $teacherKelasIds)->exists();

        if ($masterKegiatanRutin->pengajar_id !== $pengajar->id && ! $linkedToTeacher) {
            abort(403);
        }

        if (! in_array($request->kelas_id, $teacherKelasIds)) {
            abort(403, 'Anda tidak mengampu kelas ini.');
        }

        $rutin = KegiatanRutin::where([
            'sekolah_id' => $pengajar->sekolah_id,
            'kelas_id' => $request->kelas_id,
            'anak_id' => $request->anak_id,
            'master_kegiatan_rutin_id' => $masterKegiatanRutin->id,
            'tanggal' => $request->tanggal,
        ])->first();

        $data = [
            'pengajar_id' => $pengajar->id,
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
                'sekolah_id' => $pengajar->sekolah_id,
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
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $teacherKelasIds = $pengajar->accessibleKelasIds();

        // Check if student belongs to teacher's classes
        if (! in_array($anak->kelas_id, $teacherKelasIds)) {
            abort(403);
        }

        $mulai = $request->query('mulai', date('Y-m-01'));
        $sampai = $request->query('sampai', date('Y-m-t'));

        $rutins = KegiatanRutin::where('anak_id', $anak->id)
            ->where('sekolah_id', $pengajar->sekolah_id)
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
                    'photo_download_url' => $q->photo ? route('pengajar.kegiatan-rutin.photo.download', $q) : null,
                ];
            });

        return response()->json($rutins);
    }

    public function destroy(MasterKegiatanRutin $masterKegiatanRutin)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();

        if ($masterKegiatanRutin->pengajar_id !== $pengajar->id) {
            abort(403);
        }

        foreach ($masterKegiatanRutin->kegiatanRutins as $qr) {
            if ($qr->photo) {
                Storage::disk('public')->delete($qr->photo);
            }
        }

        $masterKegiatanRutin->delete();

        return redirect()->route('pengajar.master-kegiatan-rutin.index')->with('success', 'Master Kegiatan Rutin berhasil dihapus.');
    }

    public function destroyRutinRecord(KegiatanRutin $kegiatanRutin)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();

        if ($kegiatanRutin->pengajar_id !== $pengajar->id) {
            abort(403);
        }

        if ($kegiatanRutin->photo) {
            Storage::disk('public')->delete($kegiatanRutin->photo);
        }

        $kegiatanRutin->delete();

        return back()->with('success', 'Catatan pencapaian berhasil dihapus.');
    }
}
