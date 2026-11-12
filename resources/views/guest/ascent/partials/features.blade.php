@php
    use App\Support\GuestAscent;
    $cards = [
        1 => ['image' => 'images/extra-curricula/img-1.png', 'icon' => 'icon-ring-bell', 'icon_color' => 'text-[#0A6375]'],
        2 => ['image' => 'images/extra-curricula/img-3.png', 'icon' => 'icon-doll', 'icon_color' => 'text-primary-foreground'],
        3 => ['image' => 'images/extra-curricula/img-2.png', 'icon' => 'icon-doll-2', 'icon_color' => 'text-green-foreground'],
        4 => ['image' => 'images/extra-curricula/img-1.png', 'icon' => 'icon-kindergarden', 'icon_color' => 'text-destructive-foreground'],
    ];
@endphp
<section class="lg:pt-15 lg:pb-15 pt-10 pb-10 relative" aria-labelledby="features-heading">
    <div class="container">
        <div class="flex flex-col justify-center items-center text-center">
            <p class="text-primary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Fitur</p>
            <h2 id="features-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] mt-2.5 max-w-[514px] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_features_title'] }}</h2>
            <p class="mt-3 max-w-xl text-muted-foreground wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_features_subtitle'] }}</p>
        </div>
        <div class="lg:pt-15 pt-10 grid lg:grid-cols-2 sm:grid-cols-2 grid-cols-1 gap-7.5">
            @for($i = 1; $i <= 4; $i++)
                @php
                    $title = $cms["facility_{$i}_title"] ?? '';
                    $desc = $cms["facility_{$i}_desc"] ?? '';
                    $card = $cards[$i];
                    $imgUrl = GuestAscent::asset($card['image']);
                @endphp
                @if($title)
                <article class="border border-[#F2F2F2] bg-background rounded-[10px] p-7.5 group/card layer-card wow fadeInUp" data-wow-delay="{{ number_format(0.4 + ($i * 0.1), 1) }}s">
                    <div class="relative overflow-hidden">
                        <img src="{{ $imgUrl }}" alt="{{ $title }}" class="w-full" loading="lazy">
                        <div class="absolute left-0 top-full w-full h-full flex" aria-hidden="true">
                            @for($layer = 0; $layer < 4; $layer++)
                            <div class="image-layer-hover flex-1" style="background-image: url('{{ $imgUrl }}')"></div>
                            @endfor
                        </div>
                    </div>
                    <div class="pt-7.5">
                        <h3 class="lg:text-2xl text-xl font-semibold lg:leading-[140%]">
                            <a href="{{ route('guest.fasilitas') }}" class="group-hover/card:text-destructive-foreground transition-all duration-500">{{ $title }}</a>
                        </h3>
                        <p class="pt-2 text-sm text-muted-foreground">{{ $desc }}</p>
                        <div class="lg:pt-7.5 pt-5 flex justify-between items-center">
                            <a href="{{ route('guest.fasilitas') }}" class="flex gap-2 items-center text-sm">
                                <span class="group-hover/card:text-destructive-foreground transition-all duration-500">Pelajari lebih lanjut</span>
                                <i class="fa-solid fa-arrow-right text-destructive-foreground"></i>
                            </a>
                            <i class="{{ $card['icon'] }} lg:text-6xl text-[40px] {{ $card['icon_color'] }}" aria-hidden="true"></i>
                        </div>
                    </div>
                </article>
                @endif
            @endfor
        </div>
    </div>
    <div class="absolute left-0 top-0 z-[-1] 2xl:w-auto w-96 hidden xl:block" aria-hidden="true">
        <img src="{{ GuestAscent::asset('images/shapes/class-j.png') }}" alt="" loading="lazy">
    </div>
</section>
