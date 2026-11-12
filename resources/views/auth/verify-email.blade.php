<x-guest-layout>
    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wide uppercase">
            <span class="material-symbols-outlined text-[15px]">mark_email_read</span>
            Verifikasi Email
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-text-primary tracking-tight mt-2.5">
            Konfirmasi alamat email
        </h1>
        <p class="text-sm text-text-secondary mt-1.5 leading-relaxed">
            Terima kasih telah mendaftar. Silakan periksa inbox email Anda dan klik tautan verifikasi. Jika belum menerima, kami dapat mengirimkan ulang.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert-success mb-5">
            <span class="material-symbols-outlined text-[20px] text-forest-deep shrink-0">check_circle</span>
            <span class="text-xs sm:text-sm">Tautan verifikasi baru telah berhasil dikirim ke email Anda.</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between pt-2">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <x-ascent-button>
                <span class="material-symbols-outlined text-[19px]">forward_to_inbox</span>
                <span>Kirim Ulang Email</span>
            </x-ascent-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full sm:w-auto text-sm font-semibold text-text-secondary hover:text-forest-deep py-3 px-6 rounded-full border border-border-subtle bg-white hover:bg-surface-mint transition-colors">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
