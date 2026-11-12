<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">RKAS</h2>
        </div>
    </x-slot>

    <div class="app-page-shell" x-data="{ showCreateModal: false }">

        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card overflow-hidden mb-6">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Daftar RKAS</h3>
                <p class="section-subtitle">Rencana keuangan per tahun ajaran dan semester.</p>
            </div>

            <form method="GET" data-tour="admin-rkas-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[10rem] md:max-w-[14rem]">
                    <label class="input-label">Tahun Ajaran</label>
                    <select name="tahun_ajaran" class="input-field w-full">
                        @foreach($tahunOptions as $ta)
                            <option value="{{ $ta }}" @selected($tahunAjaran === $ta)>{{ $ta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-field md:min-w-[8rem] md:max-w-[10rem]">
                    <label class="input-label">Semester</label>
                    <select name="semester" class="input-field w-full">
                        <option value="" @selected(!request('semester'))>Semua</option>
                        <option value="1" @selected((string) request('semester') === '1')>Semester 1</option>
                        <option value="2" @selected((string) request('semester') === '2')>Semester 2</option>
                    </select>
                </div>
                <div class="toolbar-field md:min-w-[8rem] md:max-w-[10rem]">
                    <label class="input-label">Status</label>
                    <select name="status" class="input-field w-full">
                        <option value="" @selected(!request('status'))>Semua</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="final" @selected(request('status') === 'final')>Final</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Filter" variant="secondary" />
                    <x-filter-reset :href="route('admin.rkas.index')" compact />
                    <x-export-excel route="admin.rkas.export" :icon-only="true" />
                    <a href="{{ route('admin.rkas.laporan', ['tahun_ajaran' => $tahunAjaran]) }}"
                        class="btn-secondary toolbar-icon shrink-0 inline-flex items-center justify-center !p-0 h-9 w-9 md:h-11 md:w-11"
                        title="Laporan Komparasi" aria-label="Laporan Komparasi">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </a>
                    <x-toolbar.add-icon label="Buat RKAS" type="button" @click="showCreateModal=true" />
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table" data-tour="admin-rkas-table">
                    <thead><tr><th>Periode</th><th>Status</th><th class="text-center">Baris</th><th>Sync Terakhir</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($rkasList as $rkas)
                            <tr>
                                <td class="font-medium">{{ \App\Support\TahunAjaran::label($rkas->tahun_ajaran, $rkas->semester) }}</td>
                                <td>
                                    @if($rkas->status === 'final')
                                        <span class="badge badge-green">Final</span>
                                    @else
                                        <span class="badge badge-teal">Draft</span>
                                    @endif
                                </td>
                                <td class="text-center tabular-nums">{{ $rkas->lines_count }}</td>
                                <td class="text-xs" style="color:#9E9790;">{{ $rkas->synced_at?->format('d M Y H:i') ?? '—' }}</td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <x-table.action-icon :href="route('admin.rkas.edit', $rkas)" label="Edit" variant="edit" />
                                        <form action="{{ route('admin.rkas.sync', $rkas) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="row-action-icon" style="color:#145252;background:#B8DEDE;" title="Sync" aria-label="Sync">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.rkas.laporan', ['tahun_ajaran' => $rkas->tahun_ajaran, 'semester' => $rkas->semester]) }}" class="row-action-icon" style="color:#6B5B3E;background:#FFF8E7;" title="Laporan" aria-label="Laporan">
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-12 text-center" style="color:#9E9790;">Belum ada RKAS untuk filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('admin.rkas.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Buat RKAS Baru</h3></div>
                    <div class="modal-body grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="input-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran" class="input-field">
                                @foreach($tahunOptions as $ta)
                                    <option value="{{ $ta }}" @selected($tahunAjaran === $ta)>{{ $ta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="input-label">Semester</label>
                            <select name="semester" class="input-field">
                                <option value="1">Semester 1 (Jul – Des)</option>
                                <option value="2">Semester 2 (Jan – Jun)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Buat</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
