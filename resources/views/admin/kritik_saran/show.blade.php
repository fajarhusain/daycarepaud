<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Detail Kritik &amp; Saran</h2>
        </div>
    </x-slot>

    <div class="app-page-shell space-y-6" x-data="{ showImageModal: false, activeImage: '', activeDownloadUrl: null }">
        <div class="mb-2"><a href="{{ route('admin.kritik-saran.index') }}" class="btn-secondary text-sm">← Kritik &amp; Saran</a></div>
        @if(session('success'))
            <div class="alert-success">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Konteks laporan</h3>
                <p class="section-subtitle mt-1">Sekolah dan kelas anak terdaftar pada akun orang tua pengirim.</p>
            </div>
            <dl class="px-6 py-5 space-y-3 text-sm">
                <div class="flex flex-wrap gap-2">
                    <dt class="font-semibold w-44 shrink-0" style="color:#6B6560;">Sekolah tujuan</dt>
                    <dd style="color:#2C2C2C;">{{ $kritik_saran->sekolah?->name ?? '' }}</dd>
                </div>
                <div class="flex flex-wrap gap-2">
                    <dt class="font-semibold w-44 shrink-0 align-top" style="color:#6B6560;">Kelas &amp; anak</dt>
                    <dd class="min-w-0 flex-1">
                        @if($kritik_saran->user?->anaks?->isNotEmpty())
                            <ul class="space-y-2">
                                @foreach($kritik_saran->user->anaks as $anak)
                                    <li class="rounded-lg px-3 py-2 text-sm" style="background:#FAF6F0;">
                                        <span class="font-semibold" style="color:#2C2C2C;">{{ $anak->name }}</span>
                                        <span class="text-xs block mt-0.5" style="color:#6B6560;">
                                            Kelas: <strong style="color:#1A6B6B;">{{ $anak->kelas?->name ?? 'Belum ditetapkan' }}</strong>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color:#9E9790;">Tidak ada data anak pada akun ini atau pengirim tanpa akun terhubung.</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Informasi pengirim</h3>
            </div>
            <dl class="px-6 py-5 space-y-3 text-sm">
                <div class="flex flex-wrap gap-2">
                    <dt class="font-semibold w-40 shrink-0" style="color:#6B6560;">Tanggal kirim</dt>
                    <dd style="color:#2C2C2C;">{{ $kritik_saran->created_at->translatedFormat('d F Y, H:i') }}</dd>
                </div>
                <div class="flex flex-wrap gap-2">
                    <dt class="font-semibold w-40 shrink-0" style="color:#6B6560;">Nama (akun)</dt>
                    <dd style="color:#2C2C2C;">{{ $kritik_saran->user?->name ?? '' }}</dd>
                </div>
                <div class="flex flex-wrap gap-2">
                    <dt class="font-semibold w-40 shrink-0" style="color:#6B6560;">Email</dt>
                    <dd style="color:#2C2C2C;">{{ $kritik_saran->user?->email ?? '' }}</dd>
                </div>
                @if($kritik_saran->nama_bapak || $kritik_saran->nik_bapak || $kritik_saran->nama_anak)
                    <div class="pt-2 border-t" style="border-color:rgba(0,0,0,0.06);"></div>
                    @if($kritik_saran->nama_bapak)
                        <div class="flex flex-wrap gap-2">
                            <dt class="font-semibold w-40 shrink-0" style="color:#6B6560;">Nama orang tua (data)</dt>
                            <dd style="color:#2C2C2C;">{{ $kritik_saran->nama_bapak }}</dd>
                        </div>
                    @endif
                    @if($kritik_saran->nik_bapak)
                        <div class="flex flex-wrap gap-2">
                            <dt class="font-semibold w-40 shrink-0" style="color:#6B6560;">NIK</dt>
                            <dd style="color:#2C2C2C;">{{ $kritik_saran->nik_bapak }}</dd>
                        </div>
                    @endif
                    @if($kritik_saran->nama_anak)
                        <div class="flex flex-wrap gap-2">
                            <dt class="font-semibold w-40 shrink-0" style="color:#6B6560;">Nama anak</dt>
                            <dd style="color:#2C2C2C;">{{ $kritik_saran->nama_anak }}</dd>
                        </div>
                    @endif
                @endif
            </dl>
        </div>

        <div class="card overflow-hidden" data-tour="kritik-message">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Isi pesan</h3>
            </div>
            <div class="px-6 py-5">
                <div class="flex flex-col sm:flex-row gap-5">
                    @if($kritik_saran->photo)
                        <div class="w-full sm:w-48 shrink-0 rounded-2xl overflow-hidden border border-gray-100 shadow-sm cursor-pointer" @click="activeImage = '{{ Storage::url($kritik_saran->photo) }}'; activeDownloadUrl = '{{ route('admin.kritik-saran.photo.download', $kritik_saran) }}'; showImageModal = true">
                            <img src="{{ Storage::url($kritik_saran->photo) }}" class="w-full h-48 sm:h-32 object-cover hover:scale-105 transition duration-300" alt="Lampiran foto">
                        </div>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-xs font-bold uppercase tracking-wide mb-2" style="color:#6B6560;">Pesan orang tua</h3>
                        <p class="text-sm leading-relaxed whitespace-pre-wrap" style="color:#2C2C2C;">{{ $kritik_saran->message }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(filled($kritik_saran->umpan_balik))
            <div class="card overflow-hidden" style="background:#FAF6F0;">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Tanggapan sekolah (tersimpan)</h3>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap" style="color:#2C2C2C;">{{ $kritik_saran->umpan_balik }}</p>
                </div>
            </div>
        @endif

        <div class="card overflow-hidden" data-tour="kritik-response">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Update status &amp; tanggapan</h3>
                <p class="section-subtitle mt-1">Orang tua dapat melihat status di menu Saran &amp; Kritik mereka.</p>
            </div>
            <form method="post" action="{{ route('admin.kritik-saran.update', $kritik_saran) }}" class="px-6 py-5 space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="input-label">Status</label>
                    @php
                        $statusPilihan = ['pending', 'Terkirim', 'Dibaca', 'Diproses', 'Ditanggapi', 'Selesai'];
                        $statusSaatIni = old('status', $kritik_saran->status);
                        if (! in_array($statusSaatIni, $statusPilihan, true)) {
                            $statusPilihan[] = $statusSaatIni;
                        }
                    @endphp
                    <select name="status" class="input-field" required>
                        @foreach ($statusPilihan as $st)
                            <option value="{{ $st }}" @selected($statusSaatIni === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-xs mt-1" style="color:#C0392B;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="input-label">Tanggapan untuk orang tua (opsional)</label>
                    <textarea name="umpan_balik" rows="5" class="input-field" placeholder="Contoh: Terima kasih atas masukannya, kami akan...">{{ old('umpan_balik', $kritik_saran->umpan_balik) }}</textarea>
                    @error('umpan_balik')
                        <p class="text-xs mt-1" style="color:#C0392B;">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('admin.kritik-saran.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>

        <x-image-lightbox />
    </div>
</x-app-layout>
