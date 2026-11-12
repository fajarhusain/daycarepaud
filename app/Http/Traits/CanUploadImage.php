<?php

namespace App\Http\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait CanUploadImage
{
    /**
     * Upload and compress image using GD
     *
     * @param  UploadedFile  $file
     * @param  string  $path
     * @param  int  $quality
     * @return string
     */
    public function uploadImage($file, $path, $quality = 70)
    {
        // Generate unique filename with .jpg extension for uniform compression
        $filename = Str::random(40).'.jpg';
        $fullPath = $path.'/'.$filename;

        $imagePath = $file->getRealPath();

        // Deteksi MIME dari konten file (bukan dari header/nama file)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($imagePath);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array($mime, $allowedMimes)) {
            throw new \InvalidArgumentException('File bukan gambar yang valid.');
        }

        $image = null;

        // Load image resource based on mime type
        try {
            switch ($mime) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($imagePath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($imagePath);
                    // Handle PNG transparency by filling with white background
                    if ($image) {
                        $width = imagesx($image);
                        $height = imagesy($image);
                        $background = imagecreatetruecolor($width, $height);
                        $white = imagecolorallocate($background, 255, 255, 255);
                        imagefill($background, 0, 0, $white);
                        imagecopy($background, $image, 0, 0, 0, 0, $width, $height);
                        imagedestroy($image);
                        $image = $background;
                    }
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $image = imagecreatefromwebp($imagePath);
                    }
                    break;
            }
        } catch (\Exception $e) {
            $image = null;
        }

        if (! $image) {
            // Fallback untuk format GIF atau format yang tidak dapat diproses GD
            return $file->store($path, 'public');
        }

        // Capture compressed JPEG output
        ob_start();
        imagejpeg($image, null, $quality);
        $content = ob_get_clean();

        imagedestroy($image);

        // Store to public disk dengan cleanup jika gagal
        try {
            Storage::disk('public')->put($fullPath, $content);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($fullPath);
            throw $e;
        }

        return $fullPath;
    }
}
