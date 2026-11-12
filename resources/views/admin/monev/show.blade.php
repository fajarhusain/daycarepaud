<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Detail Monev  {{ $anak->name }}</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @include('monev._show-content', [
            'backRoute' => route('admin.monev.index', ['tahun' => $tahun, 'bulan' => $bulan]),
            'pdfRoute' => route('admin.monev.export-pdf', ['anak' => $anak->id, 'tahun' => $tahun, 'bulan' => $bulan]),
            'showBackLink' => true,
        ])
    </div>
</x-app-layout>
