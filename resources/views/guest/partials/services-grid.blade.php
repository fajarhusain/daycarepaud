@props(['cms' => [], 'heading' => true, 'title' => 'Fitur Utama', 'subtitle' => 'Empat pilar yang mendukung operasional PAUD Anda setiap hari.'])
@php
    use App\Support\GuestIllustrations;
    $blobs = ['var(--guest-blob-pink)', 'var(--guest-blob-yellow)', 'var(--guest-blob-green)', 'var(--guest-blob-blue)'];
    $services = [];
    for ($i = 1; $i <= 4; $i++) {
        $cmsIcon = $cms["facility_{$i}_icon"] ?? '';
        $services[] = [
            'title' => $cms["facility_{$i}_title"] ?? '',
            'desc' => $cms["facility_{$i}_desc"] ?? '',
            'icon_url' => GuestIllustrations::urlForFacility($i, $cmsIcon),
            'icon_emoji' => GuestIllustrations::emojiForFacility($i, $cmsIcon),
            'blob' => $blobs[$i - 1],
        ];
    }
@endphp
<section class="guest-section relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        @if($heading)
        <div class="text-center mb-10 md:mb-14" data-guest-animate="fade-up">
            <h2 class="text-3xl sm:text-4xl guest-heading text-[var(--guest-text)]">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-3 text-[var(--guest-text-muted)] max-w-2xl mx-auto">{{ $subtitle }}</p>
            @endif
        </div>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8" data-guest-stagger>
            @foreach($services as $service)
                @if($service['title'])
                <div class="text-center" data-guest-stagger-item>
                    <div class="guest-service-blob guest-service-blob-lg mb-4" style="background: {{ $service['blob'] }};">
                        @if($service['icon_url'])
                            <img src="{{ $service['icon_url'] }}" alt="" class="guest-illustration guest-illustration-icon" loading="lazy" decoding="async">
                        @elseif($service['icon_emoji'])
                            <span class="guest-illustration-emoji text-2xl" aria-hidden="true">{{ $service['icon_emoji'] }}</span>
                        @endif
                    </div>
                    <h3 class="font-bold text-sm md:text-base guest-heading">{{ $service['title'] }}</h3>
                    @if($service['desc'])
                        <p class="mt-2 text-xs md:text-sm text-[var(--guest-text-muted)] leading-relaxed hidden sm:block">{{ $service['desc'] }}</p>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>
