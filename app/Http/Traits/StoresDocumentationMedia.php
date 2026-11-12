<?php

namespace App\Http\Traits;

use App\Services\DocumentationMediaService;
use Illuminate\Http\UploadedFile;

trait StoresDocumentationMedia
{
    protected function storeDocumentationMedia(UploadedFile $file, string $imageDir, string $videoDir): string
    {
        return app(DocumentationMediaService::class)->store($file, $imageDir, $videoDir);
    }
}
