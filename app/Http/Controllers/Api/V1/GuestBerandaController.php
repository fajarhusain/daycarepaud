<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Support\ApiStorageUrl;
use App\Support\GuestCms;
use App\Support\GuestSeo;
use Illuminate\Http\JsonResponse;

class GuestBerandaController extends Controller
{
    public function show(): JsonResponse
    {
        $cms = GuestCms::data();
        $mediaKeys = [
            'hero_photo', 'about_photo', 'seo_og_image',
            'gallery_1', 'gallery_2', 'gallery_3', 'gallery_4', 'gallery_5', 'gallery_6',
        ];
        foreach ($mediaKeys as $key) {
            $cms[$key.'_url'] = ApiStorageUrl::optional($cms[$key] ?? '');
        }

        $seo = GuestSeo::forHome($cms);
        $cms['seo'] = [
            'title' => $seo['title'],
            'description' => $seo['description'],
            'canonical' => $seo['canonical'],
            'og_image_url' => $seo['og_image_url'],
        ];

        for ($i = 1; $i <= 6; $i++) {
            $cms['gallery_'.$i.'_alt_resolved'] = GuestSeo::galleryAlt($cms, $i);
        }
        $cms['hero_photo_alt_resolved'] = GuestSeo::heroPhotoAlt($cms);
        $cms['about_photo_alt_resolved'] = GuestSeo::aboutPhotoAlt($cms);

        $sekolahs = Sekolah::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Sekolah $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'address' => $s->address,
                'phone' => $s->phone,
                'photo_url' => ApiStorageUrl::optional($s->photo),
            ]);

        return response()->json([
            'cms' => $cms,
            'sekolahs' => $sekolahs,
        ]);
    }
}
