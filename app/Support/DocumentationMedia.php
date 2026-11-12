<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class DocumentationMedia
{
    /** @var list<string> */
    private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'webm'];

    public static function isVideoPath(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, self::VIDEO_EXTENSIONS, true);
    }

    /** URL relatif agar preview konsisten di semua browser (hindari beda host APP_URL vs localhost). */
    public static function publicAssetPath(string $path): string
    {
        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        return '/storage/'.$normalized;
    }

    public static function isVideoFile(?UploadedFile $file): bool
    {
        if (! $file) {
            return false;
        }

        $mime = $file->getMimeType() ?? '';
        if (str_starts_with($mime, 'video/')) {
            return true;
        }

        $ext = strtolower($file->getClientOriginalExtension());

        return in_array($ext, self::VIDEO_EXTENSIONS, true);
    }

    /**
     * @return list<string|callable>
     */
    public static function singleFileRules(bool $required = false): array
    {
        $rules = $required ? ['required'] : ['nullable'];
        $rules[] = 'file';
        $rules[] = self::fileConstraintRule();

        return $rules;
    }

    /**
     * @return list<string|callable>
     */
    public static function multiFileItemRules(): array
    {
        return ['file', self::fileConstraintRule()];
    }

    private static function fileConstraintRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! $value instanceof UploadedFile) {
                return;
            }

            $imageMax = (int) config('documentation_media.image_max_kb', 2048);
            $videoMax = (int) config('documentation_media.video_max_kb', 15360);
            $allowedVideo = config('documentation_media.allowed_video_mimes', ['mp4', 'mov', 'webm']);

            if (self::isVideoFile($value)) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (! in_array($ext, $allowedVideo, true)) {
                    $fail('Format video tidak didukung. Gunakan MP4, MOV, atau WebM.');

                    return;
                }
                if ($value->getSize() > $videoMax * 1024) {
                    $fail(sprintf('Ukuran video maksimal %d MB.', (int) ceil($videoMax / 1024)));

                    return;
                }

                return;
            }

            if (! str_starts_with($value->getMimeType() ?? '', 'image/')) {
                $fail('File harus berupa gambar atau video dokumentasi.');

                return;
            }

            if ($value->getSize() > $imageMax * 1024) {
                $fail(sprintf('Ukuran gambar maksimal %d MB.', (int) ceil($imageMax / 1024)));
            }
        };
    }

    public static function clientConfig(): array
    {
        return [
            'video_max_seconds' => (int) config('documentation_media.video_max_seconds', 60),
            'video_input_max_kb' => (int) config('documentation_media.video_input_max_kb', 30720),
            'video_max_width' => (int) config('documentation_media.video_max_width', 1280),
        ];
    }
}
