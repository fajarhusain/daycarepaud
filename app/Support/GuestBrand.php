<?php

namespace App\Support;

final class GuestBrand
{
    public const NAME = 'DaycareAI';

    public static function name(): string
    {
        return self::NAME;
    }
}
