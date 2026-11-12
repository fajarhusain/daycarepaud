@php
    use App\Support\GuestBrand;
    use App\Support\GuestWhatsApp;
    $brand = GuestBrand::name();
@endphp
<footer class="pt-[70px] relative">
    <div class="container">
        <div class="grid lg:grid-cols-[370px_auto_auto] sm:grid-cols-2 grid-cols-1 justify-between gap-7.5">
            <div class="wow fadeInUp" data-wow-delay=".3s">
                <a href="{{ route('guest.beranda') }}" class="font-bold text-xl sm:text-2xl">{{ $brand }}</a>
                <p class="pt-4 text-sm text-muted-foreground leading-relaxed">{{ $cms['footer_text'] ?? '' }}</p>
            </div>
            <div class="wow fadeInUp" data-wow-delay=".5s">
                <h3 class="text-2xl font-semibold">Menu</h3>
                <ul class="flex flex-col gap-[15px] pt-5 text-sm">
                    <li><a href="{{ route('guest.beranda') }}" class="text-[#686868] transition-all duration-500 hover:ml-1 hover:text-primary-foreground">Beranda</a></li>
                    <li><a href="{{ route('guest.tentang') }}" class="text-[#686868] transition-all duration-500 hover:ml-1 hover:text-primary-foreground">Tentang</a></li>
                    <li><a href="{{ route('guest.fasilitas') }}" class="text-[#686868] transition-all duration-500 hover:ml-1 hover:text-primary-foreground">Fitur</a></li>
                    <li><a href="{{ route('guest.harga') }}" class="text-[#686868] transition-all duration-500 hover:ml-1 hover:text-primary-foreground">Harga</a></li>
                    <li><a href="{{ route('guest.kontak') }}" class="text-[#686868] transition-all duration-500 hover:ml-1 hover:text-primary-foreground">Kontak</a></li>
                    <li><a href="{{ route('guest.daftar-sekolah') }}" class="text-[#686868] transition-all duration-500 hover:ml-1 hover:text-primary-foreground">Daftar Sekolah</a></li>
                </ul>
            </div>
            <div class="wow fadeInUp" data-wow-delay=".5s">
                <h3 class="text-2xl font-semibold">Kontak</h3>
                <ul class="flex flex-col gap-[15px] pt-5 text-sm">
                    @if(!empty($cms['kontak_alamat']))
                    <li>
                        <p class="text-[#686868] flex items-center gap-4">
                            <span class="w-11 h-11 rounded-full border border-gray-200 flex justify-center items-center text-green-foreground shrink-0"><i class="fa-solid fa-location-dot"></i></span>
                            <span class="max-w-[200px]">{{ $cms['kontak_alamat'] }}</span>
                        </p>
                    </li>
                    @endif
                    @if(!empty($cms['kontak_email']))
                    <li>
                        <p class="text-[#686868] flex items-center gap-4">
                            <span class="w-11 h-11 rounded-full border border-gray-200 flex justify-center items-center text-green-foreground shrink-0"><i class="fa-solid fa-envelope"></i></span>
                            <a href="mailto:{{ $cms['kontak_email'] }}" class="hover:text-primary-foreground">{{ $cms['kontak_email'] }}</a>
                        </p>
                    </li>
                    @endif
                    @if(!empty($cms['kontak_telepon']))
                    <li>
                        <p class="text-[#686868] flex items-center gap-4">
                            <span class="w-11 h-11 rounded-full border border-gray-200 flex justify-center items-center text-green-foreground shrink-0"><i class="fa-solid fa-phone"></i></span>
                            <a href="{{ GuestWhatsApp::url(GuestWhatsApp::demoIntro()) }}" class="hover:text-primary-foreground">{{ $cms['kontak_telepon'] }}</a>
                        </p>
                    </li>
                    @endif
                    @if(!empty($cms['kontak_jam']))
                    <li>
                        <p class="text-[#686868] flex items-center gap-4">
                            <span class="w-11 h-11 rounded-full border border-gray-200 flex justify-center items-center text-green-foreground shrink-0"><i class="fa-regular fa-clock"></i></span>
                            <span>{{ $cms['kontak_jam'] }}</span>
                        </p>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="pt-[75px] overflow-x-hidden">
            <div class="flex lg:flex-row flex-col justify-between lg:items-center pt-7.5 pb-8 border-t border-gray-200 gap-4">
                <p class="text-sm text-muted-foreground wow fadeInLeft" data-wow-delay=".3s">© {{ date('Y') }} {{ $brand }}. Hak cipta dilindungi.</p>
                <ul class="flex flex-wrap items-center gap-7.5 text-sm wow fadeInRight" data-wow-delay=".3s">
                    <li><a href="{{ route('guest.kontak') }}" class="text-[#686868] hover:text-primary-foreground">Hubungi Kami</a></li>
                </ul>
            </div>
        </div>
    </div>
    <button type="button" id="scroll-up" class="absolute bottom-20 xl:left-[90%] left-1/2 -translate-x-1/2 w-12.5 h-12.5 rounded-full bg-primary text-cream-foreground flex justify-center items-center border-[3px] border-white cursor-pointer" aria-label="Kembali ke atas">
        <i class="fa-solid fa-arrow-up"></i>
    </button>
</footer>
