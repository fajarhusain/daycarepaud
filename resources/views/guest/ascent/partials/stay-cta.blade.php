@php use App\Support\GuestAscent; @endphp
<section class="lg:pt-15 pt-10" aria-labelledby="stay-cta-heading">
    <div class="bg-warm py-12.5 relative z-[1]">
        <div class="container">
            <div class="flex md:flex-row flex-col justify-between items-center gap-10">
                <div class="lg:max-w-[573px] max-w-[400px]">
                    <p class="text-muted-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Tetap Terhubung</p>
                    <h2 id="stay-cta-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] mt-2.5 max-w-[410px] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_cta_title'] ?? 'Mulai digitalisasi PAUD Anda' }}</h2>
                    <p class="mt-5 wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_cta_subtitle'] ?? '' }}</p>
                    <div class="mt-9 flex flex-wrap gap-4 wow fadeInUp" data-wow-delay=".5s">
                        <a href="{{ route('guest.daftar-sekolah') }}" class="btn-rounded-full inline-flex items-center gap-2">
                            Daftar Sekolah <i class="fa-solid fa-arrow-right text-sm" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('guest.kontak') }}" class="border border-gray-200 rounded-full px-6 py-3 btn hover:text-cream-foreground inline-flex items-center gap-2">
                            Hubungi Kami <i class="fa-solid fa-phone text-sm" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <div class="relative">
                    <img src="{{ GuestAscent::asset('images/newsletter/stay-thumb.png') }}" alt="" loading="lazy" aria-hidden="true">
                </div>
            </div>
        </div>
        <div class="absolute left-0 bottom-0 z-[-1]" aria-hidden="true">
            <img src="{{ GuestAscent::asset('images/newsletter/stay-shape.png') }}" alt="">
        </div>
    </div>
</section>
