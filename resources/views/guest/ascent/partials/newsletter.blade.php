@php
    use App\Support\GuestAscent;
    use App\Support\GuestCmsImage;
@endphp
<section class="bg-[linear-gradient(180deg,_rgba(238,255,200,0.00)_0%,_#E9FFB6_100%)] overflow-x-hidden" aria-labelledby="cta-heading">
    <div class="bg-bottom bg-no-repeat bg-contain bg-newsletter-banner">
        <div class="container">
            <div class="flex lg:flex-row flex-col lg:items-center justify-between gap-7.5 py-12 lg:py-16">
                <div class="max-w-[598px] w-full order-1 lg:order-0 animate-left-right">
                    <div class="bg-no-repeat bg-bottom bg-contain" style="background-image: url('{{ GuestAscent::asset('images/shapes/egg-shap.png') }}')">
                        <img src="{{ GuestCmsImage::url($cms, 'newsletter_image', 'images/newsletter/student.png') }}" alt="Ilustrasi siswa" class="guest-cms-img--newsletter" loading="lazy" decoding="async">
                    </div>
                </div>
                <div class="lg:max-w-[530px] order-0 lg:order-1">
                    <p class="font-bubblegum-sans text-[19px] text-muted-foreground wow fadeInUp">Mulai sekarang</p>
                    <h2 id="cta-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] md:leading-[120%] leading-[110%] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_cta_title'] }}</h2>
                    <p class="mt-5 wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_cta_subtitle'] }}</p>
                    <div class="flex flex-wrap gap-4 lg:mt-10 mt-5 wow fadeInUp" data-wow-delay=".5s">
                        <a href="{{ route('guest.daftar-sekolah') }}" class="bg-primary text-cream-foreground rounded-[10px] px-6 py-4 btn inline-flex items-center gap-2">Daftar Sekolah <i class="fa-solid fa-arrow-right"></i></a>
                        <a href="{{ route('guest.kontak') }}" class="border border-gray-200 rounded-[10px] px-6 py-4 btn inline-flex items-center gap-2 hover:text-cream-foreground">Hubungi Kami</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
