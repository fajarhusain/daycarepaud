<?php

namespace App\Support;

class DashboardCompactMenu
{
    public static function isEmpty(?string $menu): bool
    {
        return count(self::items($menu)) === 0;
    }

    /**
     * @return list<string>
     */
    public static function items(?string $menu): array
    {
        if ($menu === null) {
            return [];
        }

        $trimmed = trim($menu);
        if (mb_strlen($trimmed) < 3) {
            return [];
        }

        $parts = preg_split('/\r\n|\n|,|;/', $trimmed) ?: [];
        $items = [];
        foreach ($parts as $part) {
            $line = trim($part);
            if ($line === '' || self::isJunk($line)) {
                continue;
            }
            $items[] = $line;
        }

        if ($items === [] && mb_strlen($trimmed) >= 3 && ! self::isJunk($trimmed)) {
            return [$trimmed];
        }

        return array_values(array_unique($items));
    }

    private static function isJunk(string $line): bool
    {
        $lower = mb_strtolower(trim($line));
        if (mb_strlen($lower) < 3) {
            return true;
        }

        return in_array($lower, ['tes', 'test', 'testing'], true);
    }
}
