<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Indikator Matrikulasi</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ showDetailModal: false, detailData: {} }">
        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Daftar Indikator Matrikulasi</h3><p class="section-subtitle">Standar perkembangan yang digunakan sebagai dasar penilaian siswa</p></div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table" data-tour="pg-matrikulasi-table">
                    <thead><tr><th>Aspek / Bidang</th><th>Indikator Perkembangan</th><th>Deskripsi</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($matrikulasis as $m)
                        <tr>
                            <td><span class="badge badge-teal">{{ $m->aspek ?? 'Umum' }}</span></td>
                            <td><span class="font-semibold" style="color:#2C2C2C;">{{ $m->indicator }}</span></td>
                            <td class="max-w-xs truncate">{{ $m->description ?? '-' }}</td>
                            <td class="text-right">
                                <button type="button" @click="detailData = @js($m); showDetailModal = true" class="row-action-icon" style="color:#1A6B6B;background:#F0F7F7;border:1px solid #D0E8E8;" title="Detail" aria-label="Detail"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada indikator matrikulasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$matrikulasis" />
                {{ $matrikulasis->links() }}
            </div>
        </div>

        {{-- DETAIL MODAL --}}
        <div x-show="showDetailModal" class="modal-overlay" style="display:none;" x-transition>
            <div class="modal-box max-w-2xl" @click.away="showDetailModal=false">
                <div class="modal-header flex items-center justify-between">
                    <h3 class="section-title">Detail Indikator Matrikulasi</h3>
                    <button @click="showDetailModal=false" class="text-gray-400 hover:text-gray-600"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <div class="modal-body space-y-5 pt-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Aspek / Bidang</p><p class="text-sm font-semibold text-gray-900" x-text="detailData.aspek || 'Umum'"></p></div>
                        <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Indikator</p><p class="text-sm font-semibold text-gray-900" x-text="detailData.indicator"></p></div>
                    </div>
                    <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tujuan Pembelajaran</p><p class="text-sm text-gray-700 whitespace-pre-line" x-text="detailData.tujuan || '-'"></p></div>
                    <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Strategi / Metode Edukasi</p><p class="text-sm text-gray-700 whitespace-pre-line" x-text="detailData.strategi || '-'"></p></div>
                    <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Deskripsi Lengkap</p><p class="text-sm text-gray-700 whitespace-pre-line" x-text="detailData.description"></p></div>
                </div>
                <div class="modal-footer"><button @click="showDetailModal=false" class="btn-secondary w-full sm:w-auto">Tutup</button></div>
            </div>
        </div>
    </div>
</x-app-layout>
