@php use App\Support\GuestFeatures; @endphp
<section class="guest-section guest-section-sage relative overflow-hidden">
    @include('guest.partials.wave-divider', ['flip' => true, 'fill' => 'var(--guest-sage-dark)'])
    <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-4 pb-8">
        <div class="text-center mb-10 md:mb-14" data-guest-animate="fade-up">
            <h2 class="text-3xl sm:text-4xl guest-heading text-white">Apa Kata Pengguna</h2>
            <p class="mt-3 text-white/80 max-w-xl mx-auto">Cerita dari admin sekolah, lembaga, dan orang tua yang memakai {{ \App\Support\GuestBrand::NAME }}.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8" data-guest-stagger>
            @foreach(GuestFeatures::testimonials($cms ?? []) as $review)
            <div data-guest-stagger-item>
                <div class="guest-speech-bubble mb-6">
                    <h3 class="font-bold text-[var(--guest-text)] guest-heading text-lg mb-2">{{ $review['title'] }}</h3>
                    <p class="text-sm text-[var(--guest-text-muted)] leading-relaxed">"{{ $review['quote'] }}"</p>
                </div>
                <div class="flex items-center gap-3 pl-2">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0" style="background: var(--guest-sage);">
                        {{ mb_substr($review['name'], 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-sm text-white">{{ $review['name'] }}</p>
                        <p class="text-xs text-white/70">{{ $review['role'] }}</p>
                    </div>
                    <div class="ml-auto flex gap-0.5 text-amber-300 text-sm" aria-label="Rating {{ $review['rating'] }} dari 5">
                        @for($i = 0; $i < $review['rating']; $i++) ★ @endfor
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @include('guest.partials.wave-divider', ['fill' => 'var(--guest-bg)'])
</section>
