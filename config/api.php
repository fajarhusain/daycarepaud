<?php

$googleClientIds = array_merge(
    array_map('trim', explode(',', (string) env('GOOGLE_CLIENT_IDS', ''))),
    [
        env('GOOGLE_WEB_CLIENT_ID'),
        env('GOOGLE_ANDROID_CLIENT_ID'),
        env('GOOGLE_IOS_CLIENT_ID'),
    ],
);

return [
    'version' => env('API_VERSION', '1.0.0'),

    'locale' => 'id',

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Mobile role registry
    |--------------------------------------------------------------------------
    |
    | Route middleware memakai alias (contoh: api.role:orang-tua), bukan nama
    | role Spatie. Tambah role mobile baru cukup lewat konfigurasi ini dan
    | route file milik role tersebut.
    |
    */
    'roles' => [
        'orang-tua' => 'Orang Tua',
        'pengajar' => 'Pengajar',
        'wali-kelas' => 'Wali Kelas',
        'admin-sekolah' => 'Admin Sekolah',
        'lembaga' => 'Lembaga',
        'superadmin' => 'Superadmin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant access strategies
    |--------------------------------------------------------------------------
    |
    | Strategy dipilih berdasarkan role yang dimiliki user. Role lain dapat
    | ditambahkan tanpa mengubah EnsureApiTenant.
    |
    */
    'tenant_access' => [
        'Superadmin' => 'all',
        'Orang Tua' => 'approved_children',
        'Admin Sekolah' => 'user_school',
        'Wali Kelas' => 'user_school',
        'Pengajar' => 'pengajar_school',
        'Lembaga' => 'lembaga_school',
    ],

    'tenant_bypass_roles' => [
        'Superadmin',
    ],

    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
        'cursor_default' => 20,
    ],

    'rate_limits' => [
        'login' => 10,
        'google' => 10,
        'api' => 120,
        'upload' => 10,
        'download' => 30,
    ],

    'google' => [
        'client_ids' => array_values(array_unique(array_filter($googleClientIds))),
        'tokeninfo_url' => env('GOOGLE_TOKENINFO_URL', 'https://oauth2.googleapis.com/tokeninfo'),
        'timeout_seconds' => (int) env('GOOGLE_TOKENINFO_TIMEOUT', 8),
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy API compatibility
    |--------------------------------------------------------------------------
    |
    | Endpoint lama tetap tersedia selama masa transisi, tetapi tidak menerima
    | fitur baru. Semua respons endpoint di bawah ini mendapat header
    | Deprecation dan Sunset agar client tahu harus bermigrasi.
    |
    */
    'legacy' => [
        'enabled' => filter_var(env('API_LEGACY_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'sunset' => env('API_LEGACY_SUNSET', '2027-06-30'),
        'documentation_url' => env('API_LEGACY_DOCS_URL'),
        'paths' => [
            'api/v1/guest/beranda',
            'api/v1/auth/login',
            'api/v1/auth/logout',
            'api/v1/auth/user',
            'api/v1/auth/active-sekolah',
            'api/v1/orang-tua/*',
        ],
    ],
];
