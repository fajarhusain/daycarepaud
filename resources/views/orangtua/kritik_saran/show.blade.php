<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3" data-tour="page-header">
                <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                </div>
                <h2 class="font-bold text-xl" style="color: #2C2C2C;">Detail masukan</h2>
            </div>
            <a href="{{ route('orangtua.kritik-saran.index') }}" class="btn-secondary text-sm">← Kembali</a>
        </div>
    </x-slot>

    <div class="py-4 md:py-8 px-3 md:px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto space-y-6" x-data="{ showImageModal: false, activeImage: '', activeDownloadUrl: null }">
        <div class="card overflow-hidden" data-tour="kritik-message">
            <div class="card-pad border-b page-toolbar" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide" style="color:#9E9790;">{{ $kritik_saran->created_at->translatedFormat('d F Y, H:i') }}</p>
                    <p class="text-sm mt-1" style="color:#6B6560;">
                        @if($kritik_saran->sekolah)
                            Ke: <strong style="color:#2C2C2C;">{{ $kritik_saran->sekolah->name }}</strong>
                        @endif
                    </p>
                </div>
                <span class="badge badge-teal">{{ $kritik_saran->status ?? '' }}</span>
            </div>
            <div class="card-body-pad">
                <div class="flex flex-col sm:flex-row gap-5">
                    @if($kritik_saran->photo)
                        <div class="w-full sm:w-48 shrink-0 rounded-2xl overflow-hidden border border-gray-100 shadow-sm cursor-pointer" @click="activeImage = '{{ Storage::url($kritik_saran->photo) }}'; activeDownloadUrl = '{{ route('orangtua.kritik-saran.photo.download', $kritik_saran) }}'; showImageModal = true">
                            <img src="{{ Storage::url($kritik_saran->photo) }}" class="w-full h-48 sm:h-32 object-cover hover:scale-105 transition duration-300">
                        </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-xs font-bold uppercase tracking-wide mb-2" style="color:#6B6560;">Pesan Anda</h3>
                        <p class="text-sm leading-relaxed whitespace-pre-wrap" style="color:#2C2C2C;">{{ $kritik_saran->message }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden border-2" style="border-color:#1A6B6B; background: linear-gradient(180deg, #F0FAFA 0%, #fff 48%);" data-tour="kritik-response">
            <div class="card-pad border-b" style="border-color:rgba(26,107,107,0.15); background:rgba(26,107,107,0.06);">
                <h3 class="section-title flex items-center gap-2">
                    <svg class="h-5 w-5 shrink-0" style="color:#1A6B6B;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    Tanggapan sekolah
                </h3>
                <p class="section-subtitle mt-1">Umpan balik dari admin sekolah untuk masukan Anda.</p>
            </div>
            <div class="card-body-pad">
                @if(filled($kritik_saran->umpan_balik))
                    <p class="text-sm leading-relaxed whitespace-pre-wrap" style="color:#2C2C2C;">{{ $kritik_saran->umpan_balik }}</p>
                @else
                    <p class="text-sm italic" style="color:#9E9790;">Belum ada tanggapan. Tim sekolah akan membalas setelah meninjau pesan Anda.</p>
                @endif
            </div>
        </div>

        <x-image-lightbox />
    </div>
</x-app-layout>
