@php
    use App\Support\GuestCmsImage;
    use App\Support\GuestFeatures;
@endphp
<section class="lg:pb-15 pb-10" aria-labelledby="faq-heading">
    <div class="container">
        <div class="grid lg:grid-cols-2 grid-cols-1 items-center gap-7.5">
            <div class="max-w-[528px] lg:max-w-full mx-auto">
                <img src="{{ GuestCmsImage::url($cms, 'faq_image', 'images/faq/banner-1.png') }}" alt="Anak belajar" class="guest-cms-img--faq mx-auto lg:mx-0" loading="lazy" decoding="async">
            </div>
            <div>
                <div class="lg:max-w-[520px] pb-10">
                    <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">FAQ</p>
                    <h2 id="faq-heading" class="font-bold lg:text-[32px] text-2xl lg:leading-[130%] wow fadeInUp" data-wow-delay=".3s">{{ GuestFeatures::faqTitle($cms) }}</h2>
                </div>
                @foreach(GuestFeatures::faqs($cms) as $i => $faq)
                <div class="rounded-md border-2 border-[#F2F2F2] lg:pl-7.5 pl-5 pr-5 py-[15px] {{ $i > 0 ? 'mt-7.5' : '' }} according-item active-accor" data-open="{{ $i === 0 ? 'true' : 'false' }}">
                    <div class="flex justify-between items-center cursor-pointer according-btn">
                        <h3 class="font-bold lg:text-xl text-[17px] lg:leading-[130%] pr-4">{{ $faq['q'] }}</h3>
                        <span class="bg-primary rounded-md flex justify-center items-center px-[11px] py-2.5 lg:w-10 w-8 lg:h-10 h-8 transition-all duration-500 icon shrink-0">
                            <i class="fa-solid fa-minus text-cream-foreground"></i>
                            <i class="fa-solid fa-plus text-cream-foreground"></i>
                        </span>
                    </div>
                    <div class="max-h-0 opacity-0 invisible transition-all duration-500 accordion-details">
                        <div class="pt-5">
                            <p>{{ $faq['a'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
