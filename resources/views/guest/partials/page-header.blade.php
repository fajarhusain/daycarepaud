@props(['badge' => '', 'title', 'subtitle' => ''])
<section class="relative overflow-hidden pt-8 pb-12 md:pt-12 md:pb-20" style="background: var(--guest-sage-light);">
    @include('guest.partials.doodles', ['variant' => 'hero'])
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center relative z-10">
        @if($badge)
            <span class="guest-badge mb-4" data-guest-animate="fade-up">{{ $badge }}</span>
        @endif
        <h1 class="text-2xl sm:text-4xl md:text-5xl guest-heading text-[var(--guest-text)]" data-guest-animate="fade-up">
            {{ $title }}
        </h1>
        @if($subtitle)
            <p class="mt-4 text-base sm:text-lg max-w-2xl mx-auto text-[var(--guest-text-muted)]" data-guest-animate="fade-up">
                {{ $subtitle }}
            </p>
        @endif
    </div>
</section>
@include('guest.partials.wave-divider', ['fill' => 'var(--guest-bg)'])
