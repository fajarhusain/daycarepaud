<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

final class AdminBreadcrumb
{
    /** @var array<string, string> */
    private const ROUTE_LABELS = [
        'dashboard' => 'Dashboard',
        'superadmin.dashboard' => 'Dashboard',
        'profile.edit' => 'Profil',
        'profile.sekolah.edit' => 'Profil Sekolah',
        'admin.settings' => 'Pengaturan',
        'admin.presensi-setting.index' => 'Pengaturan Presensi Guru',
        'admin.presensi.rekap' => 'Rekap Presensi',
        'admin.laporan-keuangan.index' => 'Laporan Keuangan',
        'admin.laporan.arus-kas' => 'Arus Kas',
        'admin.laporan.buku-besar' => 'Buku Besar',
        'admin.laporan.laba-rugi' => 'Laba Rugi',
        'admin.laporan.neraca' => 'Neraca',
        'admin.gaji-guru.komponen.index' => 'Komponen Gaji',
        'admin.monev-guru.kriteria.index' => 'Kriteria Monev Guru',
        'admin.monev-guru.evaluasi.index' => 'Evaluasi Monev Guru',
        'admin.monev-guru.evaluasi.form' => 'Form Evaluasi',
        'admin.akuntansi-setting.index' => 'Setting Akuntansi',
        'admin.activity-log.index' => 'Log Aktivitas',
        'lembaga.activity-log.index' => 'Log Aktivitas',
        'superadmin.activity-log.index' => 'Log Aktivitas',
        'orangtua.anak.create' => 'Tambah Anak',
        'pengajar.kegiatan-rutin.index' => 'Kegiatan Rutin',
    ];

    /** @var array<string, string> */
    private const ROUTE_SUBTITLES = [
        'admin.presensi-setting.index' => 'Hari kerja, jam masuk/pulang, radius lokasi, dan hari libur.',
    ];

    /**
     * @return list<array{label: string, url: string|null}>
     */
    public static function resolve(array $roleNavItems, string $homeRoute, Request $request, ?string $titleOverride = null, ?string $subtitle = null): array
    {
        $routeName = $request->route()?->getName();
        if (! $routeName) {
            return [];
        }

        if (in_array($routeName, ['dashboard', 'admin.dashboard', 'superadmin.dashboard'], true)) {
            return self::applyOverride(
                [['label' => 'Dashboard', 'url' => null]],
                $request,
                $titleOverride,
                $subtitle
            );
        }

        $items = [];
        $context = self::matchNavContext($roleNavItems, $request);
        $suffix = self::suffixForRoute($routeName);

        if ($context !== null) {
            foreach ($context['groups'] as $groupLabel) {
                if ($groupLabel !== '') {
                    $items[] = ['label' => $groupLabel, 'url' => null];
                }
            }

            $item = $context['item'];
            $itemLabel = $item['label'];
            $itemRoute = $item['route'];
            $itemUrl = Route::has($itemRoute) ? route($itemRoute) : null;

            if ($suffix && ! str_ends_with($routeName, '.index')) {
                if ($itemUrl && $routeName !== $itemRoute) {
                    $items[] = ['label' => $itemLabel, 'url' => $itemUrl];
                }
                $items[] = ['label' => $titleOverride ?? $suffix, 'url' => null];
            } else {
                $items[] = ['label' => $titleOverride ?? $itemLabel, 'url' => null];
            }
        } else {
            $parentLabel = self::ROUTE_LABELS[$routeName] ?? self::humanizeRoute($routeName);
            $suffix = self::suffixForRoute($routeName);
            if ($suffix && ! str_ends_with($routeName, '.index')) {
                $items[] = ['label' => $parentLabel, 'url' => null];
                $items[] = ['label' => $titleOverride ?? $suffix, 'url' => null];
            } else {
                $items[] = ['label' => $titleOverride ?? $parentLabel, 'url' => null];
            }
        }

        return self::applyOverride($items, $request, $titleOverride, $subtitle);
    }

