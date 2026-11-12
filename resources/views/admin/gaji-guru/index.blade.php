<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402-2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Gaji Guru</h2>
        </div>
    </x-slot>
    <div class="app-page-shell">
        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div class="card p-5">
                <p class="text-sm" style="color:#6B6560;">Total gaji periode {{ sprintf('%02d/%d', $bulan, $tahun) }}</p>
                <p class="text-2xl font-bold mt-1" style="color:#1A6B6B;">Rp {{ number_format($totalBulan, 0, ',', '.') }}</p>
            </div>
            <div class="card p-5">
                <p class="text-sm" style="color:#6B6560;">Jumlah slip (guru) periode ini</p>
                <p class="text-2xl font-bold mt-1" style="color:#2C2C2C;">{{ $jumlahGuru }}</p>
            </div>
        </div>

        <div class="card p-4 mb-6 flex flex-col lg:flex-row lg:flex-wrap lg:items-end gap-4 justify-between">
            <form method="GET" class="flex flex-wrap gap-3 items-end flex-1 min-w-0">
                <div><label class="input-label text-xs">Bulan</label>
                    <select name="bulan" class="input-field">
                        <option value="">Semua</option>
                        @for($m=1;$m<=12;$m++)<option value="{{ $m }}" @selected(request('bulan')==$m)>{{ sprintf('%02d', $m) }}</option>@endfor
                    </select>
                </div>
                <div><label class="input-label text-xs">Tahun</label>
                    <input type="number" name="tahun" class="input-field w-28" value="{{ request('tahun', $tahun) }}">
                </div>
                <div><label class="input-label text-xs">Guru</label>
                    <select name="pengajar_id" class="input-field">
                        <option value="">Semua</option>
                        @foreach($pengajars as $p)<option value="{{ $p->id }}" @selected(request('pengajar_id')==$p->id)>{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="input-label text-xs">Status</label>
                    <select name="status" class="input-field">
                        <option value="">Semua</option>
                        <option value="draft" @selected(request('status')==='draft')>Draft</option>
                        <option value="approved" @selected(request('status')==='approved')>Disetujui</option>
                    </select>
                </div>
                <x-toolbar.filter-icon />
            </form>
            <div class="flex flex-wrap gap-2 shrink-0">
                <x-icon-btn href="{{ route('admin.gaji-guru.komponen.index') }}" label="Master Komponen" variant="secondary"
                    icon="M4 6h16M4 10h16M4 14h16M4 18h16" />
                <x-export-excel route="admin.gaji-guru.export" />
                <x-icon-btn href="{{ route('admin.gaji-guru.create') }}" label="Slip Gaji" variant="primary"
                    icon="M12 4v16m8-8H4" />
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Guru</th><th>Periode</th><th>Total</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($gajis as $g)
                        <tr>
                            <td class="font-semibold">{{ $g->pengajar->name ?? '—' }}</td>
                            <td>{{ $g->periodeLabel() }}</td>
                            <td>{{ $g->getTotalFormatted() }}</td>
                            <td>@if($g->isApproved())<span class="badge badge-teal">Disetujui</span>@else<span class="badge" style="background:#eee;color:#666;">Draft</span>@endif</td>
                            <td class="text-right"><a href="{{ route('admin.gaji-guru.show', $g) }}" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Detail" aria-label="Detail"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-12 text-center" style="color:#9E9790;">Belum ada slip gaji.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$gajis" />
                {{ $gajis->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
