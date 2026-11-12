<?php

namespace Tests\Unit;

use App\Services\DocumentationMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentationMediaServiceTest extends TestCase
{
    public function test_stores_video_under_video_directory(): void
    {
        Storage::fake('public');

        $service = new DocumentationMediaService;
        $file = UploadedFile::fake()->create('clip.mp4', 50, 'video/mp4');

        $path = $service->store($file, 'kegiatan', 'kegiatan-videos');

        $this->assertStringStartsWith('kegiatan-videos/', $path);
        $this->assertStringEndsWith('.mp4', $path);
        Storage::disk('public')->assertExists($path);
    }
}
