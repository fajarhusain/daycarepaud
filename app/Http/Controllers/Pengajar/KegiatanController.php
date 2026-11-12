<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Concerns\DownloadsPhotoArchive;
use App\Http\Controllers\Controller;
use App\Http\Traits\StoresDocumentationMedia;
use App\Models\Anak;
use App\Models\Kegiatan;
use App\Models\Matrikulasi;
use App\Models\Pengajar;
use App\Services\PhotoArchiveService;
use App\Support\DocumentationMedia;
use App\Support\KegiatanCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KegiatanController extends Controller
{
    use DownloadsPhotoArchive;
    use StoresDocumentationMedia;

    private function getPengajar()
    {
        return Pengajar::where('user_id', auth()->id())->firstOrFail();
    }

    public function index(Request $request)
    {
        $pengajar = $this->getPengajar();
        $sekolah_id = $pengajar->sekolah_id;

        [$year, $month] = KegiatanCalendar::resolveYearMonth($request);
        [$from, $to] = KegiatanCalendar::dateRangeForCalendar($year, $month);

        $kelas = $pengajar->accessibleKelas();
        $kelasIds = $pengajar->accessibleKelasIds();

        $query = Kegiatan::query()
            ->whereIn('kelas_id', $kelasIds)
            ->with(['matrikulasis', 'pencapaians.anak', 'pencapaians.matrikulasi'])
            ->whereBetween('date', [$from, $to]);

        if ($request->filled('kelas_id')) {
            $kid = $request->integer('kelas_id');
            if (in_array($kid, $kelasIds, true)) {
                $query->where('kelas_id', $kid);
            }
        } else {
            $query->whereIn('kelas_id', $kelasIds);
        }

        if ($request->filled('matrikulasi_id')) {
            $mid = $request->integer('matrikulasi_id');
            if (Matrikulasi::where('id', $mid)->where('sekolah_id', $sekolah_id)->exists()) {
                $query->whereHas('matrikulasis', fn ($q) => $q->where('matrikulasis.id', $mid));
            }
        }

        if ($request->filled('day')) {
            $day = $request->integer('day');
            $query->whereDay('date', $day);
        }

        $kegiatans = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $calendarEvents = $kegiatans->map(fn (Kegiatan $k) => KegiatanCalendar::toPengajarEvent($k))->values()->all();

        $matrikulasis = Matrikulasi::where('sekolah_id', $sekolah_id)->orderBy('aspek')->get();
        $anaks = Anak::where('sekolah_id', $sekolah_id)->orderBy('name')->get();

        return view('pengajar.kegiatan.index', compact('calendarEvents', 'year', 'month', 'matrikulasis', 'anaks', 'kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => DocumentationMedia::multiFileItemRules(),
            'kelas_id' => 'required|exists:kelas,id',
            'matrikulasi_ids' => 'required|array|min:1',
            'matrikulasi_ids.*' => 'exists:matrikulasis,id',
        ]);

        $pengajar = $this->getPengajar();
        abort_if(! in_array((int) $request->kelas_id, $pengajar->accessibleKelasIds(), true), 403);

        $data = [
            'sekolah_id' => $pengajar->sekolah_id,
            'pengajar_id' => $pengajar->id,
            'kelas_id' => $request->kelas_id,
            'date' => $request->date,
            'title' => $request->title,
            'description' => $request->description,
        ];

        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $file) {
                $photos[] = $this->storeDocumentationMedia($file, 'kegiatan', 'kegiatan-videos');
            }
            $data['photos'] = $photos;
        }

        $kegiatan = Kegiatan::create($data);

        if ($request->matrikulasi_ids) {
            $kegiatan->matrikulasis()->sync($request->matrikulasi_ids);
        }

        return redirect()->route('pengajar.kegiatan.index')->with('success', 'Kegiatan berhasil dicatat.');
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $pengajar = $this->getPengajar();
        $kelasIds = $pengajar->accessibleKelasIds();
        abort_if(! in_array($kegiatan->kelas_id, $kelasIds), 403);

        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => DocumentationMedia::multiFileItemRules(),
            'kelas_id' => 'required|exists:kelas,id',
            'matrikulasi_ids' => 'required|array|min:1',
            'matrikulasi_ids.*' => 'exists:matrikulasis,id',
        ]);

        abort_if(! in_array((int) $request->kelas_id, $kelasIds), 403);

        $data = [
            'date' => $request->date,
            'title' => $request->title,
            'description' => $request->description,
            'kelas_id' => $request->kelas_id,
        ];

        $currentPhotos = $kegiatan->photos ?? [];

        // Handle deletions first
        if ($request->filled('delete_photos')) {
            foreach ($request->delete_photos as $p) {
                Storage::disk('public')->delete($p);
                $currentPhotos = array_values(array_filter($currentPhotos, fn ($path) => $path !== $p));
            }
        }

        // Handle new uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $currentPhotos[] = $this->storeDocumentationMedia($file, 'kegiatan', 'kegiatan-videos');
            }
        }

        $data['photos'] = $currentPhotos;

        $kegiatan->update($data);
        $kegiatan->matrikulasis()->sync($request->matrikulasi_ids ?? []);

        return redirect()->route('pengajar.kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $pengajar = $this->getPengajar();
        $kelasIds = $pengajar->accessibleKelasIds();
        abort_if(! in_array($kegiatan->kelas_id, $kelasIds), 403);

        if ($kegiatan->photo) {
            Storage::disk('public')->delete($kegiatan->photo);
        }
        $kegiatan->delete();

        return redirect()->route('pengajar.kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function downloadPhotos(Request $request, PhotoArchiveService $photoArchive)
    {
        $kegiatans = $this->filteredKegiatansQuery($request)->get();
        $entries = $photoArchive->entriesFromKegiatans($kegiatans);
        [$year, $month] = KegiatanCalendar::resolveYearMonth($request);

        return $this->downloadPhotoArchive($entries, sprintf('foto-agenda-belajar-%04d-%02d.zip', $year, $month));
    }

    public function downloadPhotosSingle(Request $request, Kegiatan $kegiatan, PhotoArchiveService $photoArchive)
    {
        $pengajar = $this->getPengajar();
        $kelasIds = $pengajar->accessibleKelasIds();
        abort_if(! in_array((int) $kegiatan->kelas_id, $kelasIds, true), 403);

        return $this->downloadKegiatanPhotoAtIndex($kegiatan, (int) $request->input('index', 0), $photoArchive);
    }

    protected function downloadKegiatanPhotoAtIndex(Kegiatan $kegiatan, int $index, PhotoArchiveService $photoArchive)
    {
        $photos = collect($kegiatan->photos ?? [])->filter()->values();
        abort_if($photos->isEmpty(), 404, 'Foto dokumentasi tidak ditemukan.');
        abort_if(! isset($photos[$index]), 404, 'Foto dokumentasi tidak ditemukan.');

        $date = $kegiatan->date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $filename = $photoArchive->slugFilenameForStoragePath($date, $kegiatan->title ?? 'kegiatan', $index + 1, $photos[$index]);

        return $photoArchive->downloadPublicFile($photos[$index], $filename);
    }

    protected function filteredKegiatansQuery(Request $request)
    {
        $pengajar = $this->getPengajar();
        $kelasIds = $pengajar->accessibleKelasIds();
        [$year, $month] = KegiatanCalendar::resolveYearMonth($request);
        [$from, $to] = KegiatanCalendar::dateRangeForCalendar($year, $month);

        $query = Kegiatan::query()
            ->whereIn('kelas_id', $kelasIds)
            ->whereBetween('date', [$from, $to]);

        if ($request->filled('kelas_id')) {
            $kid = $request->integer('kelas_id');
            if (in_array($kid, $kelasIds, true)) {
                $query->where('kelas_id', $kid);
            }
        }

        if ($request->filled('matrikulasi_id')) {
            $mid = $request->integer('matrikulasi_id');
            if (Matrikulasi::where('id', $mid)->where('sekolah_id', $pengajar->sekolah_id)->exists()) {
                $query->whereHas('matrikulasis', fn ($q) => $q->where('matrikulasis.id', $mid));
            }
        }

        if ($request->filled('day')) {
            $query->whereDay('date', $request->integer('day'));
        }

        return $query->orderBy('date', 'desc')->orderBy('id', 'desc');
    }
}
