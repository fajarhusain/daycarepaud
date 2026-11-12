@php
    use App\Support\GuestAscent;
    $slides = [
        ['icon' => 'icon-mat', 'title' => $cms['facility_1_title'] ?? 'Fitur 1', 'desc' => $cms['facility_1_desc'] ?? ''],
        ['icon' => 'icon-baby-body', 'title' => $cms['facility_2_title'] ?? 'Fitur 2', 'desc' => $cms['facility_2_desc'] ?? ''],
        ['icon' => 'icon-teddy-bear', 'title' => $cms['facility_3_title'] ?? 'Fitur 3', 'desc' => $cms['facility_3_desc'] ?? ''],
        ['icon' => 'icon-feeder', 'title' => $cms['facility_4_title'] ?? 'Fitur 4', 'desc' => $cms['facility_4_desc'] ?? ''],
    ];
@endphp
<section class="pt-15 pb-15 relative bg-warm lg:bg-transparent services" aria-labelledby="services-heading">
    <div class="container">
        <div class="relative after:absolute after:right-0 after:top-0 after:lg:bg-warm after:bg-transparent after:w-[calc(100%-279px)] after:h-[calc(100%-120px)] after:rounded-[10px] after:z-[-1]">
            <div class="flex lg:flex-row flex-col justify-between lg:items-center gap-6">
                <div class="flex-shrink-0 lg:w-[30%]">
                    <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Layanan</p>
                    <h2 id="services-heading" class="font-bold lg:text-[32px] text-2xl lg:leading-[130%] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_features_title'] }}</h2>
                </div>
                <div class="lg:w-[50%]">
                    <p class="wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_features_subtitle'] }}</p>
                </div>
            </div>
            <div class="lg:flex justify-between">
                <div class="lg:w-[25%]">
                    <div class="relative lg:mt-7.5 mt-5">
                        <div class="service-pagination"></div>
                        <div class="lg:mt-10 mt-5">
                            <a href="{{ route('guest.fasilitas') }}" class="px-7.5 py-5 border border-secondary rounded-md btn group inline-flex items-center gap-2">Selengkapnya <i class="fa-solid fa-arrow-right text-secondary-foreground transition-all duration-500 group-hover:text-cream-foreground"></i></a>
                        </div>
                    </div>
                </div>
                <div class="lg:w-[70%] mt-6 lg:mt-0">
                    <div class="swiper service-swiper">
                        <div class="swiper-wrapper [&_.swiper-slide-active>.service-card]:bg-background [&_.swiper-slide-active_.card-footer]:opacity-100 [&_.swiper-slide-active_.card-footer]:visible">
                            @foreach($slides as $slide)
                            <div class="swiper-slide">
                                <div class="service-card rounded-[10px] px-7.5 py-9 bg-transparent hover:bg-background transition-all duration-500 hover:shadow-3xl m-2.5 group/card">
                                    <i class="{{ $slide['icon'] }} text-[65px] text-green-foreground" aria-hidden="true"></i>
                                    <h3 class="lg:max-w-[176px] mt-5">
                                        <a href="{{ route('guest.fasilitas') }}" class="lg:text-2xl text-xl font-semibold leading-[141%] group-hover/card:text-green-foreground transition-all duration-500">{{ $slide['title'] }}</a>
                                    </h3>
                                    <div class="card-footer opacity-0 invisible transition-all duration-500 group-hover/card:opacity-100 group-hover/card:visible">
                                        <p class="mt-[15px] lg:max-w-[223px]">{{ $slide['desc'] }}</p>
                                        <a href="{{ route('guest.fasilitas') }}" class="inline-flex items-center gap-2.5 lg:mt-7.5 mt-4 group/btn">
                                            <span class="group-hover/btn:text-green-foreground transition-all duration-500">Selengkapnya</span>
                                            <span class="group-hover/btn:ml-1 text-green-foreground transition-all duration-500"><i class="fa-solid fa-arrow-right"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute lg:left-24 left-4 lg:bottom-20 bottom-3 animate-left-right sm:block hidden" aria-hidden="true">
        <img src="{{ GuestAscent::asset('images/shapes/man.png') }}" alt="">
    </div>
</section>