    /**
     * @param  list<array{label: string, url: string|null}>  $items
     * @return list<array{label: string, url: string|null, subtitle?: string|null}>
     */
    private static function applyOverride(array $items, Request $request, ?string $titleOverride, ?string $subtitle = null): array
    {
        if ($titleOverride && count($items) > 0) {
            $items[array_key_last($items)]['label'] = $titleOverride;
        }
        $routeName = $request->route()?->getName();
        $autoSubtitle = $routeName ? (self::ROUTE_SUBTITLES[$routeName] ?? null) : null;
        $resolvedSubtitle = $subtitle ?? $autoSubtitle;
        if ($resolvedSubtitle !== null && $resolvedSubtitle !== '' && count($items) > 0) {
            $items[array_key_last($items)]['subtitle'] = $resolvedSubtitle;
        }

        return $items;
    }

    /**
     * @return array{groups: list<string>, item: array{route: string, label: string}}|null
     */
    private static function matchNavContext(array $roleNavItems, Request $request): ?array
    {
        $best = null;
        $bestLen = -1;

        $visit = function (array $nodes, array $groupStack) use (&$visit, &$best, &$bestLen, $request): void {
            foreach ($nodes as $nav) {
                if (! empty($nav['collapsible']) && isset($nav['sections'])) {
                    $nextStack = $groupStack;
                    $groupName = (string) ($nav['group'] ?? '');
                    if ($groupName !== '') {
                        $nextStack[] = $groupName;
                    }
                    foreach ($nav['sections'] as $section) {
                        foreach ($section['items'] ?? [] as $item) {
                            if (! empty($item['collapsible'])) {
                                $visit([$item], $nextStack);
                            } else {
                                self::considerNavMatch($item, $nextStack, $request, $best, $bestLen);
                            }
                        }
                    }

                    continue;
                }

                if (isset($nav['items'])) {
                    $nextStack = $groupStack;
                    $groupName = (string) ($nav['group'] ?? '');
                    if ($groupName !== '') {
                        $nextStack[] = $groupName;
                    }
                    foreach ($nav['items'] as $item) {
                        self::considerNavMatch($item, $nextStack, $request, $best, $bestLen);
                    }

                    continue;
                }

                if (isset($nav['route'], $nav['label'])) {
                    self::considerNavMatch($nav, $groupStack, $request, $best, $bestLen);
                }
            }
        };

        $visit($roleNavItems, []);

        foreach ([
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'pattern' => 'admin.dashboard'],
            ['route' => 'dashboard', 'label' => 'Dashboard', 'pattern' => 'dashboard'],
            ['route' => 'superadmin.dashboard', 'label' => 'Dashboard', 'pattern' => 'superadmin.dashboard'],
        ] as $dashboardItem) {
            if (Route::has($dashboardItem['route'])) {
                self::considerNavMatch($dashboardItem, [], $request, $best, $bestLen);
            }
        }

        return $best;
    }

    /**
     * @param  list<string>  $groupStack
     * @param  array{groups: list<string>, item: array{route: string, label: string}}|null  $best
     */
    private static function considerNavMatch(array $item, array $groupStack, Request $request, ?array &$best, int &$bestLen): void
    {
        if (! isset($item['route'], $item['label'])) {
            return;
        }

        $patterns = is_array($item['pattern'] ?? null) ? $item['pattern'] : [($item['pattern'] ?? $item['route'] ?? '')];
        foreach ($patterns as $pattern) {
            if ($pattern === '') {
                continue;
            }
            if (! $request->routeIs($pattern)) {
                continue;
            }
            $len = strlen($pattern);
            if ($len > $bestLen) {
                $bestLen = $len;
                $best = [
                    'groups' => $groupStack,
                    'item' => ['route' => $item['route'], 'label' => $item['label']],
                ];
            }
        }
    }

    private static function suffixForRoute(string $routeName): ?string
    {
        return match (true) {
            str_ends_with($routeName, '.create') => 'Tambah',
            str_ends_with($routeName, '.edit') => 'Ubah',
            str_ends_with($routeName, '.show') => 'Detail',
            default => null,
        };
    }

    private static function humanizeRoute(string $routeName): string
    {
        if (isset(self::ROUTE_LABELS[$routeName])) {
            return self::ROUTE_LABELS[$routeName];
        }

        $tail = str_replace(['.', '-', '_'], ' ', Str::afterLast($routeName, '.'));

        return Str::title($tail);
    }
}
