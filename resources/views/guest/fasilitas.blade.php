@php
    use App\Support\GuestFeatures;

    $brand = \App\Support\GuestBrand::name();
    $pageSeo = \App\Support\GuestSeo::forInnerPage(
        $cms,
        'fasilitas',
        'Fitur',
        route('guest.fasilitas'),
        $cms['seo_fasilitas_description'] ?? '',
    );

    $defaultFacilityTitles = [
        1 => 'Penghubung Ortu & Sekolah',
        2 => 'Operasional Internal',
        3 => 'Laporan & Dokumentasi',
        4 => 'Komunikasi Sekolah',
    ];
    $defaultFacilityDescs = [
        1 => 'Portal orang tua terintegrasi: pantau absensi, menu makan, tagihan, monev, dan pesan harian langsung dari ponsel.',
        2 => 'Satu sistem untuk data siswa, kelas, absensi guru, kegiatan rutin, inventaris sarana, serta pembukuan PSAK.',
        3 => 'Generate laporan monev otomatis per siswa, dokumentasi foto aktivitas, dan rekap kehadiran tanpa spreadsheet manual.',
        4 => 'Kanal pengumuman resmi, kritik & saran, serta chat admin–orang tua dalam satu saluran terpusat.',
    ];
    $facilityIcons = ['diversity_1', 'dashboard_customize', 'auto_stories', 'forum'];

    $pillarIcons = ['diversity_1', 'dashboard_customize', 'psychology'];

    $roleModules = [
        [
            'role' => 'Admin Sekolah',
            'icon' => 'admin_panel_settings',
            'items' => [
                'Siswa, pendaftaran, kelas & presensi',
                'Kegiatan, pencapaian & monev siswa',
                'Keuangan PSAK, RKAS & pembayaran bulanan',
                'Pengajar, monev guru & pengaturan AI',
            ],
        ],
        [
            'role' => 'Pengajar & Wali Kelas',
            'icon' => 'school',
            'items' => [
                'Presensi & dokumentasi kegiatan harian',
                'Pencapaian siswa dengan foto',
                'Kegiatan rutin & matrikulasi',
                'Saran umpan balik dari AI',
            ],
        ],
        [
            'role' => 'Orang Tua',
            'icon' => 'family_restroom',
            'items' => [
                'Presensi, kegiatan & pencapaian anak',
                'Monev PDF & menu makanan (+ voting)',
                'Pembayaran & invoice online',
                'Chat AI, kritik saran & pengumuman',
            ],
        ],
        [
            'role' => 'Lembaga Multi-Cabang',
            'icon' => 'corporate_fare',
            'items' => [
                'Kelola banyak sekolah dalam satu akun',
                'Admin sekolah per cabang',
                'Pantau kritik & saran terpusat',
                'Activity log lintas unit',
            ],
        ],
    ];
@endphp

