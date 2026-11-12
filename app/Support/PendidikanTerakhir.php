<?php

namespace App\Support;

final class PendidikanTerakhir
{
    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return [
            'SD / sederajat',
            'SMP / sederajat',
            'SMA / SMK / sederajat',
            'D3',
            'D4',
            'S1',
            'S2',
            'S3',
        ];
    }
}
