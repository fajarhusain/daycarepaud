@php $cms = $cms ?? \App\Support\GuestCms::data(); @endphp
<x-guest-layout max-width="max-w-xl">
    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wide uppercase">
            <span class="material-symbols-outlined text-[15px]">corporate_fare</span>
            Pendaftaran Yayasan / Lembaga
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-text-primary tracking-tight mt-2.5">
            {{ $cms['page_daftar_lembaga_h1'] }}
        </h1>
        <p class="text-sm text-text-secondary mt-1.5 leading-relaxed">
            {{ $cms['page_daftar_lembaga_intro'] }}
        </p>
    </div>

    @if($errors->any())
        <div class="auth-callout auth-callout--danger mb-6" role="alert">
            <span class="auth-callout__icon">
                <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
            </span>
            <ul class="list-disc pl-4 space-y-1 text-xs sm:text-sm">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('guest.daftar-lembaga.store') }}" class="space-y-6">
        @csrf

        <!-- SECTION 1: DATA LEMBAGA -->
        <div class="space-y-4 pt-1">
            <div class="flex items-center gap-2.5 mb-2">
                <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">1</span>
                <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Data Lembaga / Yayasan</span>
            </div>
            <div>
                <x-input-label for="lembaga_name" value="Nama Lembaga / Yayasan *" />
                <x-text-input id="lembaga_name" name="lembaga_name" type="text" :value="old('lembaga_name')" required placeholder="Contoh: Yayasan Pendidikan Harapan Bangsa" />
            </div>
            <div>
                <x-input-label for="lembaga_address" value="Alamat Kantor Lembaga" />
                <textarea id="lembaga_address" name="lembaga_address" rows="2" class="input-field" placeholder="Alamat kantor sekretariat">{{ old('lembaga_address') }}</textarea>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="lembaga_phone" value="Telepon Lembaga" />
                    <x-text-input id="lembaga_phone" name="lembaga_phone" type="text" :value="old('lembaga_phone')" placeholder="08xx atau (021) xxx" />
                </div>
                <div>
                    <x-input-label for="organisasi" value="Afiliasi / Organisasi (Opsional)" />
                    <x-text-input id="organisasi" name="organisasi" type="text" :value="old('organisasi')" placeholder="Mis. IGTKI, HIMPAUDI" />
                </div>
            </div>
        </div>

        <!-- SECTION 2: CABANG PERTAMA -->
        <div class="space-y-4 pt-4 border-t border-border-subtle/80">
            <div class="flex items-center gap-2.5 mb-2">
                <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">2</span>
                <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Sekolah / Cabang Pertama</span>
            </div>
            <div>
                <x-input-label for="sekolah_name" value="Nama Unit Sekolah *" />
                <x-text-input id="sekolah_name" name="sekolah_name" type="text" :value="old('sekolah_name')" required placeholder="Contoh: Daycare & PAUD Tunas Ceria" />
            </div>
            <div>
                <x-input-label for="sekolah_address" value="Alamat Lokasi Sekolah" />
                <textarea id="sekolah_address" name="sekolah_address" rows="2" class="input-field" placeholder="Alamat lokasi sekolah / cabang">{{ old('sekolah_address') }}</textarea>
            </div>
            <div>
                <x-input-label for="sekolah_phone" value="Telepon Sekolah" />
                <x-text-input id="sekolah_phone" name="sekolah_phone" type="text" :value="old('sekolah_phone')" placeholder="Telepon atau kontak sekolah" />
            </div>
        </div>

        <!-- SECTION 3: ADMIN LEMBAGA -->
        <div class="space-y-4 pt-4 border-t border-border-subtle/80">
            <div class="flex items-center gap-2.5 mb-2">
                <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">3</span>
                <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Kontak Admin Lembaga</span>
            </div>
            <div>
                <x-input-label for="contact_name" value="Nama Penanggung Jawab *" />
                <x-text-input id="contact_name" name="contact_name" type="text" :value="old('contact_name')" required placeholder="Nama lengkap pimpinan / PIC" />
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="contact_email" value="Email Login *" />
                    <x-text-input id="contact_email" name="contact_email" type="email" :value="old('contact_email')" required placeholder="admin@yayasan.org" />
                </div>
                <div>
                    <x-input-label for="contact_phone" value="Telepon / WhatsApp" />
                    <x-text-input id="contact_phone" name="contact_phone" type="text" :value="old('contact_phone')" placeholder="08xxxxxxxxxx" />
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="password" value="Kata Sandi *" />
                    <x-text-input id="password" name="password" type="password" required autocomplete="new-password" placeholder="Min. 8 karakter" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Ulangi Kata Sandi *" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
                </div>
            </div>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row gap-3">
            <x-ascent-button>
                <span class="material-symbols-outlined text-[19px]">domain_add</span>
                <span>Kirim Pendaftaran Lembaga</span>
            </x-ascent-button>
        </div>

        <div class="text-center pt-2">
            <a href="{{ route('guest.beranda') }}" class="text-xs sm:text-sm font-semibold text-text-secondary hover:text-forest-deep transition-colors inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[15px]">arrow_back</span>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </form>
</x-guest-layout>
