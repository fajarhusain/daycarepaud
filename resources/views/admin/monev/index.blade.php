<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Monev Matrikulasi</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @include('monev._index-content', [
            'indexRoute' => route('admin.monev.index'),
            'generateRoute' => route('admin.monev.generate'),
            'bulkGenerateRoute' => route('admin.monev.bulk-generate'),
            'bulkResetRoute' => route('admin.monev.bulk-reset'),
            'showRouteName' => 'admin.monev.show',
            'statusRoute' => $activeGeneration ? route('admin.monev.generation.status', $activeGeneration) : null,
            'activeGeneration' => $activeGeneration,
            'tourPrefix' => 'admin-monev',
            'exportRoute' => 'admin.monev.export',
        ])
    </div>
</x-app-layout>
