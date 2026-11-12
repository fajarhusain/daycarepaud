<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

final class AdminSettingsNav
{
    /** @var list<string> */
    private const SETTINGS_PERMS = [
        'menu.role',
        'menu.pengguna',
        'menu.pengaturan-presensi-guru',
        'menu.log-aktivitas',
        'menu.setting-akuntansi',
        'menu.pengaturan-ai',
    ];

    /** @return list<string> */
    public static function sidebarPatterns(): array
    {
        return [
            'admin.settings',
            'profile.edit',
            'profile.sekolah.*',
            'admin.role.*',
            'admin.pengguna.*',
            'admin.presensi-setting.*',
            'admin.activity-log.*',
            'admin.akuntansi-setting.*',
            'admin.ai-persona.*',
        ];
    }

    public static function canAccessHub(?User $user): bool
    {
        return $user !== null && self::isSettingsHubMember($user);
    }

    public static function firstRouteName(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        foreach (self::tabDefinitions() as $tab) {
            if (self::tabVisible($user, $tab) && Route::has($tab['route'])) {
                return $tab['route'];
            }
        }

        return null;
    }

    /**
     * @return list<array{route: string, label: string, active: bool}>
     */
    public static function tabsFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $tabs = [];
        foreach (self::tabDefinitions() as $tab) {
            if (! self::tabVisible($user, $tab) || ! Route::has($tab['route'])) {
                continue;
            }

            $tabs[] = [
                'route' => $tab['route'],
                'label' => $tab['label'],
                'active' => request()->routeIs($tab['pattern']),
            ];
        }

        return $tabs;
    }

    /**
     * @return list<array{route: string, label: string, pattern: string, perm?: string, admin_sekolah_only?: bool, hub_member?: bool, except_roles?: list<string>}>
     */
    private static function tabDefinitions(): array
    {
        return [
            ['route' => 'profile.edit', 'label' => 'Profil Akun', 'pattern' => 'profile.edit', 'hub_member' => true],
            ['route' => 'profile.sekolah.edit', 'label' => 'Profil Sekolah', 'pattern' => 'profile.sekolah.*', 'admin_sekolah_only' => true],
            ['route' => 'admin.role.index', 'label' => 'Role', 'pattern' => 'admin.role.*', 'perm' => 'menu.role'],
            ['route' => 'admin.pengguna.index', 'label' => 'Pengguna', 'pattern' => 'admin.pengguna.*', 'perm' => 'menu.pengguna'],
            ['route' => 'admin.presensi-setting.index', 'label' => 'Presensi Guru', 'pattern' => 'admin.presensi-setting.*', 'perm' => 'menu.pengaturan-presensi-guru', 'except_roles' => ['Pengajar', 'Wali Kelas']],
            ['route' => 'admin.activity-log.index', 'label' => 'Log Aktivitas', 'pattern' => 'admin.activity-log.*', 'perm' => 'menu.log-aktivitas'],
            ['route' => 'admin.akuntansi-setting.index', 'label' => 'Akuntansi', 'pattern' => 'admin.akuntansi-setting.*', 'perm' => 'menu.setting-akuntansi'],
            ['route' => 'admin.ai-persona.index', 'label' => 'AI', 'pattern' => 'admin.ai-persona.*', 'perm' => 'menu.pengaturan-ai'],
        ];
    }

    private static function isSettingsHubMember(User $user): bool
    {
        if ($user->hasRole('Admin Sekolah')) {
            return true;
        }

        foreach (self::SETTINGS_PERMS as $perm) {
            if ($user->can($perm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{route: string, label: string, pattern: string, perm?: string, admin_sekolah_only?: bool, hub_member?: bool}  $tab
     */
    private static function tabVisible(User $user, array $tab): bool
    {
        if (! empty($tab['hub_member'])) {
            return self::isSettingsHubMember($user);
        }

        if (! empty($tab['admin_sekolah_only']) && ! $user->hasRole('Admin Sekolah')) {
            return false;
        }

        if (! empty($tab['except_roles'])
            && ! $user->hasAnyRole(['Admin Sekolah', 'Lembaga'])
            && $user->hasAnyRole((array) $tab['except_roles'])) {
            return false;
        }

        if (isset($tab['perm']) && ! $user->can($tab['perm'])) {
            return false;
        }

        return true;
    }
}
