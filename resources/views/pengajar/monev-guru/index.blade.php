<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Monev Saya</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Riwayat evaluasi kinerja</h3>
                <p class="section-subtitle">Hasil penilaian dari admin sekolah yang sudah difinalisasi.</p>
            </div>

            <form method="get" action="{{ route('pengajar.monev-guru.index') }}" data-tour="pengajar-monev-guru-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full" placeholder="Judul evaluasi atau evaluator…">
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[10.5rem]">
                    <label class="input-label">Periode mulai ≥</label>
                    <input type="date" name="periode_mulai" value="{{ request('periode_mulai') }}" class="input-field w-full">
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[10.5rem]">
                    <label class="input-label">Periode selesai ≤</label>
                    <input type="date" name="periode_selesai" value="{{ request('periode_selesai') }}" class="input-field w-full">
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Filter" variant="secondary" />
                    <x-filter-reset :href="route('pengajar.monev-guru.index')" compact />
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table" data-tour="pengajar-monev-guru-table">
                    <thead>
                        <tr>
                            <th>Judul / Periode</th>
                            <th>Evaluator</th>
                            <th class="text-center">Skor</th>
                            <th>Dipublikasikan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($evaluasis as $e)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $e->judul ?: 'Evaluasi Guru' }}</div>
                                    <div class="text-xs mt-0.5" style="color:#9E9790;">{{ $e->periodeLabel() }}</div>
                                </td>
                                <td class="text-sm">{{ $e->evaluator->name ?? '—' }}</td>
                                <td class="text-center">
                                    @if($e->skor_keseluruhan !== null)
                                        <span class="badge badge-teal tabular-nums">{{ $e->skor_keseluruhan }}</span>
                                    @else
                                        <span style="color:#9E9790;">—</span>
                                    @endif
                                </td>
                                <td class="text-sm whitespace-nowrap" style="color:#6B6560;">
                                    @if($e->finalized_at)
                                        {{ $e->finalized_at->translatedFormat('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-right">
                                    <x-table.action-icon :href="route('pengajar.monev-guru.show', $e)" label="Detail" variant="detail"
                                        @if($loop->first) data-tour="pengajar-monev-guru-action-detail" @endif />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 md:py-12 text-center text-sm" style="color:#9E9790;">Belum ada evaluasi yang dipublikasikan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 md:px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$evaluasis" />
                {{ $evaluasis->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
