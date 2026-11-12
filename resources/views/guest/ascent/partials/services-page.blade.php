@php use App\Support\GuestFeatures; @endphp
<div class="lg:pb-15 pb-10">
    <div class="container">
        <div class="lg:pl-11">
            <div class="grid md:grid-cols-2 grid-cols-1 gap-y-7.5 lg:gap-x-[74px] gap-x-5 lg:pt-15 pt-10">
                @foreach(GuestFeatures::servicePageCards($cms) as $i => $card)
                <article class="relative rounded-[10px] border-2 bg-background border-[#F2F2F2] lg:p-10 p-4 transition-all duration-500 hover:shadow-3xl hover:border-transparent group/card wow fadeInUp" data-wow-delay="{{ number_format(0.3 + ($i * 0.1), 1) }}s">
                    <div class="md:max-w-[88px] max-w-[70px] w-full max-h-[88px] flex justify-center items-center rounded-[10px] border border-[#F2F2F2] bg-background sm:p-[14px] p-2.5 static lg:absolute -left-11 top-1/2 lg:-translate-y-1/2 transition-all duration-500 text-green-foreground group-hover/card:bg-green group-hover/card:text-cream-foreground">
                        <i class="{{ $card['icon'] }} md:text-6xl text-[40px]" aria-hidden="true"></i>
                    </div>
                    <div class="lg:pl-11 mt-4 lg:mt-0">
                        <h3 class="font-semibold lg:text-2xl text-xl group-hover/card:text-green-foreground transition-all duration-500">{{ $card['title'] }}</h3>
                        @if($card['desc'])
                            <p class="lg:mt-4 mt-3 text-muted-foreground">{{ $card['desc'] }}</p>
                        @endif
                        <a href="{{ route('guest.kontak') }}" class="inline-flex items-center gap-2.5 lg:mt-7.5 mt-4 group/btn">
                            <span class="group-hover/btn:text-green-foreground transition-all duration-500">Selengkapnya</span>
                            <span class="group-hover/btn:ml-1 group-hover/btn:text-green-foreground transition-all duration-500">
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </span>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
</div>
