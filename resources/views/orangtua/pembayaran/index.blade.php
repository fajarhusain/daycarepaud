<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h2 class="font-bold text-lg sm:text-xl" style="color: #2C2C2C;">Pembayaran Bulanan</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if($anaks->count() > 1)
            <div class="card overflow-hidden mb-4 sm:mb-6">
                <div class="px-3 py-3 sm:card-pad border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title mb-0 text-base">Filter Tagihan</h3>
                </div>
                <form method="GET" action="{{ route('orangtua.pembayaran.index') }}" class="px-3 py-3 sm:card-pad flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3">
                    <div class="w-full sm:min-w-0 sm:flex-1 sm:max-w-xs">
                        <label class="input-label">Anak</label>
                        <select name="anak_id" class="input-field" onchange="this.form.submit()">
                            <option value="">Semua Anak</option>
                            @foreach($anaks as $anak)
                                <option value="{{ $anak->id }}" @selected($selectedAnakId === $anak->id)>{{ $anak->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-filter-reset :href="route('orangtua.pembayaran.index')" />
                </form>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 mb-4 sm:mb-8" data-tour="ortu-pembayaran-summary">
            @foreach($anaks as $anak)
                @php
                    $tagihanAnak = $summaryPembayarans->where('anak_id', $anak->id);
                    $belumBayar = $tagihanAnak->whereIn('status', ['pending', 'rejected'])->sum('total_bayar');
                    $sudahBayar = $tagihanAnak->where('status', 'approved')->sum('total_bayar');
                @endphp
                <div class="card px-3 py-3 sm:card-body-pad">
                    <div class="flex items-center gap-3 mb-3 sm:mb-4">
                        <x-foto-profil :path="$anak->photo" :name="$anak->name" size="sm" class="shrink-0 sm:!h-10 sm:!w-10" />
                        <div class="min-w-0">
                            <h4 class="font-bold text-sm sm:text-base leading-tight line-clamp-2 normal-case">{{ $anak->name }}</h4>
                            <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 truncate">{{ $anak->kelas->name ?? 'Tanpa Kelas' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:gap-4">
                        <div class="bg-gray-50 rounded-lg sm:rounded-xl p-2.5 sm:p-3 text-center min-w-0">
                            <p class="text-[9px] sm:text-[10px] font-semibold uppercase text-gray-400 mb-0.5 sm:mb-1 leading-tight">Belum dibayar</p>
                            <p class="text-sm sm:text-lg font-bold text-amber-600 tabular-nums leading-tight">Rp {{ number_format($belumBayar, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg sm:rounded-xl p-2.5 sm:p-3 text-center min-w-0">
                            <p class="text-[9px] sm:text-[10px] font-semibold uppercase text-gray-400 mb-0.5 sm:mb-1 leading-tight">Sudah dibayar</p>
                            <p class="text-sm sm:text-lg font-bold text-[#1A6B6B] tabular-nums leading-tight">Rp {{ number_format($sudahBayar, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $showAnakCol = $anaks->count() > 1 && $selectedAnakId === null;
            $tableColspan = $showAnakCol ? 5 : 4;
        @endphp

        <div class="card overflow-hidden">
            <div class="px-3 py-3 sm:card-pad border-b page-toolbar" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title mb-0 text-base sm:text-lg">Daftar Tagihan</h3>
                </div>
                <div class="text-xs font-semibold text-gray-400 shrink-0">{{ $pembayarans->total() }} tagihan</div>
            </div>
            <div class="table-responsive w-full" data-tour="ortu-pembayaran-table">
                <table class="w-full min-w-full table-fixed text-left">
                    <colgroup>
                        <col style="width:{{ $showAnakCol ? '22%' : '28%' }}">
                        @if($showAnakCol)
                            <col style="width:28%">
                        @endif
                        <col style="width:{{ $showAnakCol ? '26%' : '32%' }}">
                        <col style="width:{{ $showAnakCol ? '18%' : '24%' }}">
                        <col style="width:{{ $showAnakCol ? '6%' : '16%' }}">
                    </colgroup>
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-2 py-2.5 sm:px-4 sm:py-3 text-[10px] sm:text-xs font-bold uppercase text-gray-400">Periode</th>
                            @if($showAnakCol)
                                <th class="px-2 py-2.5 sm:px-4 sm:py-3 text-[10px] sm:text-xs font-bold uppercase text-gray-400">Anak</th>
                            @endif
                            <th class="px-2 py-2.5 sm:px-4 sm:py-3 text-[10px] sm:text-xs font-bold uppercase text-gray-400 text-right">Total</th>
                            <th class="px-2 py-2.5 sm:px-4 sm:py-3 text-[10px] sm:text-xs font-bold uppercase text-gray-400 text-center">Status</th>
                            <th class="px-1 py-2.5 sm:px-2 sm:py-3 text-[10px] sm:text-xs font-bold uppercase text-gray-400 text-center"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pembayarans as $p)
                            @php
                                $periodePendek = \Carbon\Carbon::create((int) $p->periode_tahun, (int) $p->periode_bulan, 1)->locale('id')->translatedFormat('M y');
                                $detailUrl = route('orangtua.pembayaran.show', $p);
                            @endphp
                            <tr class="hover:bg-gray-50/80 cursor-pointer sm:cursor-default" onclick="if (window.matchMedia('(max-width: 639px)').matches) { window.location.href='{{ $detailUrl }}' }">
                                <td class="px-2 py-3 sm:px-4 sm:py-4 font-semibold align-middle">
                                    <a href="{{ $detailUrl }}" class="block text-xs sm:text-sm leading-tight hover:underline sm:no-underline" style="color:#2C2C2C;">
                                        <span class="sm:hidden" title="{{ $p->getPeriodeLabel() }}">{{ $periodePendek }}</span>
                                        <span class="hidden sm:inline">{{ $p->getPeriodeLabel() }}</span>
                                    </a>
                                </td>
                                @if($showAnakCol)
                                    <td class="px-2 py-3 sm:px-4 sm:py-4 align-middle">
                                        <span class="block text-xs sm:text-sm leading-tight line-clamp-2" title="{{ $p->anak->name }}">{{ $p->anak->name }}</span>
                                    </td>
                                @endif
                                <td class="px-2 py-3 sm:px-4 sm:py-4 text-right align-middle font-bold text-xs sm:text-sm tabular-nums whitespace-nowrap">
                                    {{ $p->getTotalFormatted() }}
                                </td>
                                <td class="px-2 py-3 sm:px-4 sm:py-4 text-center align-middle">
                                    <span class="badge badge-{{ $p->status_badge }} !text-[10px] sm:!text-xs !px-2 !py-0.5 whitespace-nowrap">{{ $p->status_label }}</span>
                                </td>
                                <td class="px-1 py-3 sm:px-2 sm:py-4 align-middle" onclick="event.stopPropagation()">
                                    <div class="flex justify-center sm:justify-end gap-1 sm:gap-2">
                                        <a @if($loop->first) data-tour="ortu-pembayaran-action-detail" @endif href="{{ $detailUrl }}" class="row-action-icon shrink-0" style="color:#1A6B6B;background:#D0E8E8;" title="Detail" aria-label="Detail">
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @if($p->isApproved())
                                            <a href="{{ route('orangtua.pembayaran.invoice', $p) }}" class="row-action-icon shrink-0" style="color:#1A6B6B;background:#E8F5F5;border:1px solid #D0E8E8;" target="_blank" rel="noopener" title="Download Invoice" aria-label="Download Invoice">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $tableColspan }}" class="px-3 sm:px-6 py-12 text-center text-gray-400 text-sm">Belum ada tagihan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3 sm:card-pad border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$pembayarans" />
                {{ $pembayarans->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
