@php
    use App\Support\GuestAscent;
    use App\Support\GuestFeatures;
@endphp
<section class="lg:pt-15 lg:pb-15 pt-10 pb-10 testimonial" aria-labelledby="testimonial-heading">
    <div class="container">
        <div class="flex lg:flex-row flex-col justify-between lg:items-center gap-4 lg:pb-15 pb-10">
            <div class="lg:max-w-[410px]">
                <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Testimoni</p>
                <h2 id="testimonial-heading" class="font-bold lg:text-[32px] text-2xl wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_testimonial_title'] }}</h2>
            </div>
            <p class="lg:max-w-[410px] text-muted-foreground wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_testimonial_subtitle'] }}</p>
        </div>
        <div class="relative w-full after:absolute after:left-0 after:top-0 after:lg:max-w-[calc(100%-410px)] after:max-w-[calc(100%-100px)] after:w-full after:h-full after:bg-testimonial-banner after:bg-cover after:bg-no-repeat after:z-[-1]">
            <div class="py-10">
                <div class="swiper testimonial-swiper max-w-[630px] w-full ml-auto mr-0">
                    <div class="swiper-wrapper">
                        @foreach(GuestFeatures::testimonials($cms) as $review)
                        <div class="swiper-slide">
                            <article class="lg:p-10 sm:p-8 py-8 sm:py-0 sm:-mr-10">
                                <div class="bg-background border border-[#F2F2F2] lg:p-10 p-5 max-w-[630px] w-full rounded-[10px] ml-auto shadow-[0px_0px_60px_0px_rgba(0,0,0,0.05)]">
                                    <div class="flex justify-between items-center relative z-10 lg:pb-7.5 pb-5">
                                        <div>
                                            <h3 class="md:text-xl text-lg font-semibold">{{ $review['title'] }}</h3>
                                            <p class="text-sm text-muted-foreground">{{ $review['name'] }}  {{ $review['role'] }}</p>
                                        </div>
                                        <div class="absolute right-0 z-[-1]">
                                            <img src="{{ GuestAscent::asset('images/testimonial/quotation.png') }}" alt="" class="lg:w-auto w-9" aria-hidden="true">
                                        </div>
                                    </div>
                                    <blockquote class="text-sm leading-relaxed">"{{ $review['quote'] }}"</blockquote>
                                    <ul class="flex items-center gap-1 lg:pt-6 pt-4" aria-label="Rating {{ $review['rating'] }} dari 5">
                                        @for($s = 0; $s < $review['rating']; $s++)
                                        <li><i class="fa-solid fa-star text-primary-foreground"></i></li>
                                        @endfor
                                    </ul>
                                </div>
                            </article>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
