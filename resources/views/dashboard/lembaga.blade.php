<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard Yayasan / Lembaga') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Summary Card 1 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Sekolah</h3>
                        <p class="mt-2 text-3xl font-semibold">12</p>
                    </div>
                </div>
                <!-- Summary Card 2 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Admin</h3>
                        <p class="mt-2 text-3xl font-semibold">24</p>
                    </div>
                </div>
                <!-- Summary Card 3 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Feedback</h3>
                        <p class="mt-2 text-3xl font-semibold">5</p>
                    </div>
                </div>
            </div>

            <!-- Main Panel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <h3 class="text-lg font-medium mb-4">Akses Cepat</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="px-4 py-2 bg-slate-900 text-white rounded-md text-sm hover:bg-slate-800 transition">Kelola Sekolah</a>
                        <a href="#" class="px-4 py-2 bg-white text-slate-900 border border-slate-300 rounded-md text-sm hover:bg-slate-50 transition">Input Admin Sekolah</a>
                        <a href="#" class="px-4 py-2 bg-white text-slate-900 border border-slate-300 rounded-md text-sm hover:bg-slate-50 transition">Lihat Kritik & Saran</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
