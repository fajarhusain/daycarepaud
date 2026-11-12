<?php

namespace App\Support\Api;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

final class ApiMedia
{
    public const TTL_MINUTES = 10;

    /**
     * @return array{
     *     url: string,
     *     thumbnail_url: string|null,
     *     mime_type: string|null,
     *     size: int|null,
     *     width: int|null,
     *     height: int|null,
     *     expires_at: string|null
     * }|null
     */
    public static function fromPath(?string $path, string $disk = 'public'): ?array
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return self::external($path);
        }

        if (Str::startsWith($path, '/')) {
            return self::external(url($path));
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            return null;
        }

        $expiresAt = now()->addMinutes(self::TTL_MINUTES);
        $url = URL::temporarySignedRoute('api.v1.media.show', $expiresAt, [
            'path' => $path,
            'disk' => $disk,
        ]);

        $mimeType = $storage->mimeType($path) ?: null;
        $size = $storage->size($path);
        $dimensions = self::dimensions($storage->path($path));

        return [
            'url' => $url,
            'thumbnail_url' => $url,
            'mime_type' => $mimeType,
            'size' => $size !== false ? (int) $size : null,
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * @param  array<int, string|null>|null  $paths
     * @return array<int, array<string, mixed>>
     */
    public static function collection(?array $paths, string $disk = 'public'): array
    {
        $media = [];

        foreach ($paths ?? [] as $path) {
            $item = self::fromPath($path, $disk);

            if ($item !== null) {
                $media[] = $item;
            }
        }

        return $media;
    }

    /**
     * @return array{
     *     url: string,
     *     thumbnail_url: string|null,
     *     mime_type: string|null,
     *     size: int|null,
     *     width: int|null,
     *     height: int|null,
     *     expires_at: string|null
     * }
     */
    private static function external(string $url): array
    {
        return [
            'url' => $url,
            'thumbnail_url' => $url,
            'mime_type' => null,
            'size' => null,
            'width' => null,
            'height' => null,
            'expires_at' => null,
        ];
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    private static function dimensions(string $fullPath): ?array
    {
        if (! is_file($fullPath)) {
            return null;
        }

        $size = @getimagesize($fullPath);

        if (! is_array($size) || ! isset($size[0], $size[1])) {
            return null;
        }

        return [(int) $size[0], (int) $size[1]];
    }
}
