<?php

namespace App\Http\Controllers\Concerns;

use App\Services\PhotoArchiveService;
use App\Support\DocumentationMedia;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait DownloadsPublicPhoto
{
    protected function downloadPublicPhoto(
        PhotoArchiveService $photoArchive,
        ?string $path,
        string $downloadName
    ): StreamedResponse {
        abort_if(! $path, 404, 'Foto tidak ditemukan.');

        return $photoArchive->downloadPublicFile($path, $downloadName);
    }

    protected function slugPhotoFilename(string $prefix, ?string $path): string
    {
        if ($path && DocumentationMedia::isVideoPath($path)) {
            return Str::slug($prefix).'.mp4';
        }

        $ext = $path ? pathinfo($path, PATHINFO_EXTENSION) : 'jpg';

        return Str::slug($prefix).'.'.($ext ?: 'jpg');
    }

}
