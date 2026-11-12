@php
    use App\Support\GuestAscent;
    $programIcons = ['icon-car-toy', 'icon-toys', 'icon-feeder', 'icon-book'];
@endphp
<section class="lg:pt-15 pt-10 lg:pb-15 pb-10 relative" aria-labelledby="programs-heading">
    <div class="container">
        <div class="text-center flex flex-col items-center">
            <p class="text-green-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Fitur Utama</p>
            <h2 id="programs-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] lg:max-w-[630px] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_features_title'] }}</h2>
            <p class="mt-3 max-w-xl text-muted-foreground wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_features_subtitle'] }}</p>
        </div>
        <div class="lg:pl-11">
            <div class="grid md:grid-cols-2 grid-cols-1 gap-y-7.5 lg:gap-x-[74px] gap-x-5 lg:pt-15 pt-10">
                @for($i = 1; $i <= 4; $i++)
                    @php
                        $title = $cms["facility_{$i}_title"] ?? '';
                        $desc = $cms["facility_{$i}_desc"] ?? '';
                    @endphp
                    @if($title)
                    <article class="relative rounded-[10px] bg-background border-2 border-[#F2F2F2] lg:p-10 p-4 transition-all duration-500 hover:shadow-3xl hover:border-transparent group/card wow fadeInUp" data-wow-delay="{{ number_format(0.3 + ($i * 0.1), 1) }}s">
                        <div class="md:max-w-[88px] max-w-[70px] w-full max-h-[88px] flex justify-center items-center rounded-[10px] border border-[#F2F2F2] bg-background sm:p-[14px] p-2.5 static lg:absolute -left-11 top-1/2 lg:-translate-y-1/2 transition-all duration-500 text-green-foreground group-hover/card:bg-green group-hover/card:text-cream-foreground">
                            <i class="{{ $programIcons[$i - 1] }} md:text-6xl text-[40px]" aria-hidden="true"></i>
                        </div>
                        <div class="lg:pl-11 mt-4 lg:mt-0">
                            <h3 class="font-semibold lg:text-2xl text-xl">
                                <a href="{{ route('guest.fasilitas') }}" class="group-hover/card:text-green-foreground transition-all duration-500">{{ $title }}</a>
                            </h3>
                            <p class="lg:mt-4 mt-3">{{ $desc }}</p>
                            <a href="{{ route('guest.fasilitas') }}" class="inline-flex items-center gap-2.5 lg:mt-7.5 mt-4 group/btn">
                                <span class="group-hover/btn:text-green-foreground transition-all duration-500">Selengkapnya</span>
                                <span class="group-hover/btn:ml-1 group-hover/btn:text-green-foreground transition-all duration-500"><i class="fa-solid fa-arrow-right"></i></span>
                            </a>
                        </div>
                    </article>
                    @endif
                @endfor
            </div>
        </div>
    </div>
    <div class="absolute top-15 right-11 z-[-1] lg:max-w-full max-w-36 md:block hidden animate-left-right" aria-hidden="true">
        <img src="{{ GuestAscent::asset('images/shapes/pencil-rocket.png') }}" alt="" class="w-full h-auto">
    </div>
</section>
