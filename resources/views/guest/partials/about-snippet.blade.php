@props(['cms' => []])
<section class="guest-section relative overflow-hidden">
    @include('guest.partials.doodles', ['variant' => 'hero'])
    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div data-guest-animate="fade-up">
                <h2 class="text-3xl sm:text-4xl guest-heading text-[var(--guest-text)]">
                    {{ $cms['about_title'] ?? 'Tentang '.\App\Support\GuestBrand::NAME }}
                </h2>
                <p class="mt-5 text-[var(--guest-text-muted)] leading-relaxed">
                    {{ Str::limit(strip_tags($cms['about_text'] ?? \App\Support\GuestBrand::NAME.' menghubungkan orang tua dan sekolah, mempermudah operasional harian, dan didukung AI untuk dokumentasi yang lebih ringan.'), 280) }}
                </p>
                <a href="{{ route('guest.tentang') }}" class="guest-btn guest-btn-secondary mt-8">Pelajari Lebih Lanjut</a>
            </div>
            <div class="flex justify-center lg:justify-end" data-guest-animate="fade-up">
                @if(!empty($cms['about_photo']))
                    <img src="{{ Storage::url($cms['about_photo']) }}" alt="Tentang {{ \App\Support\GuestBrand::NAME }}" class="w-full max-w-md rounded-3xl object-cover aspect-[4/3]">
                @else
                    <div class="w-full max-w-md rounded-3xl aspect-[4/3] flex items-center justify-center p-6" style="background: var(--guest-sage-light);">
                        @include('guest.partials.illustration', [
                            'name' => 'placeholder.about',
                            'alt' => 'Tentang '.\App\Support\GuestBrand::NAME,
                            'class' => 'guest-illustration w-full h-full max-h-48 object-contain',
                        ])
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
