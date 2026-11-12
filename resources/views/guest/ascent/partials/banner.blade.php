@php
    use App\Support\GuestAscent;
    use App\Support\GuestCmsImage;
@endphp
{{-- Struktur index.html Ascent (class hanya yang ada di output.css); tanpa painting --}}
<section class="bg-warm guest-hero-section relative lg:mb-15 mb-10" aria-labelledby="hero-heading">
    <div class="container relative guest-hero-container">
        <div class="flex flex-col items-center text-center relative z-10">
            <h1 id="hero-heading" class="font-normal xl:text-[70px] lg:text-6xl md:text-5xl text-4xl xl:leading-[128%] lg:leading-[125%] md:leading-[120%] max-w-[776px] wow fadeInUp" data-wow-delay=".3s">
                {{ $cms['hero_title'] }}
            </h1>
            <div class="flex absolute right-[87px] top-14 animate-skw max-lg:hidden" aria-hidden="true">
                <img src="{{ GuestAscent::asset('images/shapes/shap.png') }}" alt="" class="w-7.5 h-12.5 relative top-9">
                <img src="{{ GuestAscent::asset('images/shapes/shap.png') }}" alt="">
                <img src="{{ GuestAscent::asset('images/shapes/shap.png') }}" alt="" class="w-5 h-8 -mt-7">
            </div>
            <p class="pt-5 max-w-[431px] wow fadeInUp" data-wow-delay=".5s">{{ $cms['hero_subtitle'] }}</p>
            <div class="mt-6 flex flex-wrap justify-center gap-4 wow fadeInUp" data-wow-delay=".6s">
                <a href="{{ route('guest.daftar-sekolah') }}" class="bg-green text-cream-foreground rounded-md max-h-15 leading-normal btn">Daftar Sekolah</a>
                <a href="{{ route('guest.kontak') }}" class="border border-gray-200 rounded-md px-6 py-3 btn hover:text-cream-foreground">Hubungi Kami</a>
            </div>
        </div>
        <div class="guest-hero-side-left lg:block hidden animate-up-down" aria-hidden="true">
            <div class="guest-hero-side-frame">
                <img src="{{ GuestCmsImage::url($cms, 'hero_left_photo', 'images/banner/boy_img_1.png') }}" alt="" class="guest-cms-img--hero-side" decoding="async">
                <span class="guest-hero-side-ring border-primary" aria-hidden="true"></span>
            </div>
        </div>
        <div class="guest-hero-side-right lg:block hidden animate-up-down" aria-hidden="true">
            <div class="guest-hero-side-frame guest-hero-side-frame--right">
                <img src="{{ GuestCmsImage::url($cms, 'hero_right_photo', 'images/banner/boy_img_2.png') }}" alt="" class="guest-cms-img--hero-side guest-cms-img--hero-side-right" decoding="async">
                <span class="guest-hero-side-ring border-secondary" aria-hidden="true"></span>
            </div>
        </div>
    </div>
    <div class="lg:block hidden pointer-events-none" aria-hidden="true">
        <div class="absolute left-0 top-[60px] animate-left-right-2">
            <img src="{{ GuestAscent::asset('images/banner/left-circle-1.png') }}" alt="">
        </div>
        <div class="absolute left-[37px] top-[186px] animate-left-right-2">
            <img src="{{ GuestAscent::asset('images/banner/left-circle-2.png') }}" alt="">
        </div>
        <div class="absolute right-0 bottom-[165px] animate-up-down">
            <img src="{{ GuestAscent::asset('images/banner/right-circle.png') }}" alt="">
        </div>
    </div>
</section>
