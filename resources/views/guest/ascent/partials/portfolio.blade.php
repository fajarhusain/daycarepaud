@php
    use App\Support\GuestAscent;
    use App\Support\GuestSeo;
    use Illuminate\Support\Facades\Storage;
    $galleryImages = [];
    for ($i = 1; $i <= 6; $i++) {
        if (!empty($cms['gallery_'.$i])) {
            $galleryImages[] = [
                'url' => Storage::url($cms['gallery_'.$i]),
                'alt' => GuestSeo::galleryAlt($cms, $i),
            ];
        }
    }
    $templatePortfolio = [
        'images/portfolio/portfolio-1.png',
        'images/portfolio/portfolio-2.png',
        'images/portfolio/portfolio-3.png',
        'images/portfolio/portfolio-5.png',
        'images/portfolio/portfolio-6.png',
    ];
    while (count($galleryImages) < 5) {
        $idx = count($galleryImages) % count($templatePortfolio);
        $galleryImages[] = [
            'url' => GuestAscent::asset($templatePortfolio[$idx]),
            'alt' => $cms['section_gallery_title'] ?? 'Galeri',
        ];
    }
@endphp
<section class="lg:pt-15 lg:pb-15 pt-10 pb-10 portfolio" aria-labelledby="portfolio-heading">
    <div class="container">
        <div class="text-center flex flex-col items-center">
            <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Portfolio</p>
            <h2 id="portfolio-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] lg:max-w-[630px] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_gallery_title'] }}</h2>
            <p class="mt-3 max-w-xl text-muted-foreground wow fadeInUp" data-wow-delay=".4s">{{ $cms['section_gallery_subtitle'] }}</p>
        </div>
        <div class="pt-10">
            <ul class="flex items-center justify-center flex-wrap md:gap-7.5 gap-5">
                <li class="px-5 py-2.5 text-xl font-700 active-tab border border-[#F2F2F2] rounded-[10px] font-jost cursor-pointer hover:bg-primary hover:text-cream-foreground transition-all duration-500 target-tab" data-target="education">Education</li>
                <li class="px-5 py-2.5 text-xl font-700 text-[#686868] border border-[#F2F2F2] rounded-[10px] cursor-pointer hover:bg-primary hover:text-cream-foreground transition-all duration-500 target-tab" data-target="school">School</li>
                <li class="px-5 py-2.5 text-xl font-700 text-[#686868] border border-[#F2F2F2] rounded-[10px] cursor-pointer hover:bg-primary hover:text-cream-foreground transition-all duration-500 target-tab" data-target="learn">Learn</li>
                <li class="px-5 py-2.5 text-xl font-700 text-[#686868] border border-[#F2F2F2] rounded-[10px] cursor-pointer hover:bg-primary hover:text-cream-foreground transition-all duration-500 target-tab" data-target="child">Child</li>
                <li class="px-5 py-2.5 text-xl font-700 text-[#686868] border border-[#F2F2F2] rounded-[10px] cursor-pointer hover:bg-primary hover:text-cream-foreground transition-all duration-500 target-tab" data-target="coaching">Coaching</li>
            </ul>
            <div class="mt-[64px] overflow-hidden relative wow fadeInUp" data-wow-delay=".3s">
                <div data-target="education" class="grid lg:gap-7.5 gap-4 grid-cols-12 grid-rows-[277px] relative top-0 left-0 transition-all duration-500 translate-y-0 target-card">
                    @foreach(array_slice($galleryImages, 0, 5) as $idx => $img)
                        @php
                            $classes = match($idx) {
                                0 => 'sm:col-start-1 md:col-end-5 sm:col-end-7 col-span-full sm:row-span-2 relative group/card',
                                1 => 'md:col-start-5 md:col-end-10 sm:col-start-7 sm:col-end-13 col-span-full relative group/card',
                                2 => 'md:col-start-10 sm:col-start-7 sm:col-end-13 col-span-full relative group/card',
                                3 => 'md:col-start-5 md:col-end-9 sm:col-start-1 sm:col-end-7 col-span-full relative group/card',
                                default => 'md:col-start-9 sm:col-span-6 sm:col-end-13 col-span-full relative group/card',
                            };
                        @endphp
                        <div class="{{ $classes }}">
                            <img src="{{ $img['url'] }}" alt="{{ $img['alt'] }}" class="w-full h-full max-h-[300px] sm:max-h-full object-cover rounded-[10px]" loading="lazy">
                        </div>
                    @endforeach
                </div>
                <div data-target="school" class="grid lg:gap-7.5 gap-4 grid-cols-12 sm:grid-rows-[453px] absolute top-0 left-0 w-full transition-all duration-500 translate-y-10 invisible opacity-0 target-card">
                    @foreach(['portfolio-1.png', 'portfolio-2.png', 'portfolio-3.png', 'portfolio-5.png'] as $i => $file)
                    <div class="{{ $i === 0 ? 'sm:col-start-1 sm:col-end-8' : ($i === 1 ? 'sm:col-start-8 sm:col-end-13' : 'sm:col-start-1 sm:col-end-7') }} col-span-full relative group/card max-h-[453px]">
                        <img src="{{ GuestAscent::asset('images/portfolio/'.$file) }}" alt="" class="w-full h-full max-h-[300px] sm:max-h-full object-cover rounded-[10px]" loading="lazy">
                    </div>
                    @endforeach
                </div>
                <div data-target="learn" class="grid lg:gap-7.5 gap-4 grid-cols-12 sm:grid-rows-[453px] absolute top-0 left-0 w-full transition-all duration-500 translate-y-10 invisible opacity-0 target-card">
                    <div class="sm:col-start-1 sm:col-end-7 col-span-full relative group/card max-h-[453px]">
                        <img src="{{ GuestAscent::asset('images/portfolio/portfolio-3.png') }}" alt="" class="w-full h-full max-h-[300px] sm:max-h-full object-cover rounded-[10px]" loading="lazy">
                    </div>
                    <div class="sm:col-start-7 sm:col-end-13 col-span-full relative group/card max-h-[453px]">
                        <img src="{{ GuestAscent::asset('images/portfolio/portfolio-5.png') }}" alt="" class="w-full h-full max-h-[300px] sm:max-h-full object-cover rounded-[10px]" loading="lazy">
                    </div>
                </div>
                <div data-target="child" class="grid lg:gap-7.5 gap-4 grid-cols-12 sm:grid-rows-[453px] absolute top-0 left-0 w-full transition-all duration-500 translate-y-10 invisible opacity-0 target-card">
                    <div class="sm:col-start-1 sm:col-end-8 col-span-full relative group/card max-h-[453px]">
                        <img src="{{ GuestAscent::asset('images/portfolio/portfolio-1.png') }}" alt="" class="w-full h-full max-h-[300px] sm:max-h-full object-cover rounded-[10px]" loading="lazy">
                    </div>
                    <div class="sm:col-start-8 sm:col-end-13 col-span-full relative group/card max-h-[453px]">
                        <img src="{{ GuestAscent::asset('images/portfolio/portfolio-2.png') }}" alt="" class="w-full h-full max-h-[300px] sm:max-h-full object-cover rounded-[10px]" loading="lazy">
                    </div>
                </div>
                <div data-target="coaching" class="grid lg:gap-7.5 gap-4 grid-cols-12 grid-rows-[277px] absolute top-0 left-0 w-full transition-all duration-500 translate-y-10 invisible opacity-0 target-card">
                    @foreach(array_slice($galleryImages, 0, 5) as $idx => $img)
                        @php
                            $classes = match($idx) {
                                0 => 'sm:col-start-1 md:col-end-5 sm:col-end-7 col-span-full sm:row-span-2 relative group/card',
                                1 => 'md:col-start-5 md:col-end-10 sm:col-start-7 sm:col-end-13 col-span-full relative group/card',
                                2 => 'md:col-start-10 sm:col-start-7 sm:col-end-13 col-span-full relative group/card',
                                3 => 'md:col-start-5 md:col-end-9 sm:col-start-1 sm:col-end-7 col-span-full relative group/card',
                                default => 'md:col-start-9 sm:col-span-6 sm:col-end-13 col-span-full relative group/card',
                            };
                        @endphp
                        <div class="{{ $classes }}">
                            <img src="{{ $img['url'] }}" alt="{{ $img['alt'] }}" class="w-full h-full max-h-[300px] sm:max-h-full object-cover rounded-[10px]" loading="lazy">
                        </div>
                    @endforeach
                </div>
            </div>
            <p class="text-center mt-10">
                <a href="{{ route('guest.harga') }}" class="border border-gray-200 rounded-md px-6 py-3 btn inline-block">Lihat Harga</a>
            </p>
        </div>
    </div>
</section>
