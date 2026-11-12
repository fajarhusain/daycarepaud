@props([
    'title' => 'Siap Digitalisasi PAUD Anda?',
    'subtitle' => 'Hubungi tim kami untuk demo platform '.\App\Support\GuestBrand::NAME.' dan konsultasi kebutuhan sekolah Anda.',
    'primaryLabel' => 'Hubungi untuk Demo',
    'primaryRoute' => 'guest.kontak',
    'secondaryLabel' => 'Masuk',
    'secondaryRoute' => 'login',
    'tertiaryLabel' => null,
    'tertiaryRoute' => null,
])
<section class="relative overflow-hidden">
    @include('guest.partials.wave-divider', ['flip' => true, 'fill' => 'var(--guest-sage)'])
    <div class="py-16 md:py-20 text-center text-white relative" style="background: var(--guest-sage);">
        <div class="max-w-3xl mx-auto px-4 sm:px-6" data-guest-animate="fade-up">
            <h2 class="text-2xl sm:text-3xl md:text-4xl guest-heading">{{ $title }}</h2>
            <p class="mt-4 text-base sm:text-lg opacity-90 max-w-xl mx-auto">{{ $subtitle }}</p>
            <div class="mt-8 flex flex-col sm:flex-row flex-wrap justify-center gap-3">
                <a href="{{ route($primaryRoute) }}" class="guest-btn cursor-pointer" style="background:#fff; color: var(--guest-sage-dark);">{{ $primaryLabel }}</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="guest-btn guest-btn-secondary cursor-pointer" style="border-color:#fff; color:#fff; background:transparent;">Buka Dashboard</a>
                @else
                    <a href="{{ route($secondaryRoute) }}" class="guest-btn guest-btn-secondary cursor-pointer" style="border-color:#fff; color:#fff; background:transparent;">{{ $secondaryLabel }}</a>
                    @if($tertiaryLabel && $tertiaryRoute)
                        <a href="{{ route($tertiaryRoute) }}" class="guest-btn guest-btn-secondary cursor-pointer" style="border-color:#fff; color:#fff; background:transparent;">{{ $tertiaryLabel }}</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
    @include('guest.partials.wave-divider', ['fill' => 'var(--guest-bg)'])
</section>
