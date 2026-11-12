@php
    use App\Support\GuestAscent;
    use App\Support\GuestBrand;
    use App\Support\GuestCmsImage;
    use App\Support\GuestSeo;
    use Illuminate\Support\Facades\Storage;
    $showLearnMore = $showLearnMore ?? true;
    $pageLayout = $pageLayout ?? false;
    $paragraphs = preg_split("/\n\s*\n/", trim($cms['about_text'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    if ($paragraphs === [] && trim($cms['about_text'] ?? '') !== '') {
        $paragraphs = [trim($cms['about_text'])];
    }
    $cmsAboutPhoto = trim($cms['about_photo'] ?? '');
    $gridClass = $pageLayout ? 'lg:grid-cols-2 grid-cols-1 xl:gap-x-20 gap-x-7.5' : 'lg:grid-cols-[60%_40%] grid-cols-1';
    $textColClass = $pageLayout ? 'pt-7.5 sm:pt-[70px] lg:pt-0' : 'lg:max-w-[439px] pt-7.5';
@endphp
<section class="lg:pt-15 pt-10 lg:pb-15 pb-10" aria-labelledby="about-heading">
    <div class="container">
        <div class="grid {{ $gridClass }} items-center">
            <div class="relative">
                @if($cmsAboutPhoto !== '')
                    <div class="wow fadeInUp" data-wow-delay=".3s">
                        <img src="{{ Storage::url($cmsAboutPhoto) }}" alt="{{ GuestSeo::aboutPhotoAlt($cms) }}" class="guest-cms-img--about rounded-[11px]">
                    </div>
                @else
                <div class="flex sm:flex-row flex-col sm:items-end gap-6">
                    <div class="{{ $pageLayout ? 'relative ' : '' }}wow fadeInUp" data-wow-delay=".3s">
                        <div>
                            <img src="{{ GuestAscent::asset('images/about/shap-1.png') }}" alt="" aria-hidden="true">
                        </div>
                        <div class="ml-9">
                            <img src="{{ GuestCmsImage::url($cms, 'about_collage_image', 'images/about/about-1.png') }}" alt="{{ GuestSeo::aboutPhotoAlt($cms) }}" class="guest-cms-img--about-collage">
                        </div>
                        @if($pageLayout)
                        <div class="absolute -bottom-12.5 left-0 bg-primary rounded-[10px] py-4 px-[22px] flex items-center gap-3">
                            <div class="bg-background w-11 h-11 rounded-full flex justify-center items-center">
                                <img src="{{ GuestAscent::asset('images/about/customer.png') }}" alt="" aria-hidden="true">
                            </div>
                            <div>
                                <h6 class="text-cream-foreground font-bold text-2xl">Terpadu</h6>
                                <p class="text-cream-foreground text-sm">Satu platform sekolah</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="flex sm:flex-col gap-8 {{ $pageLayout ? 'pt-15 sm:pt-0' : '' }}">
                        <div class="bg-warm max-w-[212px] rounded-[11px] px-5 pt-[22px] pb-6 flex flex-col items-center justify-center text-center">
                            <img src="{{ GuestAscent::asset('images/about/icreement.png') }}" alt="" aria-hidden="true">
                            <h6 class="text-xl font-bold {{ $pageLayout ? 'mt-2.5' : '' }}">PAUD</h6>
                            <p class="text-sm">Terpadu &amp; modern</p>
                        </div>
                        <div class="bg-background max-w-[212px] rounded-[11px] px-5 pt-[22px] pb-6 flex flex-col justify-center drop-shadow-[0px_4.8px_24.4px_rgba(19,16,34,0.10)]">
                            @if($pageLayout)
                                <h6 class="text-xl font-bold text-secondary-foreground">{{ GuestBrand::name() }}</h6>
                                <p class="text-sm">Didukung AI</p>
                            @else
                                <h6 class="text-xl font-bold text-secondary-foreground">{{ GuestBrand::name() }}</h6>
                                <p class="text-sm">Satu platform sekolah</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="{{ $textColClass }}">
                <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Tentang Kami</p>
                <h2 id="about-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] {{ $pageLayout ? 'md:leading-[120%] leading-[110%]' : '' }} pb-5 wow fadeInUp" data-wow-delay=".3s">{{ $cms['about_title'] }}</h2>
                @foreach($paragraphs as $i => $paragraph)
                    <p class="wow fadeInUp {{ $i > 0 ? 'mt-4' : '' }}" data-wow-delay="{{ number_format(0.4 + ($i * 0.1), 1) }}s">{{ trim($paragraph) }}</p>
                @endforeach
                @if($pageLayout)
                <div class="lg:mt-10 mt-7 wow fadeInUp" data-wow-delay=".6s">
                    <a href="{{ route('guest.kontak') }}" class="border border-gray-200 hover:text-cream-foreground btn inline-flex items-center gap-2">
                        Minta Demo <i class="fa-solid fa-arrow-right text-sm" aria-hidden="true"></i>
                    </a>
                </div>
                @elseif($showLearnMore)
                <a href="{{ route('guest.tentang') }}" class="border border-gray-200 rounded-md lg:mt-10 mt-7 hover:text-cream-foreground btn inline-block wow fadeInUp" data-wow-delay=".6s">Pelajari Lebih Lanjut</a>
                @endif
            </div>
        </div>
    </div>
</section>
