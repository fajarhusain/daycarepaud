<?php

namespace App\Support;

final class GuestIllustrations
{
    private const DIR = 'images/guest';

    /** @var array<string, string> */
    private const MAP = [
        'deco.tree-left' => 'deco-tree-left.svg',
        'deco.tree-right' => 'deco-tree-right.svg',
        'deco.cloud' => 'deco-cloud.svg',
        'deco.footer' => 'deco-footer.svg',
        'service.ortu' => 'service-ortu.svg',
        'service.operasional' => 'service-operasional.svg',
        'service.ai' => 'service-ai.svg',
        'service.komunikasi' => 'service-komunikasi.svg',
        'service.sekolah' => 'service-sekolah.svg',
        'pillar.ortu' => 'pillar-ortu.svg',
        'pillar.operasional' => 'pillar-operasional.svg',
        'pillar.ai' => 'pillar-ai.svg',
        'placeholder.about' => 'placeholder-about.svg',
        'placeholder.hero' => 'placeholder-hero.svg',
        'kontak.alamat' => 'kontak-alamat.svg',
        'kontak.telepon' => 'kontak-telepon.svg',
        'kontak.email' => 'kontak-email.svg',
        'kontak.jam' => 'kontak-jam.svg',
    ];

    /** @var array<int, string> */
    private const FACILITY_BY_INDEX = [
        1 => 'service.ortu',
        2 => 'service.operasional',
        3 => 'service.ai',
        4 => 'service.komunikasi',
    ];

    /** @var array<string, string> */
    private const EMOJI_FALLBACK = [
        'service.ortu' => '💬',
        'service.operasional' => '⚙️',
        'service.ai' => '✨',
        'service.komunikasi' => '🤖',
        'pillar.ortu' => '💬',
        'pillar.operasional' => '⚙️',
        'pillar.ai' => '✨',
        'placeholder.about' => '🌱',
        'placeholder.hero' => '🏫',
        'service.sekolah' => '🏫',
        'kontak.alamat' => '📍',
        'kontak.telepon' => '📞',
        'kontak.email' => '✉️',
        'kontak.jam' => '🕐',
    ];

    public static function exists(string $key): bool
    {
        $file = self::filename($key);

        return $file !== null && file_exists(public_path(self::DIR.'/'.$file));
    }

    public static function url(string $key): ?string
    {
        if (! self::exists($key)) {
            return null;
        }

        return asset(self::DIR.'/'.self::filename($key));
    }

    public static function emojiFallback(string $key): ?string
    {
        return self::EMOJI_FALLBACK[$key] ?? null;
    }

    public static function facilityKey(int $index): string
    {
        return self::FACILITY_BY_INDEX[$index] ?? 'service.ortu';
    }

    /**
     * Resolve CMS facility icon value to illustration key or direct filename.
     */
    public static function resolveFacilityIcon(int $index, ?string $cmsValue): string
    {
        $value = trim((string) $cmsValue);

        if ($value !== '' && preg_match('/\.(svg|png|webp)$/i', $value)) {
            return $value;
        }

        return self::facilityKey($index);
    }

    public static function urlForFacility(int $index, ?string $cmsValue): ?string
    {
        $value = trim((string) $cmsValue);

        if ($value !== '' && preg_match('/\.(svg|png|webp)$/i', $value)) {
            $path = public_path(self::DIR.'/'.$value);

            return file_exists($path) ? asset(self::DIR.'/'.$value) : null;
        }

        return self::url(self::facilityKey($index));
    }

    public static function emojiForFacility(int $index, ?string $cmsValue): ?string
    {
        if (self::urlForFacility($index, $cmsValue) !== null) {
            return null;
        }

        $value = trim((string) $cmsValue);

        return $value !== '' ? $value : self::emojiFallback(self::facilityKey($index));
    }

    /** @return array<int, string> */
    public static function facilityDefaults(): array
    {
        return [
            1 => 'service-ortu.svg',
            2 => 'service-operasional.svg',
            3 => 'service-ai.svg',
            4 => 'service-komunikasi.svg',
        ];
    }

    private static function filename(string $key): ?string
    {
        if (isset(self::MAP[$key])) {
            return self::MAP[$key];
        }

        if (preg_match('/\.(svg|png|webp)$/i', $key)) {
            return $key;
        }

        return null;
    }
}
