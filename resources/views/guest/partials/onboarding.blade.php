@php use App\Support\GuestFeatures; @endphp
<section class="guest-section">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10 md:mb-14" data-guest-animate="fade-up">
            <span class="guest-badge mb-3">Cara Mulai</span>
            <h2 class="text-3xl sm:text-4xl guest-heading text-[var(--guest-text)]">Live dalam 3 Langkah</h2>
            <p class="mt-3 text-[var(--guest-text-muted)]">Dari setup sekolah hingga orang tua terhubung  tanpa proses rumit.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5" data-guest-stagger>
            @foreach(GuestFeatures::onboardingSteps() as $i => $step)
            @php $blobs = ['var(--guest-blob-pink)', 'var(--guest-blob-yellow)', 'var(--guest-blob-blue)']; @endphp
            <div class="guest-card relative text-center sm:text-left" data-guest-hover data-guest-stagger-item>
                <div class="guest-service-blob mb-4 sm:mx-0" style="background: {{ $blobs[$i] }}; width: 3rem; height: 3rem; font-size: 0.875rem; font-weight: 700; color: var(--guest-sage-dark);">{{ $step['step'] }}</div>
                <h4 class="font-bold text-lg guest-heading">{{ $step['title'] }}</h4>
                <p class="mt-2 text-sm text-[var(--guest-text-muted)] leading-relaxed">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-10" data-guest-animate="fade-up">
            <a href="{{ route('guest.daftar-sekolah') }}" class="guest-btn guest-btn-primary">Daftar Sekolah Sekarang</a>
        </div>
    </div>
</section>
