<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard Pengajar') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Summary Card 1 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Jadwal Hari Ini</h3>
                            <p class="mt-2 text-2xl font-semibold">Mewarnai & Bermain</p>
                        </div>
                        <a href="#" class="px-4 py-2 bg-slate-900 text-white rounded-md text-sm hover:bg-slate-800 transition">Update</a>
                    </div>
                </div>
                <!-- Summary Card 2 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Pencapaian Menunggu Dinilai</h3>
                            <p class="mt-2 text-3xl font-semibold text-amber-600">8 Siswa</p>
                        </div>
                        <a href="#" class="px-4 py-2 border border-slate-300 rounded-md text-sm hover:bg-slate-50 transition">Nilai Sekarang</a>
                    </div>
                </div>
            </div>

            <!-- Modul Panel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <h3 class="text-lg font-medium mb-4">Modul Guru</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <a href="#" class="p-4 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 transition">
                            <h4 class="font-semibold mb-1">Kegiatan Anak</h4>
                            <p class="text-sm text-slate-500">Unggah foto dan deskripsi aktivitas hari ini.</p>
                        </a>
                        <a href="#" class="p-4 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 transition">
                            <h4 class="font-semibold mb-1">Matrikulasi</h4>
                            <p class="text-sm text-slate-500">Lihat acuan kurikulum PAUD.</p>
                        </a>
                        <a href="#" class="p-4 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 transition">
                            <h4 class="font-semibold mb-1">Pencapaian Anak</h4>
                            <p class="text-sm text-slate-500">Berikan feedback indikator matrikulasi.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
