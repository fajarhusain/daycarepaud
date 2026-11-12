<?php

namespace App\Support;

final class GeoCoordinate
{
    /**
     * @return array{lat: float, lng: float}|null
     */
    public static function parse(?string $raw): ?array
    {
        $raw = (string) ($raw ?? '');

        if (preg_match_all('/-?\d+(?:\.\d+)?/', $raw, $matches) < 2) {
            return null;
        }

        $numbers = array_map(
            fn (string $value): float => (float) $value,
            $matches[0],
        );

        [$lat, $lng] = $numbers;

        if (abs($lat) > 90 || abs($lng) > 180) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }
}
