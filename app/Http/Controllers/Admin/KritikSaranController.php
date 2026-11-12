<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Http\Controllers\Controller;
use App\Models\KritikSaran;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class KritikSaranController extends Controller
{
    use DownloadsExcel;
    use DownloadsPublicPhoto;
    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;

        $feedbacks = KritikSaran::query()
            ->where('sekolah_id', $sekolahId)
            ->with(['user.anaks.kelas', 'sekolah'])
            ->latest()
            ->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('admin.kritik_saran.index', compact('feedbacks'));
    }

    public function export(Request $request)
    {
        $rows = KritikSaran::query()
            ->where('sekolah_id', auth()->user()->sekolah_id)
            ->with(['user.anaks.kelas', 'sekolah'])
            ->latest()
            ->get()
            ->map(function (KritikSaran $f) {
                $kelas = $f->user?->anaks?->pluck('kelas.name')->filter()->unique()->join(', ') ?: '-';

                return [
                    $f->created_at?->format('Y-m-d H:i') ?? '-',
                    $f->user?->name ?? '-',
                    $f->sekolah?->name ?? '-',
                    $kelas,
                    \Illuminate\Support\Str::limit($f->message ?? '', 120),
                ];
            })
            ->all();

        return $this->downloadExcel(
            ['Tanggal', 'Pengirim', 'Sekolah', 'Kelas (anak)', 'Ringkasan'],
            $rows,
            'kritik-saran-'.now()->format('Y-m-d').'.xlsx',
            'Kritik & Saran'
        );
    }

    public function show(KritikSaran $kritik_saran)
    {
        abort_if($kritik_saran->sekolah_id !== auth()->user()->sekolah_id, 404);

        $kritik_saran->load(['user.anaks.kelas', 'sekolah']);

        return view('admin.kritik_saran.show', compact('kritik_saran'));
    }

    public function update(Request $request, KritikSaran $kritik_saran)
    {
        abort_if($kritik_saran->sekolah_id !== auth()->user()->sekolah_id, 404);

        $validated = $request->validate([
            'status' => ['required', 'string', 'max:50'],
            'umpan_balik' => ['nullable', 'string', 'max:5000'],
        ]);

        $kritik_saran->update($validated);

        return redirect()
            ->route('admin.kritik-saran.show', $kritik_saran)
            ->with('success', 'Status dan tanggapan berhasil disimpan.');
    }

    public function downloadPhoto(KritikSaran $kritik_saran, \App\Services\PhotoArchiveService $photoArchive)
    {
        abort_if($kritik_saran->sekolah_id !== auth()->user()->sekolah_id, 404);

        return $this->downloadPublicPhoto(
            $photoArchive,
            $kritik_saran->photo,
            $this->slugPhotoFilename('kritik-saran-'.$kritik_saran->id, $kritik_saran->photo)
        );
    }
}
