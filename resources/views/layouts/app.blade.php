@php
    $user = auth()->user();
    // Badge: pending enrollments untuk staf yang punya akses pendaftaran
    $pendingCount = 0;
    $tenantSekolahId = \App\Support\TenantContext::sekolahId() ?? $user?->getAttributes()['sekolah_id'] ?? null;
    if ($user && $tenantSekolahId && $user->can('menu.pendaftaran')) {
        $pendingCount = \App\Models\Anak::withoutSekolahScope()
            ->where('sekolah_id', $tenantSekolahId)
            ->where('status', 'pending')
            ->count();
    }
    $roleNavItems = [];
    $orangTuaBottomNavItems = null;
    $orangTuaMoreNavItems = null;
    $chatOrangTuaEnabled = true;
    if ($tenantSekolahId) {
        $chatOrangTuaEnabled = app(\App\Services\AiTokenService::class)->isChatOrangTuaEnabled((int) $tenantSekolahId);
    }
    if ($user && $user->hasRole('Superadmin')) {
        $roleNavItems = array_merge($roleNavItems, [
            ['route' => 'superadmin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'pattern' => 'superadmin.dashboard'],
            ['route' => 'superadmin.sekolah.index', 'label' => 'Sekolah', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'pattern' => 'superadmin.sekolah.*'],
            [
                'group' => 'Pengaturan',
                'collapsible' => true,
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'pattern' => ['superadmin.ai-setting.*', 'superadmin.cms.*', 'superadmin.users.*', 'superadmin.activity-log.*', 'superadmin.lembaga.*', 'superadmin.admin-lembaga.*'],
                'sections' => [
                    [
                        'label' => 'Platform',
                        'items' => [
                            ['route' => 'superadmin.ai-setting.index', 'label' => 'Pengaturan AI', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'pattern' => 'superadmin.ai-setting.*'],
                            ['route' => 'superadmin.cms.index', 'label' => 'CMS Website', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z', 'pattern' => 'superadmin.cms.*'],
                            ['route' => 'superadmin.users.index', 'label' => 'Superadmin', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'pattern' => 'superadmin.users.*'],
                            ['route' => 'superadmin.activity-log.index', 'label' => 'Log Aktivitas', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'pattern' => 'superadmin.activity-log.*'],
                        ],
                    ],
                    [
                        'label' => 'Lanjutan',
                        'items' => [
                            ['route' => 'superadmin.lembaga.index', 'label' => 'Lembaga', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5', 'pattern' => 'superadmin.lembaga.*'],
                            ['route' => 'superadmin.admin-lembaga.index', 'label' => 'Admin Lembaga', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'pattern' => 'superadmin.admin-lembaga.*'],
                        ],
                    ],
                ],
            ],
        ]);
    }
    $lembagaSchools = null;
    $activeSekolah = null;
    $ortuSchools = null;
    $activeOrtuSekolah = null;
    if ($user && $user->hasRole('Orang Tua')) {
        $ortuSchools = \App\Models\Sekolah::query()
            ->whereIn('id', $user->approvedSekolahIds())
            ->orderBy('name')
            ->get(['id', 'name']);
        if (session('ortu_active_sekolah_id')) {
            $activeOrtuSekolah = $ortuSchools->firstWhere('id', (int) session('ortu_active_sekolah_id'));
        }
    }
    if ($user && $user->hasRole('Lembaga')) {
        $hasProfileSekolahRoute = \Illuminate\Support\Facades\Route::has('profile.sekolah.edit');
        $lembagaSchools = \App\Models\Sekolah::where('lembaga_id', $user->lembaga_id)->orderBy('name')->get();
        if (session('active_sekolah_id')) {
            $activeSekolah = $lembagaSchools->firstWhere('id', (int) session('active_sekolah_id'));
        }
        $roleNavItems = array_merge($roleNavItems, [
            ['route' => 'lembaga.sekolah.index', 'label' => 'Sekolah', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'pattern' => 'lembaga.sekolah.*'],
            ...($hasProfileSekolahRoute ? [['route' => 'profile.sekolah.edit', 'label' => 'Profil Sekolah', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'pattern' => 'profile.sekolah.*']] : []),
            ['route' => 'lembaga.admin-sekolah.index', 'label' => 'Admin Sekolah', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'pattern' => 'lembaga.admin-sekolah.*'],
            ['route' => 'lembaga.kritik-saran.index', 'label' => 'Kritik & Saran', 'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z', 'pattern' => 'lembaga.kritik-saran.*'],
            ['route' => 'lembaga.activity-log.index', 'label' => 'Log Aktivitas', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'pattern' => 'lembaga.activity-log.*'],
        ]);
    }
    if ($user && $user->canAccessAdminPanel()) {
        $hasProfileSekolahRoute = \Illuminate\Support\Facades\Route::has('profile.sekolah.edit');
        $adminSidebarCollapsible = static function (string $group, string $icon, array $items): array {
            $patterns = [];
            foreach ($items as $item) {
                foreach ((array) ($item['pattern'] ?? []) as $pattern) {
                    if ($pattern !== '') {
                        $patterns[] = $pattern;
                    }
                }
            }

            return [
                'group' => $group,
                'collapsible' => true,
                'icon' => $icon,
                'pattern' => $patterns,
                'sections' => [['items' => $items]],
            ];
        };
        $roleNavItems = array_merge($roleNavItems, [
            $adminSidebarCollapsible('Manajemen Siswa', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', [
                    ['route' => 'admin.anak.index', 'label' => 'Siswa Sekolah', 'perm' => 'menu.siswa', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'pattern' => 'admin.anak.*'],
                    ['route' => 'admin.pendaftaran.index', 'label' => 'Pendaftaran', 'perm' => 'menu.pendaftaran', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'pattern' => 'admin.pendaftaran.*', 'badge' => $pendingCount],
                    ['route' => 'admin.kelas.index', 'label' => 'Kelola Kelas', 'perm' => 'menu.kelola-kelas', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'pattern' => 'admin.kelas.*'],
                    ['route' => 'admin.presensi.index', 'label' => 'Presensi Siswa', 'perm' => 'menu.presensi-siswa', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'pattern' => 'admin.presensi.*'],
                    ['route' => 'admin.kesehatan.index', 'label' => 'Kesehatan Siswa', 'perm' => 'menu.kesehatan-siswa', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'pattern' => 'admin.kesehatan.*'],
            ]),
            $adminSidebarCollapsible('Kurikulum', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', [
                    ['route' => 'admin.matrikulasi.index', 'label' => 'Matrikulasi', 'perm' => 'menu.matrikulasi', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'admin.matrikulasi.*'],
                    ['route' => 'admin.skala-pencapaian.index', 'label' => 'Skala Capaian', 'perm' => 'menu.skala-capaian', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'admin.skala-pencapaian.*'],
                    ['route' => 'admin.kegiatan.index', 'label' => 'Agenda Belajar', 'perm' => 'menu.agenda-belajar', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'pattern' => 'admin.kegiatan.*'],
                    ['route' => 'admin.master-kegiatan-rutin.index', 'label' => 'Kegiatan Rutin', 'perm' => 'menu.kegiatan-rutin', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l3 3 3-3', 'pattern' => ['admin.master-kegiatan-rutin.*', 'admin.kegiatan-rutin.*']],
                    ['route' => 'admin.pencapaian.index', 'label' => 'Pencapaian Siswa', 'perm' => 'menu.pencapaian-siswa', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'admin.pencapaian.*'],
                    ['route' => 'admin.monev.index', 'label' => 'Monev', 'perm' => 'menu.monev', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'admin.monev.*'],
            ]),
            $adminSidebarCollapsible('Sekolah & Guru', 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', [
                    ['route' => 'admin.pengajar.index', 'label' => 'Data Guru', 'perm' => 'menu.data-pengajar', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'pattern' => 'admin.pengajar.*'],
                    ['route' => 'admin.presensi-guru.index', 'label' => 'Presensi Guru', 'perm' => 'menu.presensi-guru', 'except_roles' => ['Pengajar', 'Wali Kelas'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'pattern' => 'admin.presensi-guru.*'],
                    ['route' => 'admin.monev-guru.index', 'label' => 'Monev Guru', 'perm' => 'menu.monev-guru', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'admin.monev-guru.*'],
                    ['route' => 'admin.sarana.index', 'label' => 'Sarana', 'perm' => 'menu.sarana', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'pattern' => 'admin.sarana.*'],
                    ['route' => 'admin.menu-makanan.index', 'label' => 'Menu Makanan', 'perm' => 'menu.menu-makanan', 'icon' => 'M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z', 'pattern' => 'admin.menu-makanan.*'],
            ]),
            $adminSidebarCollapsible('Akuntansi', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402-2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', [
                    ['route' => 'admin.akun.index', 'label' => 'Kode Rekening & Akun', 'perm' => 'menu.akun-coa', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'pattern' => 'admin.akun.*'],
                    ['route' => 'admin.cashflow.index', 'label' => 'Cashflow', 'perm' => 'menu.cashflow', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402-2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'admin.cashflow.*'],
                    ['route' => 'admin.tabungan-investasi.index', 'label' => 'Tabungan & Investasi', 'perm' => 'menu.tabungan-investasi', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'pattern' => 'admin.tabungan-investasi.*'],
                    ['route' => 'admin.jurnal.index', 'label' => 'Jurnal Umum', 'perm' => 'menu.jurnal-umum', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'admin.jurnal.*'],
                    ['route' => 'admin.laporan-keuangan.index', 'label' => 'Laporan Keuangan', 'perm' => 'menu.laporan-keuangan', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'admin.laporan-keuangan.*'],
            ]),
            $adminSidebarCollapsible('RKAS', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', [
                    ['route' => 'admin.sumber-dana.index', 'label' => 'Sumber Dana', 'perm' => 'menu.sumber-dana', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'pattern' => 'admin.sumber-dana.*'],
                    ['route' => 'admin.rkas.index', 'label' => 'RKAS', 'perm' => 'menu.rkas', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => ['admin.rkas.index', 'admin.rkas.edit']],
                    ['route' => 'admin.rkas.laporan', 'label' => 'Laporan RKAS', 'perm' => 'menu.laporan-rkas', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'pattern' => ['admin.rkas.laporan', 'admin.rkas.export-pdf']],
            ]),
            $adminSidebarCollapsible('Biaya & Pembayaran', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402-2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', [
                    ['route' => 'admin.biaya-bulanan.index', 'label' => 'Biaya Bulanan', 'perm' => 'menu.biaya-harian', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402-2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'admin.biaya-bulanan.*'],
                    ['route' => 'admin.diskon.index', 'label' => 'Diskon', 'perm' => 'menu.diskon', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'pattern' => 'admin.diskon.*'],
                    ['route' => 'admin.pembayaran-bulanan.index', 'label' => 'Rekap Pembayaran', 'perm' => 'menu.rekap-pembayaran', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'pattern' => 'admin.pembayaran-bulanan.*'],
                    ['route' => 'admin.gaji-guru.index', 'label' => 'Gaji Guru', 'perm' => 'menu.gaji-guru', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'pattern' => 'admin.gaji-guru.*'],
            ]),
            $adminSidebarCollapsible('Masukan & Komunikasi', 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z', [
                    ['route' => 'admin.kritik-saran.index', 'label' => 'Kritik & Saran', 'perm' => 'menu.kritik-saran', 'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z', 'pattern' => 'admin.kritik-saran.*'],
                    ['route' => 'admin.pengumuman.index', 'label' => 'Pengumuman', 'perm' => 'menu.pengumuman', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z', 'pattern' => 'admin.pengumuman.*'],
                    ...($chatOrangTuaEnabled ? [['route' => 'admin.orangtua-chat.index', 'label' => 'Chat Orang Tua', 'perm' => 'menu.chat-orangtua', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'pattern' => 'admin.orangtua-chat.*']] : []),
            ]),
        ]);

        if (\App\Support\AdminSettingsNav::canAccessHub($user)) {
            $roleNavItems[] = [
                'route' => 'admin.settings',
                'label' => 'Pengaturan',
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'pattern' => \App\Support\AdminSettingsNav::sidebarPatterns(),
            ];
        }

    }
    $teacherSelfServiceRoles = ['Pengajar', 'Wali Kelas'];
    if ($user && $user->hasRole('Wali Kelas')) {
        $roleNavItems = array_merge($roleNavItems, [
            [
                'group' => 'Kelas saya',
                'items' => [
                    ['route' => 'adminkelas.anak.index', 'label' => 'Siswa Kelasku', 'roles' => ['Wali Kelas'], 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'pattern' => 'adminkelas.anak.*'],
                ],
            ],
            [
                'group' => 'Kurikulum',
                'items' => [
                    ['route' => 'pengajar.presensi-guru.index', 'label' => 'Presensi Saya', 'roles' => $teacherSelfServiceRoles, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'pengajar.presensi-guru.*'],
                    ['route' => 'pengajar.monev-guru.index', 'label' => 'Monev Saya', 'roles' => $teacherSelfServiceRoles, 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'pengajar.monev-guru.*'],
                ],
            ],
        ]);
    }
    if ($user && $user->hasRole('Pengajar') && ! $user->hasRole('Wali Kelas')) {
        $roleNavItems = array_merge($roleNavItems, [
            [
                'group' => 'Pembelajaran',
                'items' => [
                    ['route' => 'pengajar.presensi-guru.index', 'label' => 'Presensi Saya', 'roles' => $teacherSelfServiceRoles, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'pengajar.presensi-guru.*'],
                ],
            ],
            [
                'group' => 'Evaluasi',
                'items' => [
                    ['route' => 'pengajar.monev-guru.index', 'label' => 'Monev Saya', 'roles' => $teacherSelfServiceRoles, 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'pengajar.monev-guru.*'],
                ],
            ],
        ]);
    }
    if ($user && $user->hasRole('Orang Tua')) {
        $chatIcon = 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z';
        $orangTuaNav = [
            ['route' => 'orangtua.pencapaian.index', 'label' => 'Pencapaian', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'orangtua.pencapaian.*'],
        ];
        if ($chatOrangTuaEnabled) {
            $orangTuaNav[] = ['route' => 'orangtua.chat.index', 'label' => 'Chat', 'icon' => $chatIcon, 'pattern' => 'orangtua.chat.*'];
        }
        $orangTuaNav = array_merge($orangTuaNav, [
            ['route' => 'orangtua.monev.index', 'label' => 'Monev', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'orangtua.monev.*'],
            ['route' => 'orangtua.kegiatan.index', 'label' => 'Agenda Belajar', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'orangtua.kegiatan.*'],
            ['route' => 'orangtua.kegiatan-rutin.index', 'label' => 'Kegiatan Rutin', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l3 3 3-3', 'pattern' => 'orangtua.kegiatan-rutin.*'],
            ['route' => 'orangtua.presensi.index', 'label' => 'Kehadiran', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'pattern' => 'orangtua.presensi.*'],
            ['route' => 'orangtua.pembayaran.index', 'label' => 'Pembayaran', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'pattern' => 'orangtua.pembayaran.*'],
            ['route' => 'orangtua.menu-makanan.index', 'label' => 'Menu Makanan', 'icon' => 'M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z', 'pattern' => 'orangtua.menu-makanan.*'],
            ['route' => 'orangtua.kritik-saran.index', 'label' => 'Saran & Kritik', 'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z', 'pattern' => 'orangtua.kritik-saran.*'],
            ['route' => 'orangtua.kesehatan.index', 'label' => 'Kesehatan Anak', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'pattern' => 'orangtua.kesehatan.*'],
        ]);
        $roleNavItems = array_merge($roleNavItems, $orangTuaNav);
        $orangTuaBottomNavItems = [
            ['route' => 'orangtua.pencapaian.index', 'label' => 'Pencapaian', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'orangtua.pencapaian.*'],
        ];
        if ($chatOrangTuaEnabled) {
            $orangTuaBottomNavItems[] = ['route' => 'orangtua.chat.index', 'label' => 'Chat', 'icon' => $chatIcon, 'pattern' => 'orangtua.chat.*', 'center' => true];
            $orangTuaBottomNavItems[] = ['route' => 'orangtua.kegiatan.index', 'label' => 'Agenda Belajar', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'orangtua.kegiatan.*'];
        } else {
            $orangTuaBottomNavItems[] = ['route' => 'orangtua.monev.index', 'label' => 'Monev', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'orangtua.monev.*'];
            $orangTuaBottomNavItems[] = ['route' => 'orangtua.kegiatan.index', 'label' => 'Agenda Belajar', 'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'pattern' => 'orangtua.kegiatan.*'];
        }
        $orangTuaMoreNavItems = [
            ['route' => 'orangtua.monev.index', 'label' => 'Monev', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'pattern' => 'orangtua.monev.*'],
            ['route' => 'orangtua.kegiatan-rutin.index', 'label' => 'Kegiatan Rutin', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l3 3 3-3', 'pattern' => 'orangtua.kegiatan-rutin.*'],
            ['route' => 'orangtua.presensi.index', 'label' => 'Kehadiran', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'pattern' => 'orangtua.presensi.*'],
            ['route' => 'orangtua.pembayaran.index', 'label' => 'Pembayaran', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'pattern' => 'orangtua.pembayaran.*'],
            ['route' => 'orangtua.menu-makanan.index', 'label' => 'Menu Makanan', 'icon' => 'M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z', 'pattern' => 'orangtua.menu-makanan.*'],
            ['route' => 'orangtua.kritik-saran.index', 'label' => 'Saran & Kritik', 'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z', 'pattern' => 'orangtua.kritik-saran.*'],
            ['route' => 'orangtua.kesehatan.index', 'label' => 'Kesehatan Anak', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'pattern' => 'orangtua.kesehatan.*'],
        ];
    }
    $roleLabel = 'Pengguna Khusus';
    if ($user && $user->hasRole('Superadmin'))
        $roleLabel = 'Superadmin';
    elseif ($user && $user->hasRole('Lembaga'))
        $roleLabel = 'Yayasan';
    elseif ($user && $user->hasRole('Admin Sekolah'))
        $roleLabel = 'Admin Sekolah';
    elseif ($user && $user->canAccessAdminPanel())
        $roleLabel = $user->getRoleNames()->first() ?: 'Staf';
    elseif ($user && $user->hasRole('Wali Kelas'))
        $roleLabel = 'Wali Kelas';
    elseif ($user && $user->hasRole('Pengajar'))
        $roleLabel = 'Guru';
    elseif ($user && $user->hasRole('Orang Tua'))
        $roleLabel = 'Wali Murid';

    $isOrangTua = $user && $user->hasRole('Orang Tua');

    $showDashboardNav = !($user && (
        $user->hasRole('Superadmin') ||
        ($user->canAccessAdminPanel() && !$user->hasRole(['Admin Sekolah', 'Lembaga']))
    ));
    if ($user && $user->hasRole('Superadmin')) {
        $homeRoute = 'superadmin.dashboard';
    } elseif ($user && $user->hasRole('Admin Sekolah')) {
        $homeRoute = 'admin.dashboard';
    } else {
        $homeRoute = (!$showDashboardNav && ($first = $user?->firstAccessibleAdminRoute())) ? $first : 'dashboard';
    }
    $dashboardRoute = ($showDashboardNav ?? true) ? $homeRoute : 'dashboard';

    if ($user) {
        if (! \App\Support\AdminSettingsNav::canAccessHub($user)) {
            $roleNavItems[] = [
                'route' => 'profile.edit',
                'label' => 'Profil',
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'pattern' => 'profile.edit',
            ];
        }

        $skipPermFilter = $user->hasRole(['Admin Sekolah', 'Lembaga']);
        $roleNavItems = \App\Support\MenuBuilder::build($roleNavItems, $user, $skipPermFilter);
    }

    $pageBreadcrumbs = $user
        ? \App\Support\AdminBreadcrumb::resolve($roleNavItems, $homeRoute, request(), $title ?? null, $subtitle ?? null)
        : [];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="turbo-cache-control" content="no-preview">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap"
        rel="stylesheet">
    <!-- Scripts -->
    <script>
        window.documentationMediaConfig = @json(\App\Support\DocumentationMedia::clientConfig());
    </script>
    @vite(['resources/css/app.css', 'resources/js/tour.js'])
</head>

<body class="font-sans antialiased text-[#2C2C2C] bg-[#F5F0E8]">
    <div x-data="{ sidebarOpen: false, moreMenuOpen: false, sidebarCollapsed: JSON.parse(localStorage.getItem('sidebarCollapsed') || 'false') }" x-init="$watch('sidebarCollapsed', v => localStorage.setItem('sidebarCollapsed', JSON.stringify(v)))" class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            <!-- Topbar -->
            @include('layouts.topbar')

            <!-- Main Content Area -->
            <main class="flex-1 w-full {{ request()->routeIs('orangtua.chat.*') ? 'overflow-hidden flex flex-col min-h-0' : 'overflow-y-auto' }} {{ ($user && $user->hasRole('Orang Tua') && !request()->routeIs('orangtua.chat.*')) ? 'pb-24' : (($user && $user->hasRole('Orang Tua')) ? 'pb-0' : 'pb-20') }} lg:pb-0">
                @if($isOrangTua && !request()->routeIs('orangtua.chat.*'))
                    {{-- Orang tua mobile: topbar disembunyikan; breadcrumb + aksi ada di bar ini --}}
                    <header class="lg:hidden sticky top-0 z-20 bg-[#FAF6F0]/95 backdrop-blur-sm border-b border-black/5 pt-[max(env(safe-area-inset-top),0px)]">
                        <div class="app-shell-bar flex flex-col gap-2 py-2">
                            @if(count($pageBreadcrumbs) > 0)
                                <x-app-breadcrumb :items="$pageBreadcrumbs" />
                            @endif
                            @if(isset($header) && count($pageBreadcrumbs ?? []) === 0)
                                <div class="page-header-toolbar shrink-0">{{ $header }}</div>
                            @endif
                        </div>
                        @if(($ortuSchools ?? null) && $ortuSchools->count() > 1)
                        <div class="px-3 pb-2 pt-0.5 border-t border-black/5">
                            <form action="{{ route('orangtua.active-sekolah.update') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <label class="text-[11px] font-semibold whitespace-nowrap text-[#6B6560]">Sekolah aktif:</label>
                                <select name="sekolah_id" class="input-field text-xs py-1 flex-1 min-w-0" onchange="this.form.submit()">
                                    @foreach($ortuSchools as $s)
                                        <option value="{{ $s->id }}" @selected(($activeOrtuSekolah?->id ?? null) === $s->id)>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        @endif
                    </header>
                @endif

                <!-- Page Content -->
                <div class="{{ request()->routeIs('orangtua.chat.*') ? 'flex-1 flex flex-col min-h-0 overflow-hidden' : 'min-h-[calc(100vh-64px)]' }} w-full app-page-body">
                    {{ $slot }}
                </div>
            </main>

            <!-- Bottom Navbar (Mobile only) -->
            @if(!request()->routeIs('orangtua.chat.*'))
                @include('layouts.bottom-nav')
            @endif
        </div>
    </div>
    <script>
        window.compressImage = function (file, maxWidth = 1280, quality = 0.8) {
            return new Promise((resolve, reject) => {
                if (!file || !file.type.startsWith('image/')) return resolve(file);
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        if (width > height) {
                            if (width > maxWidth) {
                                height *= maxWidth / width;
                                width = maxWidth;
                            }
                        } else {
                            if (height > maxWidth) {
                                width *= maxWidth / height;
                                height = maxWidth;
                            }
                        }
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        canvas.toBlob((blob) => {
                            const fileName = file.name.replace(/\.[^/.]+$/, '') + '.jpg';
                            const compressedFile = new File([blob], fileName, { type: 'image/jpeg' });
                            resolve(compressedFile);
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = reject;
                };
                reader.onerror = reject;
            });
        };

        (function () {
            const videoExtensions = ['mp4', 'mov', 'webm'];

            window.isVideoFile = function (file) {
                if (!file) {
                    return false;
                }
                if (file.type && file.type.startsWith('video/')) {
                    return true;
                }
                const ext = (file.name || '').split('.').pop()?.toLowerCase();

                return videoExtensions.includes(ext);
            };

            window.isVideoPath = function (url) {
                if (!url) {
                    return false;
                }
                const path = String(url).split('?')[0].split('#')[0];
                const ext = path.split('.').pop()?.toLowerCase();

                return videoExtensions.includes(ext);
            };

            window.seekDocumentationVideoPreview = function (video) {
                if (!video) {
                    return;
                }
                const seek = function () {
                    if (!video.duration || !Number.isFinite(video.duration)) {
                        return;
                    }
                    try {
                        video.currentTime = Math.min(0.25, video.duration * 0.05);
                    } catch (e) {
                        /* ponytail: ignore seek errors on unsupported codecs */
                    }
                };
                if (video.readyState >= 1) {
                    seek();
                } else {
                    video.addEventListener('loadedmetadata', seek, { once: true });
                }
            };

            window.__docMediaReady = new Promise(function (resolve) {
                window.__resolveDocMediaReady = resolve;
            });

            window.prepareDocumentationFile = async function (file, onProgress) {
                const report = function (p) {
                    if (onProgress && p) {
                        onProgress(p);
                    }
                };

                if (!window.isVideoFile(file)) {
                    report({ percent: 15, message: 'Mengompres gambar...' });
                    const out = typeof window.compressImage === 'function'
                        ? await window.compressImage(file)
                        : file;
                    report({ percent: 100, message: 'Selesai' });

                    return out;
                }

                if (typeof window.compressVideo === 'function') {
                    return window.compressVideo(file, onProgress);
                }

                await Promise.race([
                    window.__docMediaReady,
                    new Promise(function (_, reject) {
                        setTimeout(function () {
                            reject(new Error('Modul kompresi video belum dimuat. Jalankan npm run dev atau npm run build lalu muat ulang halaman.'));
                        }, 15000);
                    }),
                ]);

                if (typeof window.compressVideo !== 'function') {
                    throw new Error('Modul kompresi video belum dimuat. Jalankan npm run dev atau npm run build lalu muat ulang halaman.');
                }

                return window.compressVideo(file, onProgress);
            };

            window.documentationUploadProcess = async function (file, state, options) {
                const fileIndex = options?.fileIndex ?? 0;
                const fileTotal = options?.fileTotal ?? 1;
                const isFirst = fileIndex === 0;
                const isLast = fileIndex === fileTotal - 1;

                if (isFirst) {
                    state.docUploadActive = true;
                    state.docUploadProgress = 0;
                }

                const report = function (p) {
                    const slice = 100 / fileTotal;
                    const base = slice * fileIndex;
                    const percent = Math.round(base + (p.percent / 100) * slice);
                    state.docUploadProgress = Math.min(100, percent);
                };

                try {
                    return await window.prepareDocumentationFile(file, report);
                } finally {
                    if (isLast) {
                        state.docUploadActive = false;
                        state.docUploadProgress = 0;
                    }
                }
            };
        })();
    </script>
    @vite(['resources/js/app.js'])
    @auth
    <script>
        window.__tourContext = {
            route: @json($tourCurrentRoute),
            hubRoute: @json($tourHubRoute),
            isHubPage: @json($tourSessionContext['isHubPage']),
            isShowPage: @json($tourSessionContext['isShowPage']),
            showRoutes: @json($tourSessionContext['showRoutes']),
            modalTypes: @json($tourSessionContext['modalTypes']),
            steps: @json($tourPageSteps),
            modalSteps: @json($tourModalSteps),
            completed: @json(auth()->user()->tour_completed ?? []),
            completeUrl: @json(route('tour.complete')),
        };
    </script>
    @endauth
    @stack('scripts')
</body>

</html>
