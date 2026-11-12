<?php

namespace App\Http\Controllers\Concerns;

use App\Services\PhotoArchiveService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait DownloadsPhotoArchive
{
    /**
     * @param  list<array{path: string, filename: string}>  $entries
     */
    protected function downloadPhotoArchive(array $entries, string $zipName): BinaryFileResponse|RedirectResponse
    {
        if ($entries === []) {
            return redirect()->back()->with('warning', 'Tidak ada foto dokumentasi untuk filter ini.');
        }

        try {
            return app(PhotoArchiveService::class)->downloadZip($entries, $zipName);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('warning', $e->getMessage());
        }
    }
}
