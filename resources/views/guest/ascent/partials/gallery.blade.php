@php
    use App\Support\GuestSeo;
    use Illuminate\Support\Facades\Storage;
    $items = [];
    for ($i = 1; $i <= 6; $i++) {
        if (!empty($cms['gallery_'.$i])) {
            $items[] = [
                'url' => Storage::url($cms['gallery_'.$i]),
                'alt' => GuestSeo::galleryAlt($cms, $i),
            ];
        }
    }
@endphp
@if(count($items) > 0)
<section class="lg:pt-15 lg:pb-15 pt-10 pb-10" aria-labelledby="gallery-heading">
    <div class="container">
        <div class="text-center flex flex-col items-center mb-10">
            <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Galeri</p>
            <h2 id="gallery-heading" class="font-bold lg:text-[32px] text-2xl mt-2.5 max-w-[630px] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_gallery_title'] }}</h2>
            <p class="mt-3 max-w-xl text-muted-foreground wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_gallery_subtitle'] }}</p>
        </div>
        <div class="swiper gallery-swiper wow fadeInUp" data-wow-delay=".3s">
            <div class="swiper-wrapper">
                @foreach($items as $item)
                <div class="swiper-slide">
                    <img src="{{ $item['url'] }}" alt="{{ $item['alt'] }}" class="guest-cms-img--gallery-slide" loading="lazy" decoding="async">
                </div>
                @endforeach
            </div>
            <div class="gallery-pagination mt-6 flex justify-center"></div>
        </div>
        <p class="text-center mt-8">
            <a href="{{ route('guest.harga') }}" class="btn-rounded-full">Lihat Harga</a>
        </p>
    </div>
</section>
@endif
