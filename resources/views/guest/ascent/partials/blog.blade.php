@php use App\Support\GuestCmsImage; @endphp
<section class="lg:pt-15 pt-10" aria-labelledby="blog-heading">
    <div class="lg:py-[120px] py-20 bg-[linear-gradient(180deg,_#FFF0E5_0%,_rgba(255,_240,_229,_0.00)_100%)]">
        <div class="container">
            <div class="flex justify-between items-center lg:pb-15 pb-10">
                <div class="lg:max-w-[630px]">
                    <p class="text-secondary-foreground font-bubblegum-sans text-[19px] wow fadeInUp">Berita &amp; tips</p>
                    <h2 id="blog-heading" class="font-bold lg:text-[32px] text-2xl lg:leading-[130%] wow fadeInUp" data-wow-delay=".3s">{{ $cms['section_blog_title'] }}</h2>
                </div>
            </div>
            <div class="grid lg:grid-cols-2 grid-cols-1 gap-7.5">
                <div class="flex flex-col gap-7.5">
                    @foreach([1 => 'blog-1.png', 2 => 'blog-2.png'] as $n => $file)
                    <article class="bg-background rounded-[10px] p-2.5 flex sm:flex-row flex-col sm:items-center gap-5 shadow-4xl wow fadeInUp" data-wow-delay=".3s">
                        <div class="w-full sm:max-w-[210px] shrink-0 overflow-hidden">
                            <img src="{{ GuestCmsImage::url($cms, 'blog_'.$n.'_image', 'images/blog/'.$file) }}" alt="" class="guest-cms-img--blog-thumb" loading="lazy" decoding="async">
                        </div>
                        <div>
                            <div class="lg:pb-5 pb-3 flex items-center gap-5 text-sm text-muted-foreground">
                                <p><i class="fa-regular fa-calendar-days text-secondary-foreground mr-1"></i><small>Segera</small></p>
                                <p><i class="fa-regular fa-user text-secondary-foreground mr-1"></i><small>Tim {{ \App\Support\GuestBrand::name() }}</small></p>
                            </div>
                            <h3 class="lg:max-w-[370px]">
                                <span class="md:text-2xl text-xl font-semibold">Artikel {{ $n }}  konten akan hadir di fase berikutnya</span>
                            </h3>
                            <span class="inline-flex items-center gap-2.5 lg:mt-6 mt-4 text-muted-foreground text-sm">Segera hadir</span>
                        </div>
                    </article>
                    @endforeach
                </div>
                <article class="bg-background rounded-[10px] flex flex-col items-start gap-5 shadow-4xl h-full wow fadeInUp" data-wow-delay=".3s">
                    <div class="w-full overflow-hidden px-2.5 pt-2.5 sm:px-0 sm:pt-0">
                        <img src="{{ GuestCmsImage::url($cms, 'blog_3_image', 'images/blog/blog-3.png') }}" alt="" class="guest-cms-img--blog-feature" loading="lazy" decoding="async">
                    </div>
                    <div class="px-6 sm:px-10 pb-10">
                        <div class="lg:pb-5 pb-3 flex items-center gap-5 text-sm text-muted-foreground">
                            <p><i class="fa-regular fa-calendar-days text-secondary-foreground mr-1"></i><small>Segera</small></p>
                            <p><i class="fa-regular fa-user text-secondary-foreground mr-1"></i><small>Tim {{ \App\Support\GuestBrand::name() }}</small></p>
                        </div>
                        <h3 class="md:text-2xl text-xl font-semibold">Tips komunikasi efektif antara guru dan orang tua</h3>
                        <span class="inline-flex items-center gap-2.5 lg:mt-6 mt-4 text-muted-foreground text-sm">Segera hadir</span>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
