<?php

namespace App\Support;

use App\Models\SekolahAiTokenTransaction;

class AiTokenFeature
{
    public const MONEV = 'monev';

    public const PENCAPAIAN = 'pencapaian';

    public const CHAT = 'chat';

    public const PERSONA = 'persona';

    public static function transactionType(string $feature): string
    {
        return match ($feature) {
            self::MONEV => SekolahAiTokenTransaction::TYPE_MONEV,
            self::PENCAPAIAN => SekolahAiTokenTransaction::TYPE_PENCAPAIAN,
            self::CHAT => SekolahAiTokenTransaction::TYPE_CHAT,
            self::PERSONA => SekolahAiTokenTransaction::TYPE_PERSONA,
            default => $feature,
        };
    }

    public static function featureFromTransactionType(string $type): string
    {
        return match ($type) {
            SekolahAiTokenTransaction::TYPE_MONEV => self::MONEV,
            SekolahAiTokenTransaction::TYPE_PENCAPAIAN => self::PENCAPAIAN,
            SekolahAiTokenTransaction::TYPE_CHAT => self::CHAT,
            SekolahAiTokenTransaction::TYPE_PERSONA => self::PERSONA,
            default => self::CHAT,
        };
    }
}
