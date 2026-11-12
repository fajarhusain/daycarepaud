<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Detail Jurnal</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{ showDeleteModal: false }"
         @tour-close-modals.window="showDeleteModal = false">
        <div class="card mb-6">
            <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">{{ $jurnal->no_jurnal }}</h3>
                    <p class="section-subtitle">{{ $jurnal->deskripsi }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.jurnal.index') }}" class="btn-secondary text-xs">Kembali</a>
                    <button type="button" @click="showDeleteModal = true" class="btn-danger text-xs">Hapus</button>
                </div>
            </div>
            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold" style="color:#9E9790;">Tanggal</p>
                    <p class="font-medium" style="color:#2C2C2C;">{{ $jurnal->tanggal->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold" style="color:#9E9790;">Sumber</p>
                    <p><span class="badge text-xs {{ $jurnal->source === 'manual' ? 'badge-green' : '' }}" style="background: {{ $jurnal->source === 'manual' ? '#D0E8E8' : '#E0D6C8' }}; color: {{ $jurnal->source === 'manual' ? '#1A6B6B' : '#6B5B3A' }};">{{ $jurnal->source === 'manual' ? 'Manual' : 'Auto' }}</span></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold" style="color:#9E9790;">Dibuat Oleh</p>
                    <p class="font-medium" style="color:#2C2C2C;">{{ $jurnal->createdBy->name ?? 'Sistem' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider font-semibold" style="color:#9E9790;">Tanggal Dibuat</p>
                    <p class="font-medium" style="color:#2C2C2C;">{{ $jurnal->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Detail Jurnal</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th class="text-right">Debit (Rp)</th>
                            <th class="text-right">Kredit (Rp)</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jurnal->lines as $line)
                            <tr>
                                <td class="font-mono text-sm font-semibold" style="color: #1A6B6B;">{{ $line->akun->kode ?? '-' }}</td>
                                <td class="font-medium" style="color: #2C2C2C;">{{ $line->akun->nama ?? '-' }}</td>
                                <td class="text-right font-semibold {{ $line->debit > 0 ? 'text-green-700' : '' }}">
                                    {{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '' }}
                                </td>
                                <td class="text-right font-semibold {{ $line->kredit > 0 ? 'text-red-700' : '' }}">
                                    {{ $line->kredit > 0 ? number_format($line->kredit, 0, ',', '.') : '' }}
                                </td>
                                <td style="color: #9E9790;">{{ $line->keterangan ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#F5F2ED;">
                            <td colspan="2" class="font-bold text-sm" style="color:#2C2C2C;">Total</td>
                            <td class="text-right font-bold text-sm" style="color:#1A6B6B;">
                                Rp {{ number_format($jurnal->lines->sum('debit'), 0, ',', '.') }}
                            </td>
                            <td class="text-right font-bold text-sm" style="color:#C0392B;">
                                Rp {{ number_format($jurnal->lines->sum('kredit'), 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <x-confirm-modal
            show="showDeleteModal"
            :action="route('admin.jurnal.destroy', $jurnal)"
            method="DELETE"
            title="Hapus Jurnal?"
            message="Jurnal dan entri terkait akan dihapus permanen. Cashflow terkait juga akan terlepas."
        />
    </div>
</x-app-layout>
