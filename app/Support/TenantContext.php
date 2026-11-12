<?php

namespace App\Support;

use RuntimeException;

final class TenantContext
{
    private static ?int $sekolahId = null;

    private static bool $bypassScope = false;

    public static function setSekolahId(?int $id): void
    {
        self::$sekolahId = $id;
    }

    public static function sekolahId(): ?int
    {
        return self::$sekolahId;
    }

    public static function requireSekolahId(): int
    {
        if (self::$sekolahId === null) {
            throw new RuntimeException('Tenant sekolah context is not set.');
        }

        return self::$sekolahId;
    }

    public static function bypassScope(bool $bypass = true): void
    {
        self::$bypassScope = $bypass;
    }

    public static function isBypassed(): bool
    {
        return self::$bypassScope;
    }

    public static function reset(): void
    {
        self::$sekolahId = null;
        self::$bypassScope = false;
    }
}
