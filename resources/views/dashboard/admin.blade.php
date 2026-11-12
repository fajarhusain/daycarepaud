<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Dashboard Admin Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Siswa</h3>
                        <p class="mt-2 text-3xl font-semibold">{{ $totalAnak ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Pengajar</h3>
                        <p class="mt-2 text-3xl font-semibold">{{ $totalPengajar ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Sarana</h3>
                        <p class="mt-2 text-3xl font-semibold">{{ $totalSarana ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6 text-slate-900">
                        <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Saldo Kas</h3>
                        <p class="mt-2 text-xl font-semibold {{ ($saldoKas ?? 0) >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            Rp {{ number_format($saldoKas ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Keuangan Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Keuangan & Akuntansi</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('admin.akun.index') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Kode Rekening & Akun</span>
                            <span class="text-xs text-slate-400 mt-1">Kelola Akun</span>
                        </a>
                        <a href="{{ route('admin.jurnal.index') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Jurnal Umum</span>
                            <span class="text-xs text-slate-400 mt-1">Double Entry</span>
                        </a>
                        <a href="{{ route('admin.rkas.laporan') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Laporan RKAS</span>
                            <span class="text-xs text-slate-400 mt-1">Anggaran vs Realisasi</span>
                        </a>
                        <a href="{{ route('admin.akuntansi-setting.index') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Setting Akuntansi</span>
                            <span class="text-xs text-slate-400 mt-1">Konfigurasi</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actions Panel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6 text-slate-900">
                    <h3 class="text-lg font-medium mb-4">Manajemen Sekolah</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('admin.anak.index') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Data Anak & Ortu</span>
                        </a>
                        <a href="{{ route('admin.pengajar.index') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Data Guru</span>
                        </a>
                        <a href="{{ route('admin.sarana.index') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Sarana Prasarana</span>
                        </a>
                        <a href="#" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Menu Makanan</span>
                        </a>
                        <a href="{{ route('admin.kegiatan.index') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Jadwal Kegiatan</span>
                        </a>
                        <a href="{{ route('admin.cashflow.index') }}" class="p-4 border border-slate-200 rounded-lg hover:border-slate-900 transition flex flex-col items-center justify-center text-center">
                            <span class="font-medium">Modul Keuangan</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
