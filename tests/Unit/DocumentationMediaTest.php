<?php

namespace Tests\Unit;

use App\Support\DocumentationMedia;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DocumentationMediaTest extends TestCase
{
    public function test_is_video_path_by_extension(): void
    {
        $this->assertTrue(DocumentationMedia::isVideoPath('kegiatan-videos/abc.mp4'));
        $this->assertTrue(DocumentationMedia::isVideoPath('clip.MOV'));
        $this->assertFalse(DocumentationMedia::isVideoPath('pencapaian/photo.jpg'));
    }

    public function test_is_video_file_by_mime(): void
    {
        $file = UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4');
        $this->assertTrue(DocumentationMedia::isVideoFile($file));

        $image = UploadedFile::fake()->image('photo.jpg');
        $this->assertFalse(DocumentationMedia::isVideoFile($image));
    }

    public function test_client_config_exposes_limits(): void
    {
        config(['documentation_media.video_max_seconds' => 45]);
        $this->assertSame(45, DocumentationMedia::clientConfig()['video_max_seconds']);
    }
}
