<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 w-full">
            <div class="flex items-center gap-3" data-tour="page-header">
                <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background: #1A6B6B;">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <div data-tour="kelas-summary">
                    <h2 class="font-bold text-xl" style="color: #2C2C2C;">{{ $kelas->name }}</h2>
                    <p class="text-sm mt-0.5" style="color:#5A5A5A;">Daftar siswa di kelas ini</p>
                </div>
            </div>
            <a href="{{ route('admin.kelas.index') }}" data-tour="kelas-actions" class="btn-secondary text-sm inline-flex items-center gap-1.5 self-start sm:self-center">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali ke daftar kelas
            </a>
        </div>
    </x-slot>

    <div class="app-page-shell" data-tour="kelas-siswa-list">
        @include('admin.kelas.partials.kelas-siswa-panels')
    </div>
</x-app-layout>
