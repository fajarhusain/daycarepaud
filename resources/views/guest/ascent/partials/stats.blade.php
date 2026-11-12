@php
    use App\Support\GuestFeatures;
    $circleBgs = ['bg-primary', 'bg-destructive', 'bg-green', 'bg-secondary'];
@endphp
<section class="lg:pt-15 pt-10 lg:pb-15 pb-10" aria-labelledby="stats-heading">
    <div class="container">
        <div class="grid xl:grid-cols-2 lg:grid-cols-[40%_auto] grid-cols-1 gap-7.5">
            <div class="lg:max-w-[600px]">
                <p class="text-primary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Nilai</p>
                <h2 id="stats-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] lg:max-w-[410px] pb-5 wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_stats_title'] }}</h2>
                <p class="wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_stats_subtitle'] }}</p>
                <a href="{{ route('guest.kontak') }}" class="border border-gray-200 rounded-md lg:mt-10 mt-7 btn inline-block wow fadeInUp" data-wow-delay=".5s">Hubungi Kami</a>
            </div>
            <div class="grid sm:grid-cols-2 grid-cols-1 gap-7.5">
                @foreach(GuestFeatures::landingValues($cms) as $i => $item)
                <div class="rounded-lg border border-gray-200 px-[18px] lg:py-7.5 py-5 flex items-center gap-5 wow fadeInUp" data-wow-delay="{{ number_format(0.3 + ($i * 0.1), 1) }}s">
                    <div class="rounded-full {{ $circleBgs[$i] ?? 'bg-primary' }} lg:w-20 lg:h-20 w-16 h-16 flex items-center justify-center shrink-0">
                        <i class="{{ $item['icon'] }} lg:text-[40px] text-3xl text-cream-foreground" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="font-bold lg:text-[32px] md:text-[28px] text-2xl">{{ $item['value'] }}</h3>
                        <p>{{ $item['label'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
