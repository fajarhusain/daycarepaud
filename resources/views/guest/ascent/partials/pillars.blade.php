@php
    use App\Support\GuestBrand;
    use App\Support\GuestFeatures;
    $pillarIcons = ['icon-doll', 'icon-car-toy', 'icon-blocks'];
@endphp
<section class="lg:pt-15 pt-10 lg:pb-15 pb-10" aria-labelledby="pillars-heading">
    <div class="container">
        <div class="text-center flex flex-col items-center max-w-2xl mx-auto">
            <p class="text-primary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Nilai Utama</p>
            <h2 id="pillars-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] mt-2.5 wow fadeInUp" data-wow-delay=".2s">Tiga Alasan Sekolah Memilih {{ GuestBrand::name() }}</h2>
            <p class="mt-3 text-muted-foreground wow fadeInUp" data-wow-delay=".3s">Bukan sekadar banyak fitur  {{ GuestBrand::name() }} fokus menghubungkan orang tua, mempermudah kerja tim sekolah, dan mengotomasi pekerjaan rutin dengan AI.</p>
        </div>
        <div class="grid lg:grid-cols-3 grid-cols-1 gap-7.5 lg:pt-15 pt-10">
            @foreach(GuestFeatures::pillars($cms ?? []) as $i => $pillar)
            <article class="rounded-[10px] bg-background border-2 border-[#F2F2F2] lg:p-8 p-6 flex flex-col h-full group/card hover:shadow-3xl hover:border-transparent transition-all duration-500 wow fadeInUp" data-wow-delay="{{ number_format(0.3 + ($i * 0.1), 1) }}s">
                <div class="w-16 h-16 rounded-[10px] border border-[#F2F2F2] flex items-center justify-center text-green-foreground group-hover/card:bg-green group-hover/card:text-cream-foreground transition-all duration-500">
                    <i class="{{ $pillarIcons[$i % 3] }} text-4xl" aria-hidden="true"></i>
                </div>
                <p class="text-xs font-bold uppercase tracking-wide text-secondary-foreground mt-6">{{ $pillar['tagline'] }}</p>
                <h3 class="font-semibold lg:text-xl text-lg mt-1">{{ $pillar['title'] }}</h3>
                <p class="mt-3 text-sm text-muted-foreground leading-relaxed flex-1">{{ $pillar['desc'] }}</p>
                <ul class="mt-6 pt-5 border-t border-[#F2F2F2] space-y-2">
                    @foreach($pillar['highlights'] as $highlight)
                    <li class="flex gap-2 text-sm text-muted-foreground">
                        <span class="text-green-foreground font-bold shrink-0 mt-0.5">&#10003;</span>
                        <span>{{ $highlight }}</span>
                    </li>
                    @endforeach
                </ul>
            </article>
            @endforeach
        </div>
    </div>
</section>
