<?php

namespace App\Support;

use App\Models\MenuMakanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuMakananCalendar
{
    public static function formatDate(MenuMakanan $m): string
    {
        $d = $m->date;

        return $d instanceof Carbon ? $d->format('Y-m-d') : Carbon::parse($d)->format('Y-m-d');
    }

    public static function eventTitle(MenuMakanan $m): string
    {
        $line = Str::of((string) ($m->menu ?? ''))->trim()->before("\n")->before(' • ')->trim();

        return $line->isNotEmpty() ? Str::limit((string) $line, 48) : 'Menu Makanan';
    }

    /**
     * @return array{backgroundColor: string, borderColor: string}
     */
    public static function eventColors(MenuMakanan $m): array
    {
        if ($m->photo || $m->photo_kegiatan) {
            return [
                'backgroundColor' => '#10B981',
                'borderColor' => '#059669',
            ];
        }

        return [
            'backgroundColor' => '#1A6B6B',
            'borderColor' => '#145252',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detailPayload(MenuMakanan $m): array
    {
        $date = Carbon::parse($m->date);

        return [
            'id' => $m->id,
            'date' => $date->format('d M Y'),
            'date_raw' => self::formatDate($m),
            'menu' => $m->menu,
            'nutrition_info' => $m->nutrition_info,
            'description' => Str::limit((string) ($m->nutrition_info ?: $m->menu), 120),
            'photo_url' => $m->photo ? Storage::url($m->photo) : null,
            'photo_download_url' => $m->photo ? route('admin.menu-makanan.photo.download', ['menu_makanan' => $m, 'field' => 'photo']) : null,
            'photo_kegiatan_url' => $m->photo_kegiatan ? Storage::url($m->photo_kegiatan) : null,
            'photo_kegiatan_download_url' => $m->photo_kegiatan ? route('admin.menu-makanan.photo.download', ['menu_makanan' => $m, 'field' => 'photo_kegiatan']) : null,
            'likes_count' => (int) ($m->likes_count ?? 0),
            'dislikes_count' => (int) ($m->dislikes_count ?? 0),
            'is_today' => $date->isToday(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function toAdminEvent(MenuMakanan $m): array
    {
        $colors = self::eventColors($m);
        $detail = self::detailPayload($m);

        return [
            'id' => (string) $m->id,
            'title' => self::eventTitle($m),
            'start' => self::formatDate($m),
            'allDay' => true,
            'backgroundColor' => $colors['backgroundColor'],
            'borderColor' => $colors['borderColor'],
            'extendedProps' => [
                'mode' => 'menu_admin',
                'delete_url' => route('admin.menu-makanan.destroy', $m),
                'detail' => $detail,
                'edit' => [
                    'id' => $m->id,
                    'date' => self::formatDate($m),
                    'menu' => $m->menu,
                    'nutrition_info' => $m->nutrition_info,
                    'photo' => $m->photo,
                    'photo_kegiatan' => $m->photo_kegiatan,
                ],
            ],
        ];
    }
}
