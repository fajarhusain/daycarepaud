<x-guest-scandinavian :cms="$cms" title="Beranda">
    @php
        $brand = \App\Support\GuestBrand::name();
        $heroPhoto = !empty($cms['hero_photo']) ? \Illuminate\Support\Facades\Storage::url($cms['hero_photo']) : asset('images/guest/scandinavian/hero-nature.jpg');
        $aboutPhoto = !empty($cms['about_photo']) ? \Illuminate\Support\Facades\Storage::url($cms['about_photo']) : asset('images/guest/scandinavian/atelier-nature.jpg');
        $faqPhoto = !empty($cms['faq_image']) ? \Illuminate\Support\Facades\Storage::url($cms['faq_image']) : asset('images/guest/scandinavian/atelier-nature.jpg');

        $aboutParagraphs = preg_split("/\n\s*\n/", trim($cms['about_text'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($aboutParagraphs)) {
            $aboutParagraphs = preg_split("/\n\s*\n/", trim(\App\Support\GuestCms::data()['about_text'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        }
    @endphp

    <!-- ==========================================
         SECTION 1: HERO / BANNER
         ========================================== -->
    <section class="relative w-full overflow-hidden bg-canvas-cream pt-8 pb-16 lg:pt-14 lg:pb-28"
        aria-labelledby="hero-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">

                <!-- Left Editorial Column -->
                <div class="lg:col-span-7 flex flex-col items-start z-10">
                    <!-- Micro Badges -->
                    <div class="flex flex-wrap items-center gap-2.5 mb-6">
                        <span
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wider uppercase shadow-sm">
                            {{ $cms['hero_badge_primary'] }}
                        </span>
                        <span
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-surface-sage/40 text-text-primary text-xs font-semibold tracking-wider uppercase shadow-sm">
                            {{ $cms['hero_badge_secondary'] }}
                        </span>
                    </div>

                    <!-- Main Hero Headline -->
                    <h1 id="hero-heading"
                        class="font-serif text-4xl sm:text-5xl lg:text-6xl font-medium text-text-primary tracking-tight mb-6 max-w-xl leading-[1.08]">
                        {{ $cms['hero_title'] ?? 'Kelola Operasional Daycare & Pantau Tumbuh Kembang Anak Lebih Praktis' }}
                    </h1>

                    <!-- Warm Subtitle -->
                    <p class="text-base sm:text-lg text-text-secondary max-w-lg mb-8 leading-relaxed">
                        {{ $cms['hero_subtitle'] ?? 'Satu platform untuk admin, guru, dan orang tuapresensi, komunikasi, laporan harian, dan dokumentasi tumbuh kembang tanpa pekerjaan berulang.' }}
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-wrap items-center gap-4 w-full sm:w-auto">
                        <a href="{{ route('guest.daftar-sekolah') }}"
                            class="inline-flex items-center justify-center px-7 py-3.5 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                            <span>Daftar Sekolah</span>
                            <span class="material-symbols-outlined ml-2 text-[18px]">arrow_forward</span>
                        </a>
                        <a href="{{ route('guest.pendaftaran') }}"
                            class="inline-flex items-center justify-center px-7 py-3.5 rounded-full bg-canvas-cream text-forest-deep text-sm font-semibold hover:bg-surface-sage/50 transition-all duration-200 shadow-sm border border-border-subtle/60">
                            <span>Portal Orang Tua</span>
                        </a>
                    </div>
                </div>

                <!-- Right Visual: Scandinavian Horizon & Gentle Contours -->
                <div class="lg:col-span-5 relative w-full flex items-center justify-center">
                    <div
                        class="relative w-full aspect-[5/5] max-w-md rounded-3xl overflow-hidden bg-surface-mint shadow-xl p-4 flex flex-col justify-between">

                        <!-- Background Decorative Hills via SVG -->
                        <div class="absolute inset-0 pointer-events-none opacity-40">
                            <svg class="w-full h-full object-cover" fill="none" preserveAspectRatio="none"
                                viewBox="0 0 400 500">
                                <path d="M-50 280 C80 230, 240 310, 450 260 L450 500 L-50 500 Z" fill="#C8DEC2"></path>
                                <path d="M-50 350 C120 300, 260 380, 450 340 L450 500 L-50 500 Z" fill="#587E6C"
                                    opacity="0.3"></path>
                                <circle cx="90" cy="110" fill="#FAF6EE" r="45"></circle>
                                <circle cx="320" cy="180" fill="#587E6C" opacity="0.25" r="12"></circle>
                                <circle cx="340" cy="210" fill="#587E6C" opacity="0.2" r="6"></circle>
                            </svg>
                        </div>

                        <!-- Natural Centerpiece Photo -->
                        <div class="relative z-10 w-full my-auto rounded-2xl overflow-hidden shadow-md aspect-[5/5]">
                            <img src="{{ $heroPhoto }}" alt="{{ $cms['hero_title'] ?? 'Suasana alami daycare' }}"
                                class="w-full h-full object-cover" loading="eager">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Cream to Mint) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-surface-mint bg-canvas-cream">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         SECTION 2: STATS / NILAI YANG ANDA DAPATKAN
         ========================================== -->
    <section class="w-full bg-surface-mint py-16 lg:py-24" aria-labelledby="stats-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                <!-- Left Info Col -->
                <div class="lg:col-span-5 flex flex-col items-start">
                    <span
                        class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-surface-sage/50 text-forest-deep text-xs font-semibold tracking-wider uppercase mb-3">
                        <span class="material-symbols-outlined text-[16px]">verified</span>
                        Nilai Utama
                    </span>
                    <h2 id="stats-heading"
                        class="font-serif text-3xl sm:text-4xl lg:text-5xl text-text-primary font-medium leading-tight mb-4">
                        {{ $cms['section_stats_title'] ?? 'Nilai yang Anda dapatkan' }}
                    </h2>
                    <p class="text-sm sm:text-base text-text-secondary leading-relaxed mb-8">
                        {{ $cms['section_stats_subtitle'] ?? 'DaycareAI dirancang untuk transparansi ortu–sekolah dan efisiensi operasional setiap hari.' }}
                    </p>
                </div>

                <!-- Right 4 Stat Cards Grid -->
                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @php
                        $statIcons = ['update', 'hub', 'touch_app', 'shield'];
                    @endphp
                    @foreach(\App\Support\GuestFeatures::landingValues($cms) as $i => $item)
                        <div
                            class="bg-canvas-cream rounded-2xl p-6 shadow-sm border border-border-subtle/60 flex items-start gap-4 hover:shadow-md transition-shadow">
                            <div
                                class="w-12 h-12 rounded-2xl bg-surface-mint flex items-center justify-center shrink-0 text-forest-deep">
                                <span
                                    class="material-symbols-outlined text-[26px]">{{ $statIcons[$i] ?? 'check_circle' }}</span>
                            </div>
                            <div>
                                <h3 class="font-serif text-xl sm:text-2xl font-semibold text-text-primary mb-1">
                                    {{ $item['value'] }}
                                </h3>
                                <p class="text-xs sm:text-sm text-text-secondary leading-relaxed">
                                    {{ $item['label'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Mint to Cream) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-canvas-cream bg-surface-mint">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 100"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 C380,10 650,85 960,35 C1200,-5 1360,70 1440,40 L1440,100 L0,100 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         SECTION 3: ABOUT / TENTANG KAMI
         ========================================== -->
    <section class="w-full bg-canvas-cream py-16 lg:py-24" id="about-philosophy" aria-labelledby="about-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">

            <!-- Section Tag & Heading -->
            <div class="max-w-3xl mb-12 lg:mb-16">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-forest-deep text-[20px]">spa</span>
                    <span class="text-xs font-semibold tracking-widest uppercase text-forest-deep">Tentang Kami</span>
                </div>
                <h2 id="about-heading"
                    class="font-serif text-3xl sm:text-4xl lg:text-5xl text-text-primary leading-tight font-medium">
                    {{ $cms['about_title'] ?? 'Mengapa DaycareAI?' }}
                </h2>
            </div>

            <!-- Two-Column Editorial Story -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-start">

                <!-- Left Editorial Text -->
                <div class="lg:col-span-6 flex flex-col gap-6">
                    @foreach($aboutParagraphs as $para)
                        <p class="text-sm sm:text-base text-text-secondary leading-relaxed">
                            {{ $para }}
                        </p>
                    @endforeach

                    @include('guest.partials.value-pillars', ['cms' => $cms])

                    <!-- Highlight Box -->
                    <div
                        class="bg-surface-mint/50 rounded-2xl p-6 shadow-sm flex items-start gap-4 border border-border-subtle/50">
                        <div
                            class="w-12 h-12 rounded-full bg-surface-sage/60 flex items-center justify-center shrink-0 text-forest-deep">
                            <span class="material-symbols-outlined text-[24px]">diversity_1</span>
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-text-primary mb-1">{{ $cms['about_highlight_title'] }}</h4>
                            <p class="text-xs sm:text-sm text-text-secondary leading-relaxed">
                                {{ $cms['about_highlight_desc'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Mosaic & Highlight Box -->
                <div class="lg:col-span-6 flex flex-col gap-6">
                    <div class="relative rounded-3xl overflow-hidden shadow-lg aspect-[16/10]">
                        <img src="{{ $aboutPhoto }}" alt="{{ $cms['about_title'] ?? 'Tentang kami' }}"
                            class="w-full h-full object-cover">
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Cream to Mint) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-surface-mint bg-canvas-cream">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         SECTION 4: FITUR UTAMA / FASILITAS Daycare
         ========================================== -->
    <section class="w-full bg-surface-mint py-16 lg:py-24" id="features-section" aria-labelledby="programs-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">

            <!-- Section Header -->
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span
                    class="inline-block px-3.5 py-1 rounded-full bg-surface-sage/50 text-forest-deep text-xs font-semibold uppercase tracking-wider mb-3">
                    Modul Unggulan
                </span>
                <h2 id="programs-heading"
                    class="font-serif text-3xl sm:text-4xl lg:text-5xl text-text-primary mb-4 leading-tight font-medium">
                    {{ $cms['section_features_title'] ?? 'Fitur utama DaycareAI' }}
                </h2>
                <p class="text-sm sm:text-base text-text-secondary leading-relaxed">
                    {{ $cms['section_features_subtitle'] ?? 'Empat pilar yang mendukung operasional Daycare Anda setiap hari.' }}
                </p>
            </div>

            <!-- 4 Pillars Grid (From CMS facilities) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $facilityIcons = ['diversity_1', 'dashboard_customize', 'auto_stories', 'forum'];
                    $defaultFacilityTitles = [
                        1 => 'Penghubung Ortu & Sekolah',
                        2 => 'Operasional Internal',
                        3 => 'Laporan & Dokumentasi',
                        4 => 'Komunikasi Sekolah',
                    ];
                    $defaultFacilityDescs = [
                        1 => 'Portal orang tua terintegrasi: pantau absensi, menu makan, tagihan, dan pesan harian langsung dari ponsel.',
                        2 => 'Satu sistem untuk data siswa, kelas, absensi guru, kegiatan rutin, serta pembukuan sederhana.',
                        3 => 'Generate laporan monev otomatis per siswa, dokumentasi foto aktivitas, dan rekap kehadiran tanpa spreadsheet manual.',
                        4 => 'Kanal pengumuman resmi dan ruang pesan terarah antara guru dan orang tua tanpa repot chat personal tercecer.',
                    ];
                @endphp

                @for($i = 1; $i <= 4; $i++)
                    @php
                        $title = trim($cms["facility_{$i}_title"] ?? '') ?: $defaultFacilityTitles[$i];
                        $desc = trim($cms["facility_{$i}_desc"] ?? '') ?: $defaultFacilityDescs[$i];
                    @endphp
                    <div
                        class="group bg-surface-card rounded-3xl p-8 flex flex-col items-center text-center shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 border border-border-subtle/50">
                        <div
                            class="w-16 h-16 rounded-full bg-surface-mint flex items-center justify-center text-forest-deep mb-6 group-hover:bg-surface-sage transition-colors">
                            <span class="material-symbols-outlined text-[30px]">{{ $facilityIcons[$i - 1] }}</span>
                        </div>
                        <span class="text-xs font-semibold text-forest-deep uppercase tracking-widest mb-1">Pilar
                            0{{ $i }}</span>
                        <h3 class="font-serif text-xl font-semibold text-text-primary mb-3">
                            {{ $title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-text-secondary leading-relaxed mb-6">
                            {{ $desc }}
                        </p>
                        <div class="mt-auto pt-4 flex items-center gap-1.5 text-forest-deep text-xs font-semibold">
                            <a href="{{ route('guest.fasilitas') }}" class="inline-flex items-center gap-1 hover:underline">
                                <span>Selengkapnya</span>
                                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('guest.fasilitas') }}"
                    class="inline-flex items-center gap-2 px-7 py-3 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all shadow-sm">
                    <span>Lihat Semua Fitur &amp; Spesifikasi Lengkap</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            </div>

        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Mint to Cream) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-canvas-cream bg-surface-mint">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 100"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 C380,10 650,85 960,35 C1200,-5 1360,70 1440,40 L1440,100 L0,100 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         SECTION 5: FAQ (PERTANYAAN YANG SERING DIAJUKAN)
         ========================================== -->
    <section class="w-full bg-canvas-cream py-16 lg:py-24" id="faq-section" aria-labelledby="faq-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                <!-- Left Visual Image & Reassurance -->
                <div class="lg:col-span-5 flex flex-col gap-6">
                    <div class="relative rounded-3xl overflow-hidden shadow-md aspect-[4/3] bg-surface-mint">
                        <img src="{{ $faqPhoto }}" alt="FAQ Daycare" class="w-full h-full object-cover">
                    </div>
                    <div class="bg-surface-mint/50 p-6 rounded-2xl border border-border-subtle/50">
                        <h4 class="text-sm font-semibold text-text-primary mb-1">{{ $cms['faq_sidebar_title'] }}</h4>
                        @if(!empty($cms['kontak_telepon']))
                            <a href="{{ \App\Support\GuestWhatsApp::url(\App\Support\GuestWhatsApp::demoIntro()) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 text-xs text-forest-deep font-semibold hover:underline">
                                <span class="material-symbols-outlined text-[16px]">call</span>
                                <span>{{ $cms['faq_whatsapp_cta'] }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Right Accordion Column -->
                <div class="lg:col-span-7">
                    <div class="mb-8">
                        <span
                            class="inline-block px-3.5 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold uppercase tracking-wider mb-2">
                            Tanya Jawab
                        </span>
                        <h2 id="faq-heading" class="font-serif text-3xl sm:text-4xl text-text-primary font-medium">
                            {{ \App\Support\GuestFeatures::faqTitle($cms) }}
                        </h2>
                    </div>

                    <div class="space-y-4" x-data="{ activeFaq: 0 }">
                        @foreach(\App\Support\GuestFeatures::faqs($cms) as $i => $faq)
                            <div class="bg-surface-card rounded-2xl p-6 shadow-sm border border-border-subtle/60 transition-colors"
                                :class="activeFaq === {{ $i }} ? 'border-forest-deep/40 shadow-md' : 'hover:border-surface-sage'">
                                <button type="button"
                                    class="w-full flex items-center justify-between gap-4 text-left cursor-pointer focus:outline-none"
                                    @click="activeFaq = (activeFaq === {{ $i }} ? null : {{ $i }})">
                                    <h3 class="text-base sm:text-lg font-semibold text-text-primary leading-snug">
                                        {{ $faq['q'] }}
                                    </h3>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-transform duration-200"
                                        :class="activeFaq === {{ $i }} ? 'rotate-180 bg-forest-deep text-white' : 'bg-surface-mint text-forest-deep'">
                                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
                                    </div>
                                </button>
                                <div x-show="activeFaq === {{ $i }}" x-cloak x-transition.opacity.duration.200ms
                                    class="mt-4 text-xs sm:text-sm text-text-secondary leading-relaxed border-t border-border-subtle/40 pt-4">
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Cream to Mint) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-surface-mint bg-canvas-cream">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         SECTION 6: USIA PESERTA DIDIK (STUDENT AGE)
         ========================================== -->
    <section class="w-full bg-surface-mint py-16 lg:py-24" id="student-age-section"
        aria-labelledby="student-age-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                <!-- Left Content -->
                <div class="lg:col-span-6 flex flex-col items-start">
                    <h2 id="student-age-heading"
                        class="font-serif text-3xl sm:text-4xl lg:text-5xl text-text-primary font-medium leading-tight mb-4">
                        {{ $cms['section_student_age_title'] ?? 'DaycareAI mendukung pengelolaan Daycare dan jenjang usia dini' }}
                    </h2>
                    <p class="text-sm sm:text-base text-text-secondary leading-relaxed mb-8">
                        {{ $cms['section_student_age_text'] ?? 'Struktur kelas fleksibel mulai dari penitipan bayi/batita, kelompok bermain, hingga jenjang taman kanak-kanak dengan penyesuaian capaian tumbuh kembang.' }}
                    </p>
                    <a href="{{ route('guest.fasilitas') }}"
                        class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all shadow-sm">
                        <span>{{ $cms['student_age_cta_label'] }}</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                </div>

                <!-- Right Age Cards Mosaic -->
                <div class="lg:col-span-6 grid grid-cols-2 gap-4">
                    @php
                        $ageCards = [
                            ['icon' => 'baby_changing_station', 'dark' => false],
                            ['icon' => 'toys', 'dark' => true],
                            ['icon' => 'school', 'dark' => true],
                            ['icon' => 'hub', 'dark' => false],
                        ];
                    @endphp
                    @foreach($ageCards as $idx => $card)
                        @php $n = $idx + 1; @endphp
                        <div
                            class="{{ $card['dark'] ? 'bg-forest-deep text-white shadow-md hover:shadow-lg' : 'bg-canvas-cream text-text-primary shadow-sm border border-border-subtle/50 hover:shadow-md' }} rounded-3xl p-6 sm:p-8 flex flex-col justify-between transition-shadow">
                            <span class="material-symbols-outlined {{ $card['dark'] ? 'text-surface-mint' : 'text-forest-deep' }} text-[28px] mb-3">{{ $card['icon'] }}</span>
                            <div>
                                <span class="text-xs uppercase font-semibold {{ $card['dark'] ? 'text-surface-mint' : 'text-text-secondary' }} tracking-wider block mb-1">{{ $cms['student_age_'.$n.'_kicker'] }}</span>
                                <h3 class="font-serif text-xl sm:text-2xl font-semibold {{ $card['dark'] ? 'text-white' : 'text-text-primary' }} mb-1">{{ $cms['student_age_'.$n.'_range'] }}</h3>
                                <p class="text-xs {{ $card['dark'] ? 'text-surface-sage' : 'text-text-secondary' }}">{{ $cms['student_age_'.$n.'_desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Mint to Forest Deep) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-forest-deep bg-surface-mint">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         SECTION 7: TESTIMONIALS (SUARA HANGAT KELUARGA KAMI)
         ========================================== -->
    <section class="w-full bg-forest-deep text-white py-20 lg:py-28 relative overflow-hidden"
        aria-labelledby="testimonial-heading">
        <!-- Organic backdrops -->
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-forest-mid/30 blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-surface-forest/40 blur-3xl pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 relative z-10">
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div>
                    <span
                        class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-surface-mint/20 text-surface-mint text-xs font-semibold uppercase tracking-wider mb-3">
                        {{ $cms['testimonial_section_badge'] }}
                    </span>
                    <h2 id="testimonial-heading"
                        class="font-serif text-3xl sm:text-4xl lg:text-5xl text-white leading-tight font-medium">
                        {{ $cms['section_testimonial_title'] ?? 'Apa kata pengguna' }}
                    </h2>
                    <p class="text-sm sm:text-base text-surface-sage mt-2 max-w-xl">
                        {{ $cms['section_testimonial_subtitle'] ?? 'Pengalaman nyata kepala sekolah, guru, dan orang tua murid.' }}
                    </p>
                </div>
                <div
                    class="flex items-center gap-2 bg-forest-mid/50 px-4 py-2 rounded-full self-start md:self-auto border border-white/10">
                    <span class="material-symbols-outlined text-surface-mint text-[18px]">verified</span>
                    <span class="text-xs text-white font-semibold">{{ $cms['testimonial_trust_label'] }}</span>
                </div>
            </div>

            <!-- Testimonial Cards Mosaic (Speech Bubbles) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                @php
                    $parentAvatars = [
                        asset('images/guest/scandinavian/parent-1.jpg'),
                        asset('images/guest/scandinavian/parent-2.jpg'),
                        asset('images/guest/scandinavian/parent-3.jpg'),
                    ];
                @endphp

                @foreach(\App\Support\GuestFeatures::testimonials($cms) as $idx => $review)
                    <div class="flex flex-col">
                        <!-- Speech Bubble Container -->
                        <div class="relative bg-surface-card text-text-primary rounded-3xl p-8 shadow-xl">
                            <!-- Stars -->
                            <div class="flex text-amber-400 mb-4">
                                @for($s = 0; $s < ($review['rating'] ?? 5); $s++)
                                    <span class="material-symbols-outlined text-[18px] filled">star</span>
                                @endfor
                            </div>
                            <h4 class="font-semibold text-text-primary text-sm mb-2">{{ $review['title'] }}</h4>
                            <p class="text-xs sm:text-sm text-text-secondary leading-relaxed italic mb-2">
                                “{{ $review['quote'] }}”
                            </p>
                            <!-- Bubble Tail -->
                            <div
                                class="absolute -bottom-3 left-10 w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-t-[12px] border-t-surface-card">
                            </div>
                        </div>

                        <!-- Reviewer Info -->
                        <div class="flex items-center gap-3.5 mt-6 ml-4">
                            <div class="w-12 h-12 rounded-full overflow-hidden shadow-md shrink-0 border border-white/20">
                                <img src="{{ $parentAvatars[$idx] ?? $parentAvatars[0] }}" alt="{{ $review['name'] }}"
                                    class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="text-sm text-white font-semibold leading-tight">
                                    {{ $review['name'] }}
                                </p>
                                <p class="text-xs text-surface-sage leading-normal">
                                    {{ $review['role'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Forest Deep to Canvas Cream) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-canvas-cream bg-forest-deep">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 100"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 C380,10 650,85 960,35 C1200,-5 1360,70 1440,40 L1440,100 L0,100 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         SECTION 8: WAWASAN & BERITA (BLOG)
         ========================================== -->
    <section class="w-full bg-canvas-cream py-16 lg:py-24" id="blog-section" aria-labelledby="blog-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 gap-4">
                <div>
                    <span
                        class="inline-block px-3.5 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold uppercase tracking-wider mb-2">
                        Berita &amp; Tips
                    </span>
                    <h2 id="blog-heading"
                        class="font-serif text-3xl sm:text-4xl lg:text-5xl text-text-primary font-medium">
                        {{ $cms['section_blog_title'] ?? 'Wawasan seputar Daycare dan digitalisasi sekolah' }}
                    </h2>
                </div>
                <p class="text-xs sm:text-sm text-text-secondary max-w-xs">
                    {{ $cms['section_blog_subtitle'] }}
                </p>
            </div>

            <!-- Articles Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

                <!-- Left Column (Articles 1 & 2) -->
                <div class="lg:col-span-6 flex flex-col gap-6">
                    <!-- Article 1 -->
                    <article
                        class="bg-surface-card rounded-3xl p-5 sm:p-6 shadow-sm border border-border-subtle/60 flex flex-col sm:flex-row gap-5 items-center hover:shadow-md transition-shadow">
                        <div class="w-full sm:w-44 h-36 rounded-2xl overflow-hidden shrink-0 bg-surface-mint">
                            <img src="{{ !empty($cms['blog_1_image']) ? \Illuminate\Support\Facades\Storage::url($cms['blog_1_image']) : asset('images/guest/scandinavian/hero-nature.jpg') }}"
                                alt="Artikel 1" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3 text-xs text-text-secondary mb-2">
                                <span
                                    class="material-symbols-outlined text-[15px] text-forest-deep">calendar_today</span>
                                <span>Segera Hadir</span>
                                <span>•</span>
                                <span>Tim {{ $brand }}</span>
                            </div>
                            <h3 class="font-serif text-lg sm:text-xl font-semibold text-text-primary mb-2 leading-snug">
                                {{ $cms['blog_1_title'] }}
                            </h3>
                            <span class="inline-flex items-center gap-1.5 text-xs text-forest-deep font-semibold">
                                <span>Baca selengkapnya</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </span>
                        </div>
                    </article>

                    <!-- Article 2 -->
                    <article
                        class="bg-surface-card rounded-3xl p-5 sm:p-6 shadow-sm border border-border-subtle/60 flex flex-col sm:flex-row gap-5 items-center hover:shadow-md transition-shadow">
                        <div class="w-full sm:w-44 h-36 rounded-2xl overflow-hidden shrink-0 bg-surface-mint">
                            <img src="{{ !empty($cms['blog_2_image']) ? \Illuminate\Support\Facades\Storage::url($cms['blog_2_image']) : asset('images/guest/scandinavian/atelier-nature.jpg') }}"
                                alt="Artikel 2" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3 text-xs text-text-secondary mb-2">
                                <span
                                    class="material-symbols-outlined text-[15px] text-forest-deep">calendar_today</span>
                                <span>Segera Hadir</span>
                                <span>•</span>
                                <span>Tim {{ $brand }}</span>
                            </div>
                            <h3 class="font-serif text-lg sm:text-xl font-semibold text-text-primary mb-2 leading-snug">
                                {{ $cms['blog_2_title'] }}
                            </h3>
                            <span class="inline-flex items-center gap-1.5 text-xs text-forest-deep font-semibold">
                                <span>Baca selengkapnya</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                            </span>
                        </div>
                    </article>
                </div>

                <!-- Right Column (Featured Article 3) -->
                <div class="lg:col-span-6">
                    <article
                        class="h-full bg-surface-card rounded-3xl overflow-hidden shadow-sm border border-border-subtle/60 flex flex-col hover:shadow-md transition-shadow">
                        <div class="w-full h-56 sm:h-64 overflow-hidden bg-surface-mint">
                            <img src="{{ !empty($cms['blog_3_image']) ? \Illuminate\Support\Facades\Storage::url($cms['blog_3_image']) : asset('images/guest/scandinavian/hero-nature.jpg') }}"
                                alt="Artikel Unggulan" class="w-full h-full object-cover">
                        </div>
                        <div class="p-6 sm:p-8 flex flex-col flex-1">
                            <div class="flex items-center gap-3 text-xs text-text-secondary mb-3">
                                <span
                                    class="material-symbols-outlined text-[15px] text-forest-deep">calendar_today</span>
                                <span>Segera Hadir</span>
                                <span>•</span>
                                <span>Edisi Khusus</span>
                            </div>
                            <h3 class="font-serif text-2xl font-semibold text-text-primary mb-3 leading-snug">
                                {{ $cms['blog_3_title'] }}
                            </h3>
                            <p class="text-xs sm:text-sm text-text-secondary leading-relaxed mb-6">
                                {{ $cms['blog_3_teaser'] }}
                            </p>
                            <div class="mt-auto">
                                <span class="inline-flex items-center gap-1.5 text-xs text-forest-deep font-semibold">
                                    <span>Segera hadir di pembaruan berikutnya</span>
                                </span>
                            </div>
                        </div>
                    </article>
                </div>

            </div>

        </div>
    </section>

    <!-- ==========================================
         SECTION 9: CTA & RESERVASI WALKTHROUGH
         ========================================== -->
    <section class="w-full bg-canvas-cream py-16 lg:py-24" id="tour-section" aria-labelledby="cta-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div
                class="bg-surface-mint rounded-3xl p-8 lg:p-14 shadow-lg overflow-hidden relative border border-surface-sage/50">

                <!-- Decorative leaf SVG watermark -->
                <div class="absolute -right-12 -bottom-12 w-64 h-64 text-surface-sage/30 pointer-events-none">
                    <svg class="w-full h-full" fill="currentColor" viewBox="0 0 100 100">
                        <path d="M50 0 C60 25 85 40 100 50 C75 60 60 85 50 100 C40 75 15 60 0 50 C25 40 40 15 50 0 Z">
                        </path>
                    </svg>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center relative z-10">

                    <!-- Text and Actions -->
                    <div class="lg:col-span-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xs font-semibold text-forest-deep uppercase tracking-wider">
                                Mulai Sekarang
                            </span>
                        </div>
                        <h2 id="cta-heading"
                            class="font-serif text-3xl sm:text-4xl lg:text-5xl text-text-primary mb-4 leading-tight font-medium">
                            {{ $cms['section_cta_title'] ?? 'Mulai digitalisasi Daycare Anda' }}
                        </h2>
                        <p class="text-sm sm:text-base text-text-secondary mb-6 leading-relaxed">
                            {{ $cms['section_cta_subtitle'] ?? 'Tingkatkan kualitas layanan sekolah dan hadirkan kemudahan bagi guru serta orang tua hari ini.' }}
                        </p>

                        <div class="flex flex-wrap gap-3 mb-6">
                            <a href="{{ route('guest.daftar-sekolah') }}"
                                class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all shadow-md">
                                <span>Daftar Sekolah Sekarang</span>
                                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </a>
                            <a href="{{ route('guest.kontak') }}"
                                class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-canvas-cream text-forest-deep text-sm font-semibold hover:bg-surface-sage/50 transition-colors shadow-sm">
                                <span>Hubungi Kami</span>
                            </a>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            @if(!empty($cms['kontak_telepon']))
                                <a href="{{ \App\Support\GuestWhatsApp::url(\App\Support\GuestWhatsApp::demoIntro()) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-canvas-cream/80 text-forest-deep text-xs font-semibold shadow-sm hover:bg-canvas-cream transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">call</span>
                                    <span>WhatsApp: {{ $cms['kontak_telepon'] }}</span>
                                </a>
                            @endif
                            <span class="text-xs text-text-secondary">{{ $cms['section_demo_hours'] }}</span>
                        </div>
                    </div>

                    <!-- Interactive Tour Form Card -->
                    <div class="lg:col-span-6 bg-canvas-cream rounded-2xl p-6 lg:p-8 shadow-md border border-border-subtle/60"
                        x-data="{ submitted: false, parentName: '', phone: '' }">
                        <h3 class="font-serif text-xl sm:text-2xl font-semibold text-text-primary mb-1">{{ $cms['section_demo_title'] }}</h3>
                        <p class="text-xs sm:text-sm text-text-secondary mb-6">{{ $cms['section_demo_lead'] }}</p>

                        <form class="space-y-4" @submit.prevent="submitted = true">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-text-primary mb-1.5 uppercase tracking-wide">Nama
                                        Lengkap</label>
                                    <input x-model="parentName"
                                        class="w-full px-4 py-2.5 rounded-xl bg-surface-card text-text-primary placeholder:text-text-secondary/40 text-sm focus:outline-none focus:ring-2 focus:ring-forest-deep shadow-sm border border-border-subtle"
                                        placeholder="contoh: Ibu Ratna" required type="text" />
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-text-primary mb-1.5 uppercase tracking-wide">WhatsApp
                                        / No HP</label>
                                    <input x-model="phone"
                                        class="w-full px-4 py-2.5 rounded-xl bg-surface-card text-text-primary placeholder:text-text-secondary/40 text-sm focus:outline-none focus:ring-2 focus:ring-forest-deep shadow-sm border border-border-subtle"
                                        placeholder="08123456789" required type="text" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-text-primary mb-1.5 uppercase tracking-wide">Program
                                        / Kategori</label>
                                    <select
                                        class="w-full px-4 py-2.5 rounded-xl bg-surface-card text-text-primary text-sm focus:outline-none focus:ring-2 focus:ring-forest-deep shadow-sm border border-border-subtle">
                                        <option>Demo untuk sekolah / lembaga</option>
                                        <option>Portal orang tua &amp; pendaftaran siswa</option>
                                        <option>Konsultasi implementasi &amp; harga</option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-text-primary mb-1.5 uppercase tracking-wide">Pilihan
                                        Tanggal</label>
                                    <input
                                        class="w-full px-4 py-2.5 rounded-xl bg-surface-card text-text-primary text-sm focus:outline-none focus:ring-2 focus:ring-forest-deep shadow-sm border border-border-subtle"
                                        required type="date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" />
                                </div>
                            </div>
                            <div class="pt-2">
                                <button
                                    class="w-full py-3.5 px-6 rounded-full bg-forest-deep text-white text-sm font-semibold shadow-md hover:bg-primary transition-all flex items-center justify-center gap-2 cursor-pointer"
                                    type="submit">
                                    <span>Kirim permintaan demo</span>
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                </button>
                            </div>

                            <!-- Success Feedback Alert -->
                            <div x-show="submitted" x-cloak
                                class="mt-3 p-4 bg-surface-mint rounded-xl text-forest-deep text-xs sm:text-sm flex flex-col gap-2 shadow-sm border border-surface-sage">
                                <div class="flex items-center gap-2 font-semibold">
                                    <span class="material-symbols-outlined text-[20px] text-forest-deep">done_all</span>
                                    <span>Terima kasih, <span x-text="parentName"></span>!</span>
                                </div>
                                <p class="text-xs text-text-secondary leading-relaxed">
                                    {{ $cms['section_demo_success'] }}
                                </p>
                                <div class="pt-1">
                                    <a :href="'{{ \App\Support\GuestWhatsApp::url() }}&text=Halo%20Admin,%20saya%20' + encodeURIComponent(parentName) + '%20sudah%20mengisi%20jadwal%20kunjungan/demo.'"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 text-xs text-forest-deep font-semibold underline">
                                        <span>Konfirmasi via WhatsApp sekarang</span>
                                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>
</x-guest-scandinavian>