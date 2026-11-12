<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-xl" style="color: #2C2C2C;">Detail Tagihan</h2>
                <p class="text-sm" style="color:#9E9790;">{{ $pembayaran->anak->name }} - {{ $pembayaran->getPeriodeLabel() }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-4 md:py-8 px-3 md:px-4 sm:px-6 lg:px-8"
         x-data="{ showBayarModal: false, showImageModal: false, activeImage: null, activeDownloadUrl: null }"
         @tour-close-modals.window="showBayarModal=false">
        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        @php $totalTambahan = $pembayaran->getTotalBiayaTambahan(); $items = $pembayaran->items; @endphp

        <div class="card card-body-pad mb-6">
            <div class="flex items-center justify-between">
                <h3 class="section-title">Status</h3>
                <span class="badge badge-{{ $pembayaran->status_badge }}">{{ $pembayaran->status_label }}</span>
            </div>
            @if($pembayaran->catatan_admin)
                <div class="mt-3 p-3 rounded-lg" style="background:{{ $pembayaran->isRejected() ? '#FEF2F2' : '#F9FAFB' }};">
                    <p class="text-sm">{{ $pembayaran->catatan_admin }}</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card card-body-pad" data-tour="ortu-pembayaran-rincian">
                <h3 class="section-title mb-4">Rincian Biaya</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b"><span style="color:#9E9790;">Biaya Bulanan</span><span class="font-semibold">{{ $pembayaran->getBiayaPerHariFormatted() }}</span></div>
                    @if($items && $items->count() > 0)
                        <div class="flex justify-between py-2 border-b" style="border-color:rgba(0,0,0,0.06);">
                            <span style="color:#9E9790;">Biaya Lain</span>
                            <span></span>
                        </div>
                        @foreach($items as $item)
                            <div class="flex justify-between pl-4 py-1 text-sm" style="color:#6B6560;">
                                <span>{{ $item->nama_item }}</span>
                                <span>+ Rp {{ number_format($item->jumlah, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    @endif
                    <div class="flex justify-between py-2 border-b"><span style="color:#9E9790;">Hari Hadir</span><span class="font-semibold" style="color:#1A6B6B;">{{ $pembayaran->hari_hadir }} hari</span></div>
                    <div class="flex justify-between py-2 border-b"><span style="color:#9E9790;">Subtotal</span><span class="font-semibold">{{ $pembayaran->getSubtotalFormatted() }}</span></div>
                    @if($pembayaran->diskon)
                        <div class="flex justify-between py-2 border-b"><span style="color:#9E9790;">Diskon ({{ $pembayaran->diskon->nama_diskon }})</span><span class="font-semibold" style="color:#C0392B;">-{{ $pembayaran->getNilaiDiskonFormatted() }}</span></div>
                    @endif
                    <div class="flex justify-between py-3 px-4 rounded-lg" style="background:#D0E8E8;">
                        <span class="font-bold text-lg" style="color:#1A6B6B;">Total Bayar</span>
                        <span class="font-bold text-xl sm:text-2xl break-all sm:break-normal text-right" style="color:#1A6B6B;">{{ $pembayaran->getTotalFormatted() }}</span>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @if($pembayaran->bukti_transfer)
                    <div class="card card-body-pad" data-tour="ortu-pembayaran-bukti">
                        <h3 class="section-title mb-4">Bukti Transfer</h3>
                        <img src="{{ Storage::url($pembayaran->bukti_transfer) }}" alt="Bukti"
                            class="w-full rounded-lg border cursor-pointer"
                            @click="activeImage = '{{ Storage::url($pembayaran->bukti_transfer) }}'; activeDownloadUrl = '{{ route('orangtua.pembayaran.bukti.download', $pembayaran) }}'; showImageModal = true">
                    </div>
                @endif

                @if($pembayaran->isApproved())
                    <a href="{{ route('orangtua.pembayaran.invoice', $pembayaran) }}" class="btn-primary w-full justify-center" target="_blank" rel="noopener">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Download Invoice
                    </a>
                @endif

                @if($pembayaran->isPending() || $pembayaran->isRejected())
                    <button data-tour="ortu-pembayaran-bayar-btn" data-tour-open-modal="bayar" @click="showBayarModal=true" class="btn-primary w-full justify-center">
                        {{ $pembayaran->bukti_transfer ? 'Upload Ulang Bukti' : 'Upload Bukti Transfer' }}
                    </button>
                @endif

                <a href="{{ route('orangtua.pembayaran.index') }}" class="btn-secondary w-full justify-center">Kembali</a>
            </div>
        </div>

        <template x-teleport="body">
        <div x-show="showBayarModal" class="modal-overlay modal-overlay--blur" style="display:none;">
            <div x-show="showBayarModal" x-transition class="modal-box" @click.away="showBayarModal=false">
                <form action="{{ route('orangtua.pembayaran.bayar', $pembayaran) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Upload Bukti Transfer</h3></div>
                    <div class="modal-body space-y-4" data-tour="modal-create-section-form">
                        <p class="text-sm" style="color:#9E9790;">Total: <span class="font-bold text-lg" style="color:#1A6B6B;">{{ $pembayaran->getTotalFormatted() }}</span></p>
                        <div><label class="input-label">Bukti Transfer</label><input type="file" name="bukti_transfer" accept="image/*" required class="input-field"></div>
                        <div><label class="input-label">Catatan</label><textarea name="catatan" rows="2" class="input-field"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showBayarModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Kirim</button></div>
                </form>
            </div>
        </div>
        </template>

        <x-image-lightbox />
    </div>
</x-app-layout>
