<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Monev Guru</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Evaluasi kinerja guru</h3>
                <p class="section-subtitle">Penilaian manual berdasarkan rubrik kriteria per periode.</p>
            </div>

            <form method="get" data-tour="admin-monev-guru-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full" placeholder="Judul evaluasi atau nama guru…">
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Guru</label>
                    <select name="pengajar_id" class="input-field w-full">
                        <option value="">Semua</option>
                        @foreach($pengajars as $p)
                            <option value="{{ $p->id }}" @selected(request('pengajar_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-field md:min-w-[7rem] md:max-w-[9rem]">
                    <label class="input-label">Status</label>
                    <select name="status" class="input-field w-full">
                        <option value="">Semua</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="final" @selected(request('status') === 'final')>Final</option>
                    </select>
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
                    <x-filter-reset :href="route('admin.monev-guru.index')" compact />
                    <x-export-excel route="admin.monev-guru.export" :icon-only="true" />
                    <a href="{{ route('admin.monev-guru.kriteria.index') }}"
                        class="btn-secondary toolbar-icon shrink-0 inline-flex items-center justify-center !p-0 h-9 w-9 md:h-11 md:w-11"
                        title="Kelola Kriteria" aria-label="Kelola Kriteria">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </a>
                    <x-toolbar.add-icon href="{{ route('admin.monev-guru.create') }}" label="Buat Evaluasi" />
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table" data-tour="admin-monev-guru-table">
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>Judul / Periode</th>
                            <th class="text-center">Skor</th>
                            <th>Status</th>
                            <th>Evaluator</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($evaluasis as $e)
                            <tr>
                                <td class="font-semibold">{{ $e->pengajar->name ?? '—' }}</td>
                                <td>
                                    <div class="font-medium">{{ $e->judul ?: 'Evaluasi Guru' }}</div>
                                    <div class="text-xs mt-0.5" style="color:#9E9790;">{{ $e->periodeLabel() }}</div>
                                </td>
                                <td class="text-center">
                                    @if($e->skor_keseluruhan !== null)
                                        <span class="badge badge-teal tabular-nums">{{ $e->skor_keseluruhan }}</span>
                                    @else
                                        <span style="color:#9E9790;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($e->isFinal())
                                        <span class="badge badge-green">Final</span>
                                    @else
                                        <span class="badge badge-amber">Draft</span>
                                    @endif
                                </td>
                                <td class="text-sm">{{ $e->evaluator->name ?? '—' }}</td>
                                <td class="text-right">
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        <x-table.action-icon :href="route('admin.monev-guru.show', $e)" label="Detail" variant="detail" />
                                        @if($e->isDraft())
                                            <x-table.action-icon :href="route('admin.monev-guru.edit', $e)" label="Edit" variant="edit" />
                                            <form action="{{ route('admin.monev-guru.destroy', $e) }}" method="POST" class="inline" onsubmit="return confirm('Hapus draft evaluasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-table.action-icon type="submit" label="Hapus" variant="delete" />
                                            </form>
                                        @else
                                            <a href="{{ route('admin.monev-guru.pdf', $e) }}" class="row-action-icon" style="color:#6B5B3E;background:#FFF8E7;" title="PDF" aria-label="PDF">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-12 text-center text-sm" style="color:#9E9790;">Belum ada evaluasi guru.</td></tr>
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
