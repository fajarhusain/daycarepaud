<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Slip Gaji — {{ $gaji->pengajar->name ?? '' }}</h2>
        </div>
    </x-slot>
    <div class="app-page-shell space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.gaji-guru.index') }}" class="btn-secondary text-sm">← Gaji Guru</a>
        </div>
        @if(session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card p-6 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold">Periode {{ $gaji->periodeLabel() }}</span>
                @if($gaji->isApproved())<span class="badge badge-teal">Disetujui</span>@else<span class="badge" style="background:#eee;color:#666;">Draft</span>@endif
            </div>
            @if($gaji->isApproved() && $gaji->approved_at)
                <p class="text-sm" style="color:#6B6560;">Disetujui {{ $gaji->approved_at->format('d/m/Y H:i') }} oleh {{ $gaji->approvedBy->name ?? '—' }}</p>
            @endif
            <p class="text-sm">Kas: {{ $gaji->akunKas->kode ?? '' }} — {{ $gaji->akunKas->nama ?? '' }}</p>
            <p class="text-sm">Beban: {{ $gaji->akunBeban->kode ?? '' }} — {{ $gaji->akunBeban->nama ?? '' }}</p>
            @if($gaji->catatan)<p class="text-sm mt-2">{{ $gaji->catatan }}</p>@endif
        </div>

        <div class="card overflow-hidden">
            <table class="data-table">
                <thead><tr><th>Komponen</th><th>Jenis</th><th class="text-right">Nominal</th></tr></thead>
                <tbody>
                    @foreach($gaji->komponen ?? [] as $row)
                    <tr>
                        <td>{{ $row['nama'] ?? '' }}</td>
                        <td>{{ ($row['jenis'] ?? '') === 'potong' ? 'Potong' : 'Tambah' }}</td>
                        <td class="text-right">Rp {{ number_format((float) ($row['nominal'] ?? 0), 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td colspan="2" class="font-bold">Total</td><td class="text-right font-bold">{{ $gaji->getTotalFormatted() }}</td></tr></tfoot>
            </table>
        </div>

        @if($gaji->isApproved())
            <div class="card p-4 text-sm space-y-2">
                @if($gaji->jurnal_id)
                    <p>Jurnal: <a href="{{ route('admin.jurnal.show', $gaji->jurnal_id) }}" class="font-semibold" style="color:#1A6B6B;">#{{ $gaji->jurnal_id }}</a></p>
                @endif
                @if($gaji->cashflow_id)
                    <p>Cashflow tercatat (ID {{ $gaji->cashflow_id }}). Lihat di menu <a href="{{ route('admin.cashflow.index') }}" class="font-semibold" style="color:#1A6B6B;">Cashflow</a>.</p>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            @if($gaji->isDraft())
                <a href="{{ route('admin.gaji-guru.edit', $gaji) }}" class="btn-primary text-sm">Edit</a>
                <form method="POST" action="{{ route('admin.gaji-guru.approve', $gaji) }}">@csrf @method('PATCH')<button type="submit" class="btn-primary text-sm">Setujui</button></form>
                <form method="POST" action="{{ route('admin.gaji-guru.destroy', $gaji) }}" onsubmit="return confirm('Hapus slip draft?')">@csrf @method('DELETE')<button type="submit" class="btn-danger text-sm">Hapus</button></form>
            @else
                <form method="POST" action="{{ route('admin.gaji-guru.batalkan', $gaji) }}" onsubmit="return confirm('Batalkan persetujuan? Cashflow dan jurnal akan dihapus.')">@csrf @method('PATCH')<button type="submit" class="btn-secondary text-sm">Batalkan Persetujuan</button></form>
            @endif
        </div>
    </div>
</x-app-layout>
