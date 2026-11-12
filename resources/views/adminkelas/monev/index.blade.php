<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Monev Kelasku</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if($kelasList->count() > 1 && !$filterKelasId && $isCurrentMonth && $canManual === false)
            <div class="alert-danger mb-5 text-sm">Pilih kelas terlebih dahulu untuk generate ringkasan manual.</div>
        @endif

        @include('monev._index-content', [
            'indexRoute' => route('adminkelas.monev.index'),
            'generateRoute' => route('adminkelas.monev.generate'),
            'bulkGenerateRoute' => route('adminkelas.monev.bulk-generate'),
            'bulkResetRoute' => route('adminkelas.monev.bulk-reset'),
            'showRouteName' => 'adminkelas.monev.show',
            'statusRoute' => $activeGeneration ? route('adminkelas.monev.generation.status', $activeGeneration) : null,
            'activeGeneration' => $activeGeneration,
            'tourPrefix' => 'ak-monev',
        ])
    </div>
</x-app-layout>
