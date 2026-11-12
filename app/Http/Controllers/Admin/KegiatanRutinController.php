<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Concerns\DownloadsPhotoArchive;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\KegiatanRutin;
use App\Models\Kelas;
use App\Models\MasterKegiatanRutin;
use App\Models\Pengajar;
use App\Services\PhotoArchiveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KegiatanRutinController extends Controller
{
    use DownloadsExcel;
    use DownloadsPhotoArchive;
    use DownloadsPublicPhoto;

    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolahId = $user->sekolah_id;
        $classList = $this->kelasOptions($sekolahId, $this->waliKelasIds());
        $kelasIds = $classList->pluck('id')->toArray();

        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kelasId = $request->input('kelas_id');

        if (! $kelasId && ! empty($kelasIds)) {
            $kelasId = $kelasIds[0];
        }

        if ($kelasId && $this->waliKelasIds() !== null && ! in_array((int) $kelasId, $kelasIds, true)) {
            abort(403);
        }

        $anaks = $kelasId ? Anak::where('kelas_id', $kelasId)->get() : collect();

        $masters = $kelasId ? MasterKegiatanRutin::whereHas('kelas', function ($query) use ($kelasId) {
            $query->where('kelas.id', $kelasId);
        })->get() : collect();

        $rutinGrid = KegiatanRutin::gridMapForKelas($kelasId, $tanggal);
        $rutinGridYesterday = KegiatanRutin::gridMapForKelas(
            $kelasId,
            Carbon::parse($tanggal)->subDay()->format('Y-m-d')
        );
        $statusOptions = KegiatanRutin::statusOptions();

        return view('pengajar.kegiatan-rutin.index', compact(
            'classList',
            'anaks',
            'rutinGrid',
            'rutinGridYesterday',
            'statusOptions',
            'tanggal',
            'kelasId',
            'masters'
        ));
    }

    public function export(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $classList = $this->kelasOptions($sekolahId, $this->waliKelasIds());
        $kelasIds = $classList->pluck('id')->toArray();
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kelasId = $request->input('kelas_id') ?: ($kelasIds[0] ?? null);

        if ($kelasId && $this->waliKelasIds() !== null && ! in_array((int) $kelasId, $kelasIds, true)) {
            abort(403);
        }

        $anaks = $kelasId ? Anak::where('kelas_id', $kelasId)->get() : collect();
        $masters = $kelasId ? MasterKegiatanRutin::whereHas('kelas', fn ($q) => $q->where('kelas.id', $kelasId))->get() : collect();
        $rutins = $kelasId ? KegiatanRutin::where('kelas_id', $kelasId)->where('tanggal', $tanggal)->get()->keyBy(fn ($r) => $r->anak_id.'_'.$r->master_kegiatan_rutin_id) : collect();

        $rows = [];
        foreach ($anaks as $anak) {
            foreach ($masters as $master) {
                $rutin = $rutins->get($anak->id.'_'.$master->id);
                $rows[] = [
                    $anak->name,
                    $master->aspek ?? '-',
                    $master->nama_kegiatan,
                    $rutin?->status_pencapaian ?? 'Belum diisi',
                ];
            }
        }

        return $this->downloadExcel(
            ['Nama Siswa', 'Aspek', 'Kegiatan', 'Status Pencapaian'],
            $rows,
            'input-rutin-harian-'.$tanggal.'.xlsx',
            'Input Rutin Harian'
        );
    }

    public function downloadPhotos(Request $request, PhotoArchiveService $photoArchive)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $classList = $this->kelasOptions($sekolahId, $this->waliKelasIds());
        $kelasIds = $classList->pluck('id')->toArray();
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kelasId = $request->input('kelas_id') ?: ($kelasIds[0] ?? null);

        if ($kelasId && $this->waliKelasIds() !== null && ! in_array((int) $kelasId, $kelasIds, true)) {
            abort(403);
        }

        $records = KegiatanRutin::query()
            ->with(['anak', 'masterKegiatanRutin'])
            ->whereNotNull('photo')
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->where('tanggal', $tanggal)
            ->get();

        $entries = $photoArchive->entriesFromKegiatanRutin($records);

        return $this->downloadPhotoArchive($entries, 'foto-kegiatan-rutin-'.$tanggal.'.zip');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'rutin' => 'required|array',
        ]);

        $this->assertKelasIdInScope((int) $request->kelas_id);

        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;
        $pengajar_id = Pengajar::where('sekolah_id', $sekolah_id)->first()->id ?? null;
        if (! $pengajar_id) {
            return back()->withErrors(['error' => 'Tambahkan minimal 1 data pengajar terlebih dahulu.']);
        }

        foreach ($request->rutin as $anakId => $mastersData) {
            foreach ($mastersData as $masterId => $data) {
                if (empty($data['status_pencapaian'])) {
                    continue;
                }

                KegiatanRutin::updateOrCreate(
                    [
                        'sekolah_id' => $sekolah_id,
                        'kelas_id' => $request->kelas_id,
                        'anak_id' => $anakId,
                        'master_kegiatan_rutin_id' => $masterId == 'custom' ? null : $masterId,
                        'tanggal' => $request->tanggal,
                    ],
                    [
                        'pengajar_id' => $pengajar_id,
                        'aspek' => $data['aspek'] ?? '',
                        'kegiatan' => $data['kegiatan'] ?? '',
                        'status_pencapaian' => $data['status_pencapaian'],
                    ]
                );
            }
        }

        return back()->with('success', 'Kegiatan rutin berhasil diperbarui.');
    }

    public function detail(Request $request, Anak $anak)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        $this->assertAnakInScope($anak);

        $mulai = $request->query('mulai', date('Y-m-01'));
        $sampai = $request->query('sampai', date('Y-m-t'));

        $rutins = KegiatanRutin::where('anak_id', $anak->id)
            ->where('sekolah_id', $sekolah_id)
            ->whereBetween('tanggal', [$mulai, $sampai])
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'tanggal_formatted' => $q->tanggal->format('d M Y'),
                    'tanggal' => $q->tanggal->format('Y-m-d'),
                    'aspek' => $q->aspek,
                    'kegiatan' => $q->kegiatan,
                    'status_pencapaian' => $q->status_pencapaian,
                    'keterangan' => $q->keterangan,
                    'photo_url' => $q->photo ? Storage::url($q->photo) : null,
                    'photo_download_url' => $q->photo ? route('admin.kegiatan-rutin.photo.download', $q) : null,
                ];
            });

        return response()->json($rutins);
    }

    public function downloadPhoto(KegiatanRutin $kegiatan_rutin, PhotoArchiveService $photoArchive)
    {
        abort_if($kegiatan_rutin->sekolah_id !== auth()->user()->sekolah_id, 403);
        $this->assertKelasIdInScope((int) $kegiatan_rutin->kelas_id);

        return $this->downloadPublicPhoto(
            $photoArchive,
            $kegiatan_rutin->photo,
            $this->slugPhotoFilename(
                'kegiatan-rutin-'.$kegiatan_rutin->tanggal?->format('Y-m-d'),
                $kegiatan_rutin->photo
            )
        );
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
