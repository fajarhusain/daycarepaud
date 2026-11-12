<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Anak;
use App\Models\Pencapaian;
use App\Services\PhotoArchiveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ResolvesPencapaianBundlePhoto
{
    protected function downloadPencapaianBundlePhoto(Request $request, PhotoArchiveService $photoArchive): StreamedResponse
    {
        $validated = $request->validate([
            'anak_id' => ['required', 'integer', 'exists:anaks,id'],
            'kegiatan_id' => ['required', 'integer', 'exists:kegiatans,id'],
        ]);

        [$photoPath, $anak, $kegiatanTitle] = $this->resolvePencapaianBundlePhoto(
            (int) $validated['anak_id'],
            (int) $validated['kegiatan_id']
        );

        $date = Pencapaian::query()
            ->where('anak_id', $validated['anak_id'])
            ->where('kegiatan_id', $validated['kegiatan_id'])
            ->whereNotNull('photo')
            ->value('created_at');

        $filename = $photoArchive->slugFilenameForStoragePath(
            $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d'),
            ($anak->name ?? 'siswa').'-'.($kegiatanTitle ?? 'kegiatan'),
            1,
            $photoPath
        );

        return $photoArchive->downloadPublicFile($photoPath, $filename);
    }

    /** @return array{0: string, 1: Anak, 2: string|null} */
    protected function resolvePencapaianBundlePhoto(int $anakId, int $kegiatanId): array
    {
        $anak = Anak::query()
            ->with('kelas')
            ->findOrFail($anakId);

        $this->authorizePencapaianBundleAnak($anak);

        $pencapaian = Pencapaian::query()
            ->with('kegiatan')
            ->where('anak_id', $anakId)
            ->where('kegiatan_id', $kegiatanId)
            ->whereNotNull('photo')
            ->first();

        abort_if(! $pencapaian?->photo, 404, 'Foto dokumentasi tidak ditemukan.');

        return [$pencapaian->photo, $anak, $pencapaian->kegiatan?->title];
    }

    abstract protected function authorizePencapaianBundleAnak(Anak $anak): void;
}
