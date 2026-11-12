<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    /**
     * @var list<string>
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/pdf',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_DISKS = [
        'public',
        'local',
    ];

    public function show(Request $request): BinaryFileResponse
    {
        if (! $request->hasValidSignature()) {
            throw ApiException::make(ApiErrorCode::TAUTAN_KEDALUWARSA);
        }

        $path = trim((string) $request->query('path', ''));

        if ($path === ''
            || str_contains($path, '..')
            || str_contains($path, '\\')
            || str_contains($path, "\0")
            || str_contains($path, '://')
            || str_starts_with($path, '/')) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        $diskName = trim((string) $request->query('disk', 'public'));

        if (! in_array($diskName, self::ALLOWED_DISKS, true)) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        $disk = Storage::disk($diskName);

        if (! $disk->exists($path)) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        // Jangan pernah menyajikan HTML/SVG dari domain aplikasi lewat signed URL.
        if (! in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        $root = realpath($disk->path(''));
        $fullPath = realpath($disk->path($path));

        if ($root === false
            || $fullPath === false
            || ! str_starts_with($fullPath, rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, max-age=600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
