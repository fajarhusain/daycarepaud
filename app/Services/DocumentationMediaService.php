<?php

namespace App\Services;

use App\Http\Traits\CanUploadImage;
use App\Support\DocumentationMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentationMediaService
{
    use CanUploadImage;

    public function store(UploadedFile $file, string $imageDir, string $videoDir): string
    {
        if (DocumentationMedia::isVideoFile($file)) {
            return $this->storeVideo($file, $videoDir);
        }

        return $this->uploadImage($file, $imageDir);
    }

    private function storeVideo(UploadedFile $file, string $videoDir): string
    {
        $filename = Str::random(40).'.mp4';

        return $file->storeAs($videoDir, $filename, 'public');
    }
}
