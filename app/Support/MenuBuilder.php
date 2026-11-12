<?php

namespace App\Support;

use App\Models\User;

class MenuBuilder
{
    /**
     * Filter izin, buang grup kosong, dan isi metadata grup (count, has_badge_warning).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public static function build(array $items, User $user, bool $skipPermFilter = false): array
    {
        $items = array_map(
            fn (array $node) => self::processNode($node, $user, $skipPermFilter),
            $items
        );

        $items = array_values(array_filter($items, fn ($node) => $node !== null && self::nodeIsVisible($node)));

        return $items;
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>|null
     */
    private static function processNode(array $node, User $user, bool $skipPermFilter): ?array
    {
        if (! empty($node['collapsible']) && isset($node['sections'])) {
            $node['sections'] = array_map(function (array $section) use ($user, $skipPermFilter) {
                $section['items'] = array_values(array_filter(
                    array_map(
                        fn (array $item) => self::processSectionItem($item, $user, $skipPermFilter),
                        $section['items'] ?? []
                    ),
                    fn ($item) => $item !== null
                ));

                return $section;
            }, $node['sections']);

            $node['sections'] = array_values(array_filter(
                $node['sections'],
                fn (array $section) => count($section['items'] ?? []) > 0
            ));

            if ($node['sections'] === []) {
                return null;
            }

            $node['count'] = self::countVisibleLinks($node);
            $node['has_badge_warning'] = self::hasBadgeWarningInTree($node);

            return $node;
        }

        if (isset($node['items'])) {
            $node['items'] = array_values(array_filter(
                array_map(
                    fn (array $item) => self::processSectionItem($item, $user, $skipPermFilter),
                    $node['items']
                ),
                fn ($item) => $item !== null
            ));

            if ($node['items'] === []) {
                return null;
            }

            return $node;
        }

        return $node;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private static function processSectionItem(array $item, User $user, bool $skipPermFilter): ?array
    {
        if (! empty($item['collapsible'])) {
            return self::processNode($item, $user, $skipPermFilter);
        }

        if (! self::linkAllowed($item, $user, $skipPermFilter)) {
            return null;
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private static function linkAllowed(array $item, User $user, bool $skipPermFilter): bool
    {
        if (isset($item['except_roles'])
            && ! $user->hasAnyRole(['Admin Sekolah', 'Lembaga'])
            && $user->hasAnyRole((array) $item['except_roles'])) {
            return false;
        }

        if (isset($item['roles']) && ! $user->hasAnyRole((array) $item['roles'])) {
            return false;
        }

        if ($skipPermFilter || ! isset($item['perm'])) {
            return true;
        }

        return $user->can($item['perm']);
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private static function countVisibleLinks(array $node): int
    {
        $count = 0;

        if (! empty($node['sections'])) {
            foreach ($node['sections'] as $section) {
                foreach ($section['items'] ?? [] as $item) {
                    $count += self::countItemLinks($item);
                }
            }
        }

        if (isset($node['items'])) {
            foreach ($node['items'] as $item) {
                $count += self::countItemLinks($item);
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private static function countItemLinks(array $item): int
    {
        if (! empty($item['collapsible'])) {
            return self::countVisibleLinks($item);
        }

        if (isset($item['route'])) {
            return 1;
        }

        if (isset($item['items'])) {
            return self::countVisibleLinks($item);
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private static function hasBadgeWarningInTree(array $node): bool
    {
        if (! empty($node['sections'])) {
            foreach ($node['sections'] as $section) {
                foreach ($section['items'] ?? [] as $item) {
                    if (self::itemHasBadgeWarning($item)) {
                        return true;
                    }
                }
            }
        }

        if (isset($node['items'])) {
            foreach ($node['items'] as $item) {
                if (self::itemHasBadgeWarning($item)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private static function itemHasBadgeWarning(array $item): bool
    {
        if (! empty($item['collapsible'])) {
            return self::hasBadgeWarningInTree($item);
        }

        return ! empty($item['badge']) && (int) $item['badge'] > 0;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private static function nodeIsVisible(array $node): bool
    {
        if (! empty($node['collapsible'])) {
            return ! empty($node['sections']);
        }

        if (isset($node['items'])) {
            return count($node['items']) > 0;
        }

        return true;
    }

    /**
     * Semua link di dalam grup (untuk mode sidebar ciut).
     *
     * @param  array<string, mixed>  $node
     * @return array<int, array<string, mixed>>
     */
    public static function flattenCollapsibleLinks(array $node): array
    {
        $links = [];

        if (empty($node['sections'])) {
            return $links;
        }

        foreach ($node['sections'] as $section) {
            foreach ($section['items'] ?? [] as $item) {
                if (! empty($item['collapsible'])) {
                    $links = array_merge($links, self::flattenCollapsibleLinks($item));
                } elseif (isset($item['route'])) {
                    $links[] = $item;
                }
            }
        }

        return $links;
    }
}
