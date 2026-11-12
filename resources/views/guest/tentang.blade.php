@php
    $brand = \App\Support\GuestBrand::name();
    $pageSeo = \App\Support\GuestSeo::forInnerPage(
        $cms,
        'tentang',
        'Tentang Kami',
        route('guest.tentang'),
        $cms['seo_tentang_description'] ?? '',
    );
    $aboutPhoto = !empty($cms['about_photo']) ? \Illuminate\Support\Facades\Storage::url($cms['about_photo']) : asset('images/guest/scandinavian/atelier-nature.jpg');
    $aboutParagraphs = preg_split("/\n\s*\n/", trim($cms['about_text'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    if (empty($aboutParagraphs)) {
        $aboutParagraphs = preg_split("/\n\s*\n/", trim(\App\Support\GuestCms::data()['about_text'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    }
@endphp

<x-guest-scandinavian :cms="$cms" title="Tentang" :metaDesc="$pageSeo['description']" :canonical="route('guest.tentang')">

    <!-- ==========================================
         PAGE HERO
         ========================================== -->
    <section class="w-full bg-canvas-cream pt-10 pb-12 lg:pt-14 lg:pb-16 border-b border-border-subtle/50 relative overflow-hidden" aria-labelledby="page-hero-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 relative z-10">
            <!-- Breadcrumb Pill -->
            <nav class="flex items-center gap-2 mb-4 text-xs font-semibold" aria-label="Breadcrumb">
                <a href="{{ route('guest.beranda') }}" class="text-text-secondary hover:text-forest-deep transition-colors">Beranda</a>
                <span class="text-text-secondary/60">/</span>
                <span class="text-forest-deep" aria-current="page">Tentang Kami</span>
            </nav>

            <div class="max-w-3xl">
                <h1 id="page-hero-heading" class="font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-text-primary leading-tight mb-3">
                    {{ $pageSeo['h1'] }}
                </h1>
                <p class="text-sm sm:text-base text-text-secondary leading-relaxed">
                    {{ $cms['page_tentang_hero_lead'] }}
                </p>
            </div>
        </div>
    </section>

    <!-- ==========================================
         ABOUT STORY & PHILOSOPHY
         ========================================== -->
    <section class="w-full bg-canvas-cream py-16 lg:py-24" aria-labelledby="about-story-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-start">

                <!-- Left Column: Story -->
                <div class="lg:col-span-7 flex flex-col gap-6">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wider uppercase self-start">
                        <span class="material-symbols-outlined text-[16px]">spa</span>
                        Filosofi &amp; Visi Kami
                    </span>
                    <h2 id="about-story-heading" class="font-serif text-3xl sm:text-4xl text-text-primary font-medium leading-tight">
                        {{ $cms['about_title'] ?? 'Mengapa DaycareAI?' }}
                    </h2>

                    @foreach($aboutParagraphs as $paragraph)
                        <p class="text-sm sm:text-base text-text-secondary leading-relaxed">
                            {{ $paragraph }}
                        </p>
                    @endforeach

                    @include('guest.partials.value-pillars', ['cms' => $cms, 'class' => 'pt-4'])

                    <div class="pt-2">
                        <a href="{{ route('guest.kontak') }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all shadow-sm">
                            <span>Hubungi Kami &amp; Minta Demo</span>
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Right Column: Visual Showcase -->
                <div class="lg:col-span-5 flex flex-col gap-6">
                    <div class="relative rounded-3xl overflow-hidden shadow-lg aspect-[4/3] bg-surface-mint">
                        <img src="{{ $aboutPhoto }}" alt="{{ $cms['about_title'] ?? 'Tentang kami' }}"
                            class="w-full h-full object-cover">
                        <div class="absolute bottom-4 left-4 bg-canvas-cream/95 backdrop-blur-md px-4 py-2 rounded-xl shadow-sm flex items-center gap-2.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-forest-mid"></div>
                            <span class="text-xs text-text-primary font-semibold">{{ $cms['about_photo_caption'] }}</span>
                        </div>
                    </div>

                    <!-- Highlight Card -->
                    <div class="bg-surface-mint/50 rounded-2xl p-6 shadow-sm border border-border-subtle/50">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-surface-sage/70 flex items-center justify-center text-forest-deep">
                                <span class="material-symbols-outlined text-[20px]">diversity_1</span>
                            </div>
                            <h4 class="text-sm font-semibold text-text-primary">{{ $cms['about_partner_title'] }}</h4>
                        </div>
                        <p class="text-xs sm:text-sm text-text-secondary leading-relaxed">
                            {{ $cms['about_partner_desc'] }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Cream to Mint) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-surface-mint bg-canvas-cream">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor"></path>
        </svg>
    </div>

    <!-- ==========================================
         STATS / NILAI SECTION
         ========================================== -->
    <section class="w-full bg-surface-mint py-16 lg:py-24" aria-labelledby="about-stats-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block px-3.5 py-1 rounded-full bg-surface-sage/50 text-forest-deep text-xs font-semibold uppercase tracking-wider mb-2">
                    Nilai Keunggulan
                </span>
                <h2 id="about-stats-heading" class="font-serif text-3xl sm:text-4xl text-text-primary font-medium">
                    {{ $cms['section_stats_title'] ?? 'Nilai yang Anda dapatkan' }}
                </h2>
                <p class="text-sm text-text-secondary mt-2">
                    {{ $cms['section_stats_subtitle'] ?? 'DaycareAI dirancang untuk transparansi ortu–sekolah dan efisiensi operasional setiap hari.' }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $statIcons = ['update', 'hub', 'touch_app', 'shield'];
                @endphp
                @foreach(\App\Support\GuestFeatures::landingValues($cms) as $i => $item)
                <div class="bg-canvas-cream rounded-2xl p-6 shadow-sm border border-border-subtle/60 flex flex-col items-center text-center hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-2xl bg-surface-mint flex items-center justify-center text-forest-deep mb-4">
                        <span class="material-symbols-outlined text-[28px]">{{ $statIcons[$i] ?? 'check_circle' }}</span>
                    </div>
                    <h3 class="font-serif text-2xl font-semibold text-text-primary mb-1">
                        {{ $item['value'] }}
                    </h3>
                    <p class="text-xs sm:text-sm text-text-secondary leading-relaxed">
                        {{ $item['label'] }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Mint to Forest Deep) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-forest-deep bg-surface-mint">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor"></path>
        </svg>
    </div>

    <!-- ==========================================
         TESTIMONIALS SECTION
         ========================================== -->
    <section class="w-full bg-forest-deep text-white py-20 lg:py-24 relative overflow-hidden" aria-labelledby="about-testimonial-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block px-3.5 py-1 rounded-full bg-surface-mint/20 text-surface-mint text-xs font-semibold uppercase tracking-wider mb-2">
                    Ulasan &amp; Kepercayaan
                </span>
                <h2 id="about-testimonial-heading" class="font-serif text-3xl sm:text-4xl text-white font-medium">
                    {{ $cms['section_testimonial_title'] ?? 'Apa kata pengguna' }}
                </h2>
                <p class="text-sm text-surface-sage mt-2">
                    {{ $cms['section_testimonial_subtitle'] ?? 'Cerita nyata dari kepala sekolah, pendidik, dan orang tua murid.' }}
                </p>
            </div>

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
                    <div class="relative bg-surface-card text-text-primary rounded-3xl p-8 shadow-xl">
                        <div class="flex text-amber-400 mb-3">
                            @for($s = 0; $s < ($review['rating'] ?? 5); $s++)
                                <span class="material-symbols-outlined text-[18px] filled">star</span>
                            @endfor
                        </div>
                        <h4 class="font-semibold text-text-primary text-sm mb-2">{{ $review['title'] }}</h4>
                        <p class="text-xs sm:text-sm text-text-secondary leading-relaxed italic mb-2">
                            “{{ $review['quote'] }}”
                        </p>
                        <div class="absolute -bottom-3 left-10 w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-t-[12px] border-t-surface-card"></div>
                    </div>
                    <div class="flex items-center gap-3.5 mt-6 ml-4">
                        <div class="w-12 h-12 rounded-full overflow-hidden shadow-md shrink-0 border border-white/20">
                            <img src="{{ $parentAvatars[$idx] ?? $parentAvatars[0] }}" alt="{{ $review['name'] }}" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <p class="text-sm text-white font-semibold leading-tight">{{ $review['name'] }}</p>
                            <p class="text-xs text-surface-sage leading-normal">{{ $review['role'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Forest Deep to Canvas Cream) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-canvas-cream bg-forest-deep">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 100" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 C380,10 650,85 960,35 C1200,-5 1360,70 1440,40 L1440,100 L0,100 Z" fill="currentColor"></path>
        </svg>
    </div>

    <!-- ==========================================
         CTA BANNER
         ========================================== -->
    <section class="w-full bg-canvas-cream py-16 lg:py-20" aria-labelledby="about-cta-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="bg-surface-mint rounded-3xl p-8 lg:p-14 shadow-md border border-surface-sage/50 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-xl">
                    <span class="text-xs font-semibold text-forest-deep uppercase tracking-wider block mb-2">Mulai Sekarang</span>
                    <h2 id="about-cta-heading" class="font-serif text-2xl sm:text-3xl lg:text-4xl text-text-primary font-medium leading-tight mb-3">
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
                    <a href="{{ route('guest.kontak') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-canvas-cream text-forest-deep text-sm font-semibold hover:bg-surface-sage/50 transition-colors shadow-sm">
                        <span>Hubungi Kami</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-guest-scandinavian>
