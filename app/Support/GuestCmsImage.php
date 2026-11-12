<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class GuestCmsImage
{
    /**
     * @param  array<string, string>  $cms
     */
    public static function url(array $cms, string $cmsKey, string $ascentAssetPath): string
    {
        $path = trim($cms[$cmsKey] ?? '');
        if ($path !== '') {
            return Storage::url($path);
        }

        return GuestAscent::asset($ascentAssetPath);
    }
}