<x-guest-scandinavian :cms="$cms" title="Fitur" :metaDesc="$pageSeo['description']" :canonical="route('guest.fasilitas')">

    <!-- PAGE HERO -->
    <section class="w-full bg-canvas-cream pt-10 pb-12 lg:pt-14 lg:pb-16 border-b border-border-subtle/50 relative overflow-hidden" aria-labelledby="features-hero-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 relative z-10">
            <nav class="flex items-center gap-2 mb-4 text-xs font-semibold" aria-label="Breadcrumb">
                <a href="{{ route('guest.beranda') }}" class="text-text-secondary hover:text-forest-deep transition-colors">Beranda</a>
                <span class="text-text-secondary/60">/</span>
                <span class="text-forest-deep" aria-current="page">Fitur</span>
            </nav>

            <div class="max-w-3xl">
                <h1 id="features-hero-heading" class="font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-text-primary leading-tight mb-3">
                    {{ $pageSeo['h1'] }}
                </h1>
                <p class="text-sm sm:text-base text-text-secondary leading-relaxed">
                    {{ $cms['section_features_subtitle'] ?? "Platform terpadu {$brand}  portal orang tua, operasional sekolah, keuangan PSAK, presensi, dan asisten AI dalam satu sistem." }}
                </p>
            </div>
        </div>
    </section>

    <!-- EMPAT AREA PRODUK (CMS) -->
    <section class="w-full bg-canvas-cream py-16 lg:py-24" aria-labelledby="areas-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block px-3.5 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold uppercase tracking-wider mb-2">
                    Empat Area Utama
                </span>
                <h2 id="areas-heading" class="font-serif text-3xl sm:text-4xl text-text-primary font-medium">
                    {{ $cms['section_features_title'] ?? "Fitur utama {$brand}" }}
                </h2>
                <p class="text-sm text-text-secondary mt-2">
                    Modul dirancang modular  aktifkan sesuai kebutuhan sekolah dan tim Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @for($i = 1; $i <= 4; $i++)
                    @php
                        $title = trim($cms["facility_{$i}_title"] ?? '') ?: $defaultFacilityTitles[$i];
                        $desc = trim($cms["facility_{$i}_desc"] ?? '') ?: $defaultFacilityDescs[$i];
                    @endphp
                    <article class="bg-surface-card rounded-3xl p-8 flex flex-col text-center shadow-sm border border-border-subtle/50 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                        <div class="w-16 h-16 rounded-full bg-surface-mint flex items-center justify-center text-forest-deep mb-6 mx-auto">
                            <span class="material-symbols-outlined text-[30px]">{{ $facilityIcons[$i - 1] }}</span>
                        </div>
                        <span class="text-xs font-semibold text-forest-deep uppercase tracking-widest mb-1">Area 0{{ $i }}</span>
                        <h3 class="font-serif text-xl font-semibold text-text-primary mb-3">{{ $title }}</h3>
                        <p class="text-xs sm:text-sm text-text-secondary leading-relaxed flex-1">{{ $desc }}</p>
                    </article>
                @endfor
            </div>
        </div>
    </section>

    <div class="w-full overflow-hidden leading-none -mt-1 text-surface-mint bg-canvas-cream">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor"></path>
        </svg>
    </div>

    <!-- TIGA PILAR PRODUK + HIGHLIGHT -->
    <section class="w-full bg-surface-mint py-16 lg:py-24" id="product-pillars" aria-labelledby="pillars-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block px-3.5 py-1 rounded-full bg-surface-sage/50 text-forest-deep text-xs font-semibold uppercase tracking-wider mb-3">
                    Nilai Produk
                </span>
                <h2 id="pillars-heading" class="font-serif text-3xl sm:text-4xl text-text-primary font-medium">
                    Tiga alasan sekolah memilih {{ $brand }}
                </h2>
                <p class="text-sm text-text-secondary mt-2">
                    Menghubungkan orang tua, mempercepat operasional internal, dan mengotomasi pekerjaan rutin dengan AI.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                @foreach(GuestFeatures::pillars($cms) as $i => $pillar)
                    <article class="bg-canvas-cream rounded-3xl p-8 shadow-sm border border-border-subtle/50 hover:shadow-md transition-shadow flex flex-col h-full">
                        <div class="w-14 h-14 rounded-2xl bg-surface-mint flex items-center justify-center text-forest-deep mb-5">
                            <span class="material-symbols-outlined text-[28px]">{{ $pillarIcons[$i] ?? 'hub' }}</span>
                        </div>
                        <p class="text-xs font-semibold text-forest-deep uppercase tracking-wide mb-1">{{ $pillar['tagline'] }}</p>
                        <h3 class="font-serif text-2xl font-semibold text-text-primary mb-3">{{ $pillar['title'] }}</h3>
                        <p class="text-sm text-text-secondary leading-relaxed mb-6">{{ $pillar['desc'] }}</p>
                        <ul class="mt-auto space-y-2.5 pt-5 border-t border-border-subtle/40">
                            @foreach($pillar['highlights'] as $highlight)
                                <li class="flex gap-2.5 text-sm text-text-secondary">
                                    <span class="material-symbols-outlined text-forest-deep text-[18px] shrink-0">check_circle</span>
                                    <span>{{ $highlight }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <div class="w-full overflow-hidden leading-none -mt-1 text-canvas-cream bg-surface-mint">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 100" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 C380,10 650,85 960,35 C1200,-5 1360,70 1440,40 L1440,100 L0,100 Z" fill="currentColor"></path>
        </svg>
    </div>

    <!-- CAKUPAN PER PERAN -->
    <section class="w-full bg-canvas-cream py-16 lg:py-24" aria-labelledby="roles-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block px-3.5 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold uppercase tracking-wider mb-2">
                    Multi-Peran
                </span>
                <h2 id="roles-heading" class="font-serif text-3xl sm:text-4xl text-text-primary font-medium">
                    Satu platform, akses sesuai peran
                </h2>
                <p class="text-sm text-text-secondary mt-2">
                    Setiap pengguna hanya melihat menu yang relevan  dengan permission yang dapat dikustom per sekolah.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($roleModules as $block)
                    <div class="bg-surface-card rounded-3xl p-8 border border-border-subtle/60 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-12 h-12 rounded-xl bg-surface-mint flex items-center justify-center text-forest-deep">
                                <span class="material-symbols-outlined text-[24px]">{{ $block['icon'] }}</span>
                            </div>
                            <h3 class="font-serif text-xl font-semibold text-text-primary">{{ $block['role'] }}</h3>
                        </div>
                        <ul class="space-y-2">
                            @foreach($block['items'] as $item)
                                <li class="flex gap-2 text-sm text-text-secondary">
                                    <span class="text-forest-deep font-bold shrink-0">·</span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <p class="text-center text-xs text-text-secondary mt-10 max-w-2xl mx-auto">
                Inventaris fasilitas fisik sekolah dikelola terpisah di modul <strong class="font-semibold text-text-primary">Sarana &amp; Prasarana</strong> pada panel admin  berbeda dari halaman marketing fitur software ini.
            </p>
        </div>
    </section>

    <div class="w-full overflow-hidden leading-none -mt-1 text-surface-mint bg-canvas-cream">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor"></path>
        </svg>
    </div>

    <!-- CARA MULAI -->
    <section class="w-full bg-surface-mint py-16 lg:py-24" aria-labelledby="onboarding-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block px-3.5 py-1 rounded-full bg-surface-sage/50 text-forest-deep text-xs font-semibold uppercase tracking-wider mb-3">
                    Implementasi
                </span>
                <h2 id="onboarding-heading" class="font-serif text-3xl sm:text-4xl text-text-primary font-medium">
                    Mulai dalam tiga langkah
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach(GuestFeatures::onboardingSteps() as $step)
                    <div class="bg-canvas-cream rounded-3xl p-8 border border-border-subtle/50">
                        <span class="inline-block font-serif text-4xl font-medium text-forest-deep/30 mb-3">{{ $step['step'] }}</span>
                        <h3 class="font-serif text-xl font-semibold text-text-primary mb-2">{{ $step['title'] }}</h3>
                        <p class="text-sm text-text-secondary leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="w-full overflow-hidden leading-none -mt-1 text-canvas-cream bg-surface-mint">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 100" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 C380,10 650,85 960,35 C1200,-5 1360,70 1440,40 L1440,100 L0,100 Z" fill="currentColor"></path>
        </svg>
    </div>

    <!-- CTA -->
    <section class="w-full bg-canvas-cream py-16 lg:py-20" aria-labelledby="features-cta-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="bg-surface-mint rounded-3xl p-8 lg:p-14 shadow-md border border-surface-sage/50 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-xl">
                    <span class="text-xs font-semibold text-forest-deep uppercase tracking-wider block mb-2">Siap mencoba?</span>
                    <h2 id="features-cta-heading" class="font-serif text-2xl sm:text-3xl lg:text-4xl text-text-primary font-medium leading-tight mb-3">
                        {{ $cms['section_cta_title'] ?? 'Mulai digitalisasi PAUD Anda' }}
                    </h2>
                    <p class="text-sm text-text-secondary leading-relaxed">
                        {{ $cms['section_cta_subtitle'] ?? 'Tingkatkan kualitas layanan sekolah dan hadirkan kemudahan bagi guru serta orang tua hari ini.' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3 shrink-0">
                    <a href="{{ route('guest.daftar-sekolah') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all shadow-sm">
                        <span>Daftar Sekolah</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                    <a href="{{ route('guest.pendaftaran') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-canvas-cream text-forest-deep text-sm font-semibold hover:bg-surface-sage/50 transition-colors shadow-sm border border-border-subtle/60">
                        <span>Portal Orang Tua</span>
                    </a>
                    <a href="{{ route('guest.kontak') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-canvas-cream text-forest-deep text-sm font-semibold hover:bg-surface-sage/50 transition-colors shadow-sm border border-border-subtle/60">
                        <span>Minta Demo</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-guest-scandinavian>
