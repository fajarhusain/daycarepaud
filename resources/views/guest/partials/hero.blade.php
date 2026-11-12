@props(['cms'])
@php use App\Support\GuestFeatures; @endphp
<section class="guest-section overflow-hidden relative pt-6 md:pt-12 pb-14 md:pb-28">
    <div class="guest-hero-hills" aria-hidden="true">
        <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--guest-sage-light)" d="M0,120 C360,200 720,40 1080,120 C1260,160 1380,140 1440,120 L1440,200 L0,200 Z"/>
            <path fill="var(--guest-sage-mid)" opacity="0.4" d="M0,150 C400,80 800,180 1440,140 L1440,200 L0,200 Z"/>
        </svg>
    </div>
    @include('guest.partials.doodles', ['variant' => 'hero'])

    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            <div class="text-center lg:text-left">
                <span class="guest-badge mb-5" data-guest-animate="hero">Ortu · Sekolah · AI</span>
                <h1 class="text-2xl sm:text-4xl md:text-5xl guest-heading leading-tight text-[var(--guest-text)]" data-guest-animate="hero">
                    {{ $cms['hero_title'] ?? 'Sistem Informasi PAUD Terpadu ('.\App\Support\GuestBrand::NAME.')' }}
                </h1>
                <p class="mt-5 text-base sm:text-lg leading-relaxed text-[var(--guest-text-muted)] max-w-xl mx-auto lg:mx-0" data-guest-animate="hero">
                    {{ $cms['hero_subtitle'] ?? 'Menghubungkan orang tua dan sekolah, mempermudah operasional internal, dan mengotomasi pekerjaan rutin dengan AI dalam satu platform PAUD.' }}
                </p>
                <div class="mt-8 flex flex-col sm:flex-row flex-wrap gap-3 justify-center lg:justify-start" data-guest-animate="hero">
                    <a href="{{ route('guest.kontak') }}" class="guest-btn guest-btn-primary w-full sm:w-auto">Minta Demo</a>
                    <a href="{{ route('guest.daftar-sekolah') }}" class="guest-btn guest-btn-secondary w-full sm:w-auto">Daftar Sekolah</a>
                    <a href="{{ route('guest.fasilitas') }}" class="guest-btn guest-btn-secondary w-full sm:w-auto">Lihat Fitur</a>
                </div>
                <div class="mt-10 flex flex-wrap gap-6 justify-center lg:justify-start" data-guest-animate="hero">
                    @foreach(GuestFeatures::stats() as $stat)
                    <div class="text-center lg:text-left">
                        <p class="guest-stat-value text-xl md:text-2xl">{{ $stat['value'] }}</p>
                        <p class="mt-0.5 text-xs font-semibold text-[var(--guest-text-muted)]">{{ $stat['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-center lg:justify-end" data-guest-animate="hero">
                @if(!empty($cms['hero_photo']))
                    <img src="{{ Storage::url($cms['hero_photo']) }}" alt="Dashboard {{ \App\Support\GuestBrand::NAME }}" class="w-full max-w-lg rounded-3xl object-cover" style="aspect-ratio: 4/3;">
                @else
                    <div class="w-full max-w-lg rounded-3xl p-6 bg-white border overflow-hidden" style="border-color: var(--guest-border);">
                        @include('guest.partials.illustration', [
                            'name' => 'placeholder.hero',
                            'alt' => 'Dashboard '.\App\Support\GuestBrand::NAME,
                            'class' => 'guest-illustration guest-illustration-hero mx-auto',
                            'eager' => true,
                        ])
                        <div class="grid grid-cols-1 gap-3 mt-4">
                            @foreach(['Ortu & Sekolah', 'Operasional', 'AI Cerdas'] as $label)
                            <div class="rounded-full px-4 py-2.5 text-sm font-semibold text-center" style="background: var(--guest-sage-light); color: var(--guest-sage-dark);">{{ $label }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@include('guest.partials.wave-divider', ['fill' => 'var(--guest-bg)'])
