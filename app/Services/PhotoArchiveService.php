<?php

namespace App\Services;

use App\Support\DocumentationMedia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class PhotoArchiveService
{
    /**
     * @param  iterable<array{path: string, filename: string}>  $entries
     */
    public function downloadZip(iterable $entries, string $zipName): BinaryFileResponse
    {
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir.'/'.Str::uuid().'.zip';
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat arsip foto.');
        }

        $added = 0;
        $usedNames = [];
        foreach ($entries as $entry) {
            $path = $entry['path'] ?? '';
            if ($path === '' || ! Storage::disk('public')->exists($path)) {
                continue;
            }

            $filename = $entry['filename'] ?? basename($path);
            $filename = $this->uniqueFilename($filename, $usedNames);
            $usedNames[] = $filename;

            $zip->addFile(Storage::disk('public')->path($path), $filename);
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);

            throw new \RuntimeException('Tidak ada foto dokumentasi untuk filter ini.');
        }

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    public function downloadPublicFile(string $path, string $downloadName): StreamedResponse
    {
        if (! Storage::disk('public')->exists($path)) {
            throw new \RuntimeException('File foto tidak ditemukan.');
        }

        return Storage::disk('public')->download($path, $downloadName);
    }

    public function slugFilename(string $date, string $label, int $index, ?string $extension = null): string
    {
        $ext = $extension ?: 'jpg';
        $slug = Str::slug(Str::limit($label, 40, '')) ?: 'foto';

        return sprintf('%s_%s_%02d.%s', $date, $slug, $index, ltrim($ext, '.'));
    }

    public function downloadExtensionForPath(string $path): string
    {
        if (DocumentationMedia::isVideoPath($path)) {
            return 'mp4';
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');

        return $ext === 'jpeg' ? 'jpg' : $ext;
    }

    public function slugFilenameForStoragePath(string $date, string $label, int $index, string $path): string
    {
        return $this->slugFilename($date, $label, $index, $this->downloadExtensionForPath($path));
    }

    /** @param  list<string>  $usedNames */
    private function uniqueFilename(string $filename, array $usedNames): string
    {
        if (! in_array($filename, $usedNames, true)) {
            return $filename;
        }

        $base = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $counter = 2;
        do {
            $candidate = $ext !== '' ? "{$base}_{$counter}.{$ext}" : "{$base}_{$counter}";
            $counter++;
        } while (in_array($candidate, $usedNames, true));

        return $candidate;
    }

    /**
     * @param  Collection<int, \App\Models\Pencapaian>  $records
     * @return list<array{path: string, filename: string}>
     */
    public function entriesFromPencapaian(Collection $records): array
    {
        $entries = [];
        $seen = [];

        foreach ($records as $pencapaian) {
            if (! $pencapaian->photo) {
                continue;
            }

            $key = $pencapaian->anak_id.'_'.$pencapaian->kegiatan_id;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $date = $pencapaian->created_at?->format('Y-m-d') ?? 'unknown';
            $label = ($pencapaian->anak?->name ?? 'siswa').'-'.($pencapaian->kegiatan?->title ?? 'kegiatan');
            $entries[] = [
                'path' => $pencapaian->photo,
                'filename' => $this->slugFilenameForStoragePath($date, $label, count($entries) + 1, $pencapaian->photo),
            ];
        }

        return $entries;
    }

    /**
     * @param  Collection<int, \App\Models\Kegiatan>  $kegiatans
     * @return list<array{path: string, filename: string}>
     */
    public function entriesFromKegiatans(Collection $kegiatans): array
    {
        $entries = [];

        foreach ($kegiatans as $kegiatan) {
            $photos = collect($kegiatan->photos ?? [])->filter();
            $date = $kegiatan->date?->format('Y-m-d') ?? 'unknown';
            $label = $kegiatan->title ?? 'kegiatan';

            foreach ($photos as $index => $path) {
                $entries[] = [
                    'path' => $path,
                    'filename' => $this->slugFilenameForStoragePath($date, $label, $index + 1, $path),
                ];
            }
        }

        return $entries;
    }

    /**
     * @param  Collection<int, \App\Models\KegiatanRutin>  $records
     * @return list<array{path: string, filename: string}>
     */
    public function entriesFromKegiatanRutin(Collection $records): array
    {
        $entries = [];

        foreach ($records as $record) {
            if (! $record->photo) {
                continue;
            }

            $date = $record->tanggal?->format('Y-m-d') ?? 'unknown';
            $label = ($record->anak?->name ?? 'siswa').'-'.($record->masterKegiatanRutin?->nama_kegiatan ?? 'rutin');
            $entries[] = [
                'path' => $record->photo,
                'filename' => $this->slugFilenameForStoragePath($date, $label, count($entries) + 1, $record->photo),
            ];
        }

        return $entries;
    }
}
