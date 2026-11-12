<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Concerns\DownloadsPhotoArchive;
use App\Http\Controllers\Controller;
use App\Http\Traits\StoresDocumentationMedia;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Matrikulasi;
use App\Models\Pengajar;
use App\Services\PhotoArchiveService;
use App\Support\DocumentationMedia;
use App\Support\KegiatanCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KegiatanController extends Controller
{
    use DownloadsExcel;
    use DownloadsPhotoArchive;
    use StoresDocumentationMedia;

    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        [$year, $month] = KegiatanCalendar::resolveYearMonth($request);
        [$from, $to] = KegiatanCalendar::dateRangeForCalendar($year, $month);

        $query = Kegiatan::query()
            ->where('sekolah_id', $sekolah_id)
            ->with(['pengajar', 'kelas'])
            ->whereBetween('date', [$from, $to]);
        auth()->user()->applyScopedKelas($query);

        if ($request->filled('pengajar_id')) {
            $pid = $request->integer('pengajar_id');
            $query->where('pengajar_id', $pid);
        }

        $kelas = auth()->user()->applyScopedKelas(Kelas::where('sekolah_id', $sekolah_id)->orderBy('name'), 'id')->get();

        if ($request->filled('kelas_id')) {
            $kid = $request->integer('kelas_id');
            $query->where('kelas_id', $kid);
        }

        if ($request->filled('day')) {
            $day = $request->integer('day');
            $query->whereDay('date', $day);
        }

        $kegiatans = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $calendarEvents = $kegiatans->map(fn (Kegiatan $k) => KegiatanCalendar::toAdminEvent($k))->values()->all();

        $pengajars = Pengajar::where('sekolah_id', $sekolah_id)->orderBy('name')->get();
        $matrikulasis = Matrikulasi::where('sekolah_id', $sekolah_id)->orderBy('aspek')->get();

        return view('admin.kegiatan.index', compact('calendarEvents', 'year', 'month', 'pengajars', 'kelas', 'matrikulasis'));
    }

    public function export(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        [$year, $month] = KegiatanCalendar::resolveYearMonth($request);
        [$from, $to] = KegiatanCalendar::dateRangeForCalendar($year, $month);

        $query = Kegiatan::query()
            ->where('sekolah_id', $sekolah_id)
            ->with(['pengajar', 'kelas'])
            ->whereBetween('date', [$from, $to]);

        if ($request->filled('pengajar_id')) {
            $query->where('pengajar_id', $request->integer('pengajar_id'));
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }
        if ($request->filled('day')) {
            $query->whereDay('date', $request->integer('day'));
        }

        $rows = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get()->map(fn (Kegiatan $k) => [
            $k->date?->format('Y-m-d') ?? '-',
            $k->title ?? '-',
            $k->kelas?->name ?? '-',
            $k->pengajar?->name ?? '-',
            $k->description ?? '-',
        ])->all();

        return $this->downloadExcel(
            ['Tanggal', 'Judul', 'Kelas', 'Pengajar', 'Deskripsi'],
            $rows,
            sprintf('agenda-belajar-%04d-%02d.xlsx', $year, $month),
            'Agenda Belajar'
        );
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
        $sekolah_id = auth()->user()->sekolah_id;
        abort_if($kegiatan->sekolah_id !== $sekolah_id, 403);

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
        $sekolah_id = auth()->user()->sekolah_id;
        [$year, $month] = KegiatanCalendar::resolveYearMonth($request);
        [$from, $to] = KegiatanCalendar::dateRangeForCalendar($year, $month);

        $query = Kegiatan::query()
            ->where('sekolah_id', $sekolah_id)
            ->whereBetween('date', [$from, $to]);

        if ($request->filled('pengajar_id')) {
            $query->where('pengajar_id', $request->integer('pengajar_id'));
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }
        if ($request->filled('day')) {
            $query->whereDay('date', $request->integer('day'));
        }

        return $query->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function store(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => DocumentationMedia::multiFileItemRules(),
            'kelas_id' => 'required|exists:kelas,id',
            'pengajar_id' => 'required|exists:pengajars,id',
            'matrikulasi_ids' => 'required|array|min:1',
            'matrikulasi_ids.*' => 'exists:matrikulasis,id',
        ]);

        $data = [
            'sekolah_id' => $sekolah_id,
            'pengajar_id' => $request->pengajar_id,
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
        $kegiatan->matrikulasis()->sync($request->matrikulasi_ids);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil dicatat.');
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        abort_if($kegiatan->sekolah_id !== $sekolah_id, 403);

        $request->validate([
            'date' => 'required|date',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => DocumentationMedia::multiFileItemRules(),
            'kelas_id' => 'required|exists:kelas,id',
            'pengajar_id' => 'required|exists:pengajars,id',
            'matrikulasi_ids' => 'required|array|min:1',
            'matrikulasi_ids.*' => 'exists:matrikulasis,id',
        ]);

        $data = [
            'date' => $request->date,
            'title' => $request->title,
            'description' => $request->description,
            'kelas_id' => $request->kelas_id,
            'pengajar_id' => $request->pengajar_id,
        ];

        $currentPhotos = $kegiatan->photos ?? [];
        if ($request->filled('delete_photos')) {
            foreach ($request->delete_photos as $p) {
                Storage::disk('public')->delete($p);
                $currentPhotos = array_values(array_filter($currentPhotos, fn ($path) => $path !== $p));
            }
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $currentPhotos[] = $this->storeDocumentationMedia($file, 'kegiatan', 'kegiatan-videos');
            }
        }
        $data['photos'] = $currentPhotos;

        $kegiatan->update($data);
        $kegiatan->matrikulasis()->sync($request->matrikulasi_ids ?? []);

        return redirect()->route('admin.kegiatan.index', ['year' => explode('-', $request->date)[0], 'month' => explode('-', $request->date)[1]])->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        abort_if($kegiatan->sekolah_id !== $sekolah_id, 403);

        if ($kegiatan->photos) {
            foreach ($kegiatan->photos as $p) {
                Storage::disk('public')->delete($p);
            }
        }
        $kegiatan->delete();

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}
