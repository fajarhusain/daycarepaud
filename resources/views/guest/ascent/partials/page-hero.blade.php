@props(['title', 'breadcrumbLabel' => null, 'subtitle' => null])
@php
    use App\Support\GuestAscent;
    $crumbLabel = $breadcrumbLabel ?? $title;
@endphp
<div class="lg:pb-15 pb-10">
    <div class="bg-warm lg:py-15 py-10">
        <div class="container">
            <div class="flex md:flex-row flex-col justify-between items-center gap-10">
                <div>
                    <h1 class="xl:text-[70px] lg:text-6xl md:text-5xl text-4xl font-bold leading-[117%]">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="lg:pt-4 pt-3 max-w-xl text-muted-foreground">{{ $subtitle }}</p>
                    @endif
                    <ul class="lg:pt-5 pt-3 flex items-center lg:gap-5 gap-2" aria-label="Breadcrumb">
                        <li>
                            <a href="{{ route('guest.beranda') }}" class="lg:text-[28px] text-xl font-bold hover:text-primary-foreground transition-colors">Beranda</a>
                        </li>
                        <li aria-hidden="true"><i class="fa-solid fa-angle-right"></i></li>
                        <li>
                            <span class="lg:text-[28px] text-xl font-bold" aria-current="page">{{ $crumbLabel }}</span>
                        </li>
                    </ul>
                </div>
                <div class="relative shrink-0">
                    <img src="{{ GuestAscent::asset('images/shapes/bread-cat.png') }}" alt="" class="absolute bottom-5 -left-[30px] animate-up-down" aria-hidden="true">
                    <img src="{{ GuestAscent::asset('images/shapes/bread-thumb.png') }}" alt="" class="sm:max-h-full max-h-60" aria-hidden="true">
                    <img src="{{ GuestAscent::asset('images/shapes/bread-child.png') }}" alt="" class="absolute bottom-0 right-0 animate-left-right" aria-hidden="true">
                </div>
            </div>
        </div>
    </div>
</div>
