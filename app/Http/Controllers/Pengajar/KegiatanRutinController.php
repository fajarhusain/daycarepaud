<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Concerns\DownloadsPhotoArchive;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\KegiatanRutin;
use App\Models\MasterKegiatanRutin;
use App\Models\Pengajar;
use App\Services\PhotoArchiveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KegiatanRutinController extends Controller
{
    use DownloadsPhotoArchive;
    use DownloadsPublicPhoto;
    public function index(Request $request)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $sekolahId = $pengajar->sekolah_id;
        $kelasIds = $pengajar->accessibleKelasIds();

        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kelasId = $request->input('kelas_id');

        if (! $kelasId && ! empty($kelasIds)) {
            $kelasId = $kelasIds[0];
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

        $classList = $pengajar->accessibleKelas();

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

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'rutin' => 'required|array',
        ]);

        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();

        foreach ($request->rutin as $anakId => $mastersData) {
            foreach ($mastersData as $masterId => $data) {
                if (empty($data['status_pencapaian'])) {
                    continue;
                }

                KegiatanRutin::updateOrCreate(
                    [
                        'sekolah_id' => $pengajar->sekolah_id,
                        'kelas_id' => $request->kelas_id,
                        'anak_id' => $anakId,
                        'master_kegiatan_rutin_id' => $masterId == 'custom' ? null : $masterId,
                        'tanggal' => $request->tanggal,
                    ],
                    [
                        'pengajar_id' => $pengajar->id,
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
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();

        $mulai = $request->query('mulai', date('Y-m-01'));
        $sampai = $request->query('sampai', date('Y-m-t'));

        $rutins = KegiatanRutin::where('anak_id', $anak->id)
            ->where('sekolah_id', $pengajar->sekolah_id)
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
                    'photo_download_url' => $q->photo ? route('pengajar.kegiatan-rutin.photo.download', $q) : null,
                ];
            });

        return response()->json($rutins);
    }

    public function downloadPhoto(KegiatanRutin $kegiatan_rutin, PhotoArchiveService $photoArchive)
    {
        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();
        abort_if($kegiatan_rutin->sekolah_id !== $pengajar->sekolah_id, 403);
        abort_if(! in_array((int) $kegiatan_rutin->kelas_id, $pengajar->accessibleKelasIds(), true), 403);

        return $this->downloadPublicPhoto(
            $photoArchive,
            $kegiatan_rutin->photo,
            $this->slugPhotoFilename(
                'kegiatan-rutin-'.$kegiatan_rutin->tanggal?->format('Y-m-d'),
                $kegiatan_rutin->photo
            )
        );
    }

    public function downloadPhotos(Request $request, PhotoArchiveService $photoArchive)
    {
        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();
        $kelasIds = $pengajar->accessibleKelasIds();
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $kelasId = $request->input('kelas_id') ?: ($kelasIds[0] ?? null);

        abort_if($kelasId && ! empty($kelasIds) && ! in_array((int) $kelasId, $kelasIds, true), 403);

        $records = KegiatanRutin::query()
            ->with(['anak', 'masterKegiatanRutin'])
            ->whereNotNull('photo')
            ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
            ->when(! empty($kelasIds), fn ($q) => $q->whereIn('kelas_id', $kelasIds))
            ->where('tanggal', $tanggal)
            ->get();

        $entries = $photoArchive->entriesFromKegiatanRutin($records);

        return $this->downloadPhotoArchive($entries, 'foto-kegiatan-rutin-'.$tanggal.'.zip');
    }
}
