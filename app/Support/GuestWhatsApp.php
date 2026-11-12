<?php

namespace App\Support;

final class GuestWhatsApp
{
    /** Nomor tampilan: 085117494221 */
    public const PHONE_E164 = '6285117494221';

    public const DISPLAY = '085117494221';

    public static function url(?string $text = null): string
    {
        $base = 'https://wa.me/'.self::PHONE_E164;

        if ($text === null || $text === '') {
            return $base;
        }

        return $base.'?text='.rawurlencode($text);
    }

    public static function demoIntro(): string
    {
        return 'Halo, saya ingin bertanya tentang '.GuestBrand::NAME.'.';
    }

    public static function demoRequest(string $nama, string $email, string $pesan): string
    {
        return 'Halo '.GuestBrand::NAME.", saya ingin minta demo.\n\nNama: {$nama}\nEmail: {$email}\n\n{$pesan}";
    }
}
