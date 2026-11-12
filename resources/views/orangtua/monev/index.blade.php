@php
    use App\Support\IndonesianMonths;

    $months = IndonesianMonths::NAMES;
    $periodeLabel = IndonesianMonths::label($bulan, $tahun);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">
                Monev{{ $selectedAnak ? '  ' . $selectedAnak->name : '' }}
            </h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <div class="card mb-6">
            <div class="px-5 sm:px-6 py-5 border-b space-y-5" style="background:#FAF6F0; border-color: rgba(0,0,0,0.06);">
                <div class="space-y-1">
                    <h3 class="text-xl font-bold" style="color:#2C2C2C;">Ringkasan Perkembangan</h3>
                </div>

                <form data-tour="ortu-monev-filters" method="get" action="{{ route('orangtua.monev.index') }}"
                    class="filter-toolbar-inline">
                    @if($anaks->count() > 1)
                        <div class="filter-toolbar-field">
                            <label class="input-label" for="ortu-monev-anak">Anak</label>
                            <select id="ortu-monev-anak" name="anak_id" class="input-field w-full h-9 md:h-11">
                                @foreach($anaks as $anak)
                                    <option value="{{ $anak->id }}" @selected($selectedAnak?->id === $anak->id)>{{ $anak->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($selectedAnak)
                        <input type="hidden" name="anak_id" value="{{ $selectedAnak->id }}">
                    @endif
                    <div class="filter-toolbar-field">
                        <label class="input-label" for="ortu-monev-bulan">Bulan</label>
                        <select id="ortu-monev-bulan" name="bulan" class="input-field w-full h-9 md:h-11">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" @selected($bulan == $num)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-toolbar-field">
                        <label class="input-label" for="ortu-monev-tahun">Tahun</label>
                        <select id="ortu-monev-tahun" name="tahun" class="input-field w-full h-9 md:h-11">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                                <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="filter-toolbar-actions">
                        <button type="submit" class="btn-primary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0" title="Tampilkan" aria-label="Tampilkan">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                        <x-filter-reset :href="route('orangtua.monev.index')" compact />
                    </div>
                </form>
            </div>
            <div class="px-5 sm:px-6 py-3" style="border-color: rgba(0,0,0,0.06);">
                <span class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-lg" style="background:#E8F5F5; color:#1A6B6B;">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    {{ $periodeLabel }}
                </span>
            </div>
        </div>

        @if($anaks->isEmpty())
            <div class="card px-6 py-16 text-center text-sm" style="color:#9E9790;">
                Belum ada data anak aktif.
            </div>
        @elseif(!$summary)
            <div class="card px-6 py-16 text-center">
                <p class="text-sm font-medium" style="color:#2C2C2C;">Ringkasan belum tersedia</p>
                <p class="text-sm mt-2" style="color:#9E9790;">
                    Laporan Monev untuk <strong>{{ $selectedAnak->name }}</strong> pada {{ $periodeLabel }} belum dipublikasikan sekolah.
                </p>
            </div>
        @else
            <div data-tour="ortu-monev-report">
            @include('monev._show-content', [
                'anak' => $selectedAnak,
                'summary' => $summary,
                'pdfRoute' => route('orangtua.monev.export-pdf', ['anak' => $selectedAnak->id, 'tahun' => $tahun, 'bulan' => $bulan]),
                'showBackLink' => false,
            ])
            </div>
        @endif
    </div>
</x-app-layout>
