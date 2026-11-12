<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">{{ $evaluasi->judul ?: 'Evaluasi Guru' }}</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <div class="card overflow-hidden mb-6">
            <div class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-actions w-full sm:w-auto sm:ml-0">
                    <a href="{{ route('pengajar.monev-guru.index') }}"
                        class="btn-secondary toolbar-icon shrink-0 inline-flex items-center justify-center !p-0 h-9 w-9 md:h-11 md:w-11"
                        title="Kembali" aria-label="Kembali">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                </div>
            </div>

            <div class="p-4 md:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold" style="color:#2C2C2C;">{{ $evaluasi->judul ?: 'Evaluasi Guru' }}</h3>
                        <p class="text-sm mt-1" style="color:#9E9790;">Periode: {{ $evaluasi->periodeLabel() }}</p>
                        <p class="text-sm" style="color:#9E9790;">Evaluator: {{ $evaluasi->evaluator->name ?? '—' }}</p>
                        @if($evaluasi->finalized_at)
                            <p class="text-sm" style="color:#9E9790;">Dipublikasikan: {{ $evaluasi->finalized_at->translatedFormat('d F Y') }}</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <span class="badge badge-green">Final</span>
                        @if($evaluasi->skor_keseluruhan !== null)
                            <div class="mt-2 text-3xl font-bold tabular-nums" style="color:#1A6B6B;">{{ $evaluasi->skor_keseluruhan }}</div>
                            <div class="text-xs" style="color:#9E9790;">Skor keseluruhan</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden mb-6">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Penilaian per kriteria</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kriteria</th>
                            <th class="text-center">Bobot</th>
                            <th class="text-center">Skor</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluasi->items->filter(fn ($i) => $i->kriteria?->is_active)->sortBy(fn ($i) => $i->kriteria?->urutan ?? 999) as $item)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $item->kriteria->nama ?? '—' }}</div>
                                    @if($item->kriteria?->deskripsi)
                                        <div class="text-xs mt-0.5" style="color:#9E9790;">{{ $item->kriteria->deskripsi }}</div>
                                    @endif
                                </td>
                                <td class="text-center tabular-nums">{{ $item->kriteria->bobot ?? '—' }}%</td>
                                <td class="text-center">
                                    @if($item->skor !== null)
                                        <span class="badge badge-teal tabular-nums">{{ $item->skor }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-sm">{{ $item->catatan ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($evaluasi->catatan_umum || $evaluasi->rekomendasi)
            <div class="card p-4 md:p-6 space-y-4">
                @if($evaluasi->catatan_umum)
                    <div>
                        <h4 class="section-title text-base mb-2">Catatan umum</h4>
                        <p class="text-sm whitespace-pre-line" style="color:#555;">{{ $evaluasi->catatan_umum }}</p>
                    </div>
                @endif
                @if($evaluasi->rekomendasi)
                    <div>
                        <h4 class="section-title text-base mb-2">Rekomendasi tindak lanjut</h4>
                        <p class="text-sm whitespace-pre-line" style="color:#555;">{{ $evaluasi->rekomendasi }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
