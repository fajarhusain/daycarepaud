@php
    $brand = \App\Support\GuestBrand::name();
    $pageSeo = \App\Support\GuestSeo::forInnerPage(
        $cms,
        'harga',
        'Harga',
        route('guest.harga'),
        $cms['seo_harga_description'] ?? '',
    );

    $plans = [];
    for ($pi = 1; $pi <= 3; $pi++) {
        $featuresRaw = trim($cms["pricing_{$pi}_features"] ?? '');
        $plans[] = [
            'name' => $cms["pricing_{$pi}_name"] ?? '',
            'price' => $cms["pricing_{$pi}_price"] ?? '',
            'period' => $cms["pricing_{$pi}_period"] ?? '',
            'desc' => $cms["pricing_{$pi}_desc"] ?? '',
            'badge' => trim($cms["pricing_{$pi}_badge"] ?? ''),
            'featured' => trim($cms["pricing_{$pi}_featured"] ?? '') === '1',
            'features' => $featuresRaw !== '' ? preg_split("/\r\n|\r|\n/", $featuresRaw) : [],
        ];
    }
@endphp

<x-guest-scandinavian :cms="$cms" title="Harga" :metaDesc="$pageSeo['description']" :canonical="route('guest.harga')">

    <section class="w-full bg-canvas-cream pt-10 pb-12 lg:pt-14 lg:pb-16 border-b border-border-subtle/50 relative overflow-hidden" aria-labelledby="pricing-hero-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 relative z-10">
            <nav class="flex items-center gap-2 mb-4 text-xs font-semibold" aria-label="Breadcrumb">
                <a href="{{ route('guest.beranda') }}" class="text-text-secondary hover:text-forest-deep transition-colors">Beranda</a>
                <span class="text-text-secondary/60">/</span>
                <span class="text-forest-deep" aria-current="page">Harga</span>
            </nav>

            <div class="max-w-3xl">
                <h1 id="pricing-hero-heading" class="font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-text-primary leading-tight mb-3">
                    {{ $pageSeo['h1'] }}
                </h1>
                <p class="text-sm sm:text-base text-text-secondary leading-relaxed">
                    {{ $cms['page_harga_intro'] }}
                </p>
            </div>
        </div>
    </section>

    <section class="w-full bg-canvas-cream py-16 lg:py-24" aria-labelledby="pricing-section-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block px-3.5 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold uppercase tracking-wider mb-2">
                    {{ $cms['section_pricing_badge'] }}
                </span>
                <h2 id="pricing-section-heading" class="font-serif text-3xl sm:text-4xl text-text-primary font-medium">
                    {{ $cms['section_pricing_title'] }}
                </h2>
                <p class="text-sm text-text-secondary mt-2">
                    {{ $cms['section_pricing_subtitle'] }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-stretch">
                @foreach($plans as $plan)
                    <article
                        class="relative flex flex-col rounded-3xl p-8 border shadow-sm transition-shadow hover:shadow-md {{ $plan['featured'] ? 'bg-forest-deep text-white border-forest-deep shadow-lg md:-translate-y-1' : 'bg-surface-card text-text-primary border-border-subtle/60' }}">
                        @if($plan['badge'] !== '')
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-xs font-semibold {{ $plan['featured'] ? 'bg-surface-mint text-forest-deep' : 'bg-forest-deep text-white' }}">
                                {{ $plan['badge'] }}
                            </span>
                        @endif
                        <h3 class="font-serif text-xl font-semibold mb-1 {{ $plan['featured'] ? 'text-white' : 'text-text-primary' }}">{{ $plan['name'] }}</h3>
                        <p class="text-xs sm:text-sm mb-5 {{ $plan['featured'] ? 'text-surface-sage' : 'text-text-secondary' }}">{{ $plan['desc'] }}</p>
                        <div class="mb-6">
                            <span class="font-serif text-3xl sm:text-4xl font-semibold">{{ $plan['price'] }}</span>
                            @if($plan['period'] !== '')
                                <span class="text-sm {{ $plan['featured'] ? 'text-surface-sage' : 'text-text-secondary' }}"> / {{ $plan['period'] }}</span>
                            @endif
                        </div>
                        <ul class="space-y-2.5 mb-8 flex-1">
                            @foreach($plan['features'] as $feature)
                                @if(trim($feature) !== '')
                                    <li class="flex gap-2 text-sm {{ $plan['featured'] ? 'text-surface-mint' : 'text-text-secondary' }}">
                                        <span class="material-symbols-outlined text-[18px] shrink-0 {{ $plan['featured'] ? 'text-surface-mint' : 'text-forest-deep' }}">check_circle</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                        <a href="{{ route('guest.kontak') }}"
                            class="inline-flex items-center justify-center gap-2 w-full py-3 rounded-full text-sm font-semibold transition-all {{ $plan['featured'] ? 'bg-canvas-cream text-forest-deep hover:bg-white' : 'bg-forest-deep text-white hover:bg-primary' }}">
                            <span>Minta penawaran</span>
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </article>
                @endforeach
            </div>

            @if(trim($cms['pricing_footnote'] ?? '') !== '')
                <p class="text-center text-xs text-text-secondary mt-10 max-w-2xl mx-auto leading-relaxed">
                    {{ $cms['pricing_footnote'] }}
                </p>
            @endif
        </div>
    </section>

    <div class="w-full overflow-hidden leading-none -mt-1 text-surface-mint bg-canvas-cream">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor"></path>
        </svg>
    </div>

    <section class="w-full bg-surface-mint py-16 lg:py-20" aria-labelledby="pricing-cta-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="bg-canvas-cream rounded-3xl p-8 lg:p-14 shadow-sm border border-border-subtle/70 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-xl">
                    <span class="text-xs font-semibold text-forest-deep uppercase tracking-wider block mb-2">{{ $cms['harga_cta_eyebrow'] }}</span>
                    <h2 id="pricing-cta-heading" class="font-serif text-2xl sm:text-3xl lg:text-4xl text-text-primary font-medium leading-tight mb-3">
                        {{ $cms['harga_cta_title'] }}
                    </h2>
                    <p class="text-sm text-text-secondary leading-relaxed">
                        {{ $cms['harga_cta_body'] }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3 shrink-0">
                    <a href="{{ route('guest.kontak') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all shadow-sm">
                        <span>{{ $cms['harga_cta_button'] }}</span>
                        <span class="material-symbols-outlined text-[18px]">chat</span>
                    </a>
                    <a href="{{ route('guest.daftar-sekolah') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-surface-mint text-forest-deep text-sm font-semibold hover:bg-surface-sage/50 transition-colors border border-border-subtle/60">
                        <span>Daftar sekolah</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-guest-scandinavian>
