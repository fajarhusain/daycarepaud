<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard Orang Tua') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Feed Terbaru -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 mb-6">
                <div class="p-6 text-slate-900">
                    <h3 class="text-lg font-medium mb-4 flex justify-between items-center">
                        Aktivitas Terbaru
                        <span class="text-sm text-slate-500 font-normal">Hari ini, 10:00 AM</span>
                    </h3>
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-100 mb-4">
                        <h4 class="font-semibold">Bermain Puzzle Bersama</h4>
                        <p class="text-slate-600 mt-2">Anak-anak belajar mengenali bentuk dan warna sambil memecahkan puzzle bersama kelompok mereka.</p>
                    </div>
                    <a href="#" class="text-sm text-slate-600 hover:text-slate-900 font-medium">Lihat Semua Kegiatan &rarr;</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Info Nutrisi -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                         <h3 class="text-md font-medium mb-3">Menu Makan Hari Ini</h3>
                         <div class="flex items-center space-x-4">
                             <div class="w-16 h-16 bg-slate-200 rounded-md"></div>
                             <div>
                                 <h4 class="font-semibold">Sup Sayur & Ayam Goreng</h4>
                                 <p class="text-sm text-slate-500">Vitamin C, Protein, Karbohidrat</p>
                             </div>
                         </div>
                    </div>
                </div>
                <!-- Pencapaian -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                         <h3 class="text-md font-medium mb-3">Pencapaian Bulan Ini</h3>
                         <div class="bg-emerald-50 text-emerald-700 p-3 rounded border border-emerald-200 text-sm">
                             <strong>Kognitif:</strong> Sangat Baik. Anak mampu mengidentifikasi 5 warna dasar.
                         </div>
                         <div class="mt-3">
                             <a href="#" class="text-sm text-slate-600 hover:text-slate-900 font-medium">Lihat Laporan Lengkap &rarr;</a>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Tindakan Tambahan -->
            <div class="flex space-x-4">
                <a href="#" class="px-4 py-2 bg-slate-900 text-white rounded-md text-sm hover:bg-slate-800 transition">Kirim Kritik & Saran</a>
                <a href="#" class="px-4 py-2 bg-white text-slate-900 border border-slate-300 rounded-md text-sm hover:bg-slate-50 transition">Detail Matrikulasi</a>
            </div>
        </div>
    </div>
</x-app-layout>
