@php
    use App\Support\GuestAscent;
    use App\Support\GuestWhatsApp;
    $phone = $cms['kontak_telepon'] ?? GuestWhatsApp::DISPLAY;
    $email = $cms['kontak_email'] ?? '';
    $address = $cms['kontak_alamat'] ?? '';
@endphp
<div class="lg:pb-15 lg:pt-15 pb-10 pt-10">
    <div class="container">
        <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-7.5">
            @if($address)
            <div class="bg-background rounded-md shadow-3xl pt-5 pb-7.5 px-7.5 text-center flex flex-col items-center wow fadeInUp" data-wow-delay=".3s">
                <div class="w-16 h-16 rounded-full flex justify-center items-center bg-green">
                    <span class="text-cream-foreground text-[28px]"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                </div>
                <h3 class="font-bold text-xl mt-5 pb-2.5">Alamat</h3>
                <p class="text-muted-foreground">{{ $address }}</p>
            </div>
            @endif
            @if($email)
            <div class="bg-background rounded-md shadow-3xl pt-5 pb-7.5 px-7.5 text-center flex flex-col items-center wow fadeInUp" data-wow-delay=".4s">
                <div class="w-16 h-16 rounded-full flex justify-center items-center bg-green">
                    <span class="text-cream-foreground text-[28px]"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                </div>
                <h3 class="font-bold text-xl mt-5 pb-2.5">Email</h3>
                <p><a href="mailto:{{ $email }}" class="text-green-foreground hover:underline">{{ $email }}</a></p>
            </div>
            @endif
            @if($phone)
            <div class="bg-background rounded-md shadow-3xl pt-5 pb-7.5 px-7.5 text-center flex flex-col items-center wow fadeInUp" data-wow-delay=".5s">
                <div class="w-16 h-16 rounded-full flex justify-center items-center bg-green">
                    <span class="text-cream-foreground text-[28px]"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                </div>
                <h3 class="font-bold text-xl mt-5 pb-2.5">WhatsApp</h3>
                <p>
                    <a href="{{ GuestWhatsApp::url(GuestWhatsApp::demoIntro()) }}" target="_blank" rel="noopener noreferrer" class="text-green-foreground hover:underline">{{ $phone }}</a>
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<section class="lg:pt-15 lg:pb-15 pb-10 pt-10" aria-labelledby="contact-form-heading">
    <div class="container">
        <div class="max-w-[546px] mx-auto text-center">
            <p class="text-secondary-foreground font-bubblegum-sans text-[19px]">Kontak</p>
            <h2 id="contact-form-heading" class="font-bold lg:text-[32px] md:text-[28px] text-2xl lg:leading-[130%] wow fadeInUp" data-wow-delay=".3s">{{ $cms['contact_form_h2'] ?? 'Minta demo & penawaran' }}</h2>
            @if(!empty($cms['contact_form_lead']))
                <p class="mt-3 text-muted-foreground wow fadeInUp" data-wow-delay=".4s">{{ $cms['contact_form_lead'] }}</p>
            @endif
        </div>
        <div class="mt-15">
            <div class="grid lg:grid-cols-2 grid-cols-1 items-center gap-7.5">
                <div class="relative hidden lg:block">
                    <div class="absolute top-1/2 -translate-y-1/2 h-full flex flex-col justify-between w-full">
                        <div class="mt-[68px] sm:w-full w-40 animate-up-down">
                            <img src="{{ GuestAscent::asset('images/contact/contact-2.png') }}" alt="" loading="lazy" aria-hidden="true">
                        </div>
                        <div class="bg-primary px-5 py-[18px] rounded-[10px] flex items-center gap-5 mb-7.5 animate-left-right max-w-xs">
                            <div>
                                <img src="{{ GuestAscent::asset('images/contact/winner.svg') }}" alt="" aria-hidden="true">
                            </div>
                            <div>
                                <h4 class="text-[28px] font-bold text-cream-foreground leading-[148%]">PAUD</h4>
                                <h5 class="text-lg font-bold text-cream-foreground mt-[5px] leading-[130%]">Terpadu &amp; modern</h5>
                            </div>
                        </div>
                    </div>
                    <div class="flex lg:justify-end justify-center">
                        <img src="{{ GuestAscent::asset('images/contact/contact-1.png') }}" alt="" loading="lazy" aria-hidden="true">
                    </div>
                </div>
                <div>
                    <div class="bg-background shadow-[0px_5px_60px_0px_rgba(0,0,0,0.05)] rounded-[10px] lg:p-10 p-5">
                        <h3 class="text-[28px] font-bold leading-[148%]">Kirim pesan</h3>
                        <form method="POST" action="{{ route('guest.kontak.send') }}" class="mt-7">
                            @csrf
                            <div class="grid sm:grid-cols-2 grid-cols-1 gap-7.5">
                                <div class="relative">
                                    <input type="text" name="nama" id="kontak-nama" value="{{ old('nama') }}" required placeholder="Nama Anda" class="w-full rounded-[10px] border-2 text-[#686868] placeholder:[#686868] border-[#F2F2F2] px-5 py-[15px] outline-none focus:border-primary/40">
                                    <label for="kontak-nama" class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground"><i class="fa-solid fa-user" aria-hidden="true"></i></label>
                                    @error('nama')<p class="text-xs mt-1 text-red-600 text-left">{{ $message }}</p>@enderror
                                </div>
                                <div class="relative">
                                    <input type="email" name="email" id="kontak-email" value="{{ old('email') }}" required placeholder="Email Anda" class="w-full rounded-[10px] border-2 text-[#686868] placeholder:[#686868] border-[#F2F2F2] px-5 py-[15px] outline-none focus:border-primary/40">
                                    <label for="kontak-email" class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-muted-foreground"><i class="fa-solid fa-envelope" aria-hidden="true"></i></label>
                                    @error('email')<p class="text-xs mt-1 text-red-600 text-left">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="relative mt-5">
                                <textarea name="pesan" id="kontak-pesan" required rows="5" placeholder="Tulis pesan Anda di sini" class="w-full min-h-36 rounded-[10px] border-2 text-[#686868] placeholder:[#686868] border-[#F2F2F2] px-5 py-[15px] outline-none focus:border-primary/40">{{ old('pesan') }}</textarea>
                                <label for="kontak-pesan" class="absolute right-5 top-[15px] pointer-events-none text-muted-foreground"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i></label>
                                @error('pesan')<p class="text-xs mt-1 text-red-600 text-left">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="bg-primary text-cream-foreground rounded-full w-full lg:mt-10 mt-5 btn inline-flex items-center justify-center gap-2">
                                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Kirim via WhatsApp
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
