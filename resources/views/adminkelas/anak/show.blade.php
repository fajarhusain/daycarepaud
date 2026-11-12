<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3" data-tour="page-header">
                <a href="{{ route('adminkelas.anak.index') }}" class="h-8 w-8 rounded-lg flex items-center justify-center bg-gray-100 hover:bg-gray-200 transition">
                    <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </a>
                <h2 class="font-bold text-xl" style="color: #2C2C2C;">Detail Siswa Kelasku</h2>
            </div>
        </div>
    </x-slot>

    <div class="app-page-shell space-y-6">
        <div class="card overflow-hidden" data-tour="anak-summary">
            <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row gap-6 items-start md:items-center">
                    <x-foto-profil :path="$anak->photo" :name="$anak->name" size="hero" rounded="xl" class="shadow-lg ring-2 ring-white" />
                    <div class="flex-1">
                        <h1 class="text-2xl md:text-3xl font-bold text-[#2C2C2C] mb-2">{{ $anak->name }}</h1>
                        <div class="flex flex-wrap gap-3 items-center">
                            @if($anak->kelas)
                                <span class="badge badge-teal py-1.5 px-4 font-bold text-sm">{{ $anak->kelas->name }}</span>
                            @endif
                            <span class="text-gray-400">|</span>
                            <span class="text-sm font-medium text-gray-500">NIK: {{ $anak->nik ?: '-' }}</span>
                            <span class="text-gray-400">|</span>
                            <span class="text-sm font-medium text-gray-500">{{ $anak->jenis_kelamin }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8 pt-8 border-t border-gray-100">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Tanggal Lahir</p>
                        <p class="font-bold text-[#2C2C2C]">
                            {{ $anak->dob ? \Carbon\Carbon::parse($anak->dob)->translatedFormat('d F Y') : '-' }}
                            @if($anak->dob)<span class="ml-1 text-[#1A6B6B] font-bold text-sm">({{ $anak->age }})</span>@endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Nama Orang Tua</p>
                        <p class="font-bold text-[#2C2C2C]">{{ $anak->parent_name }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Alamat</p>
                        <p class="font-bold text-[#2C2C2C]">{{ $anak->alamat ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" data-tour="anak-detail">
            <div class="space-y-6">
                <div class="card p-6">
                    <h3 class="font-bold text-[#2C2C2C] mb-4 flex items-center justify-between">
                        Kondisi Terakhir
                        @if($anak->kesehatans->isNotEmpty())
                            <span class="text-[10px] font-normal text-gray-400">{{ \Carbon\Carbon::parse($anak->kesehatans->first()->tanggal_pemeriksaan)->format('d/m/Y') }}</span>
                        @endif
                    </h3>
                    
                    @if($anak->kesehatans->isEmpty())
                        <p class="text-sm text-gray-400 italic">Belum ada data kesehatan.</p>
                    @else
                        @php $latest = $anak->kesehatans->first(); @endphp
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                                <span class="text-sm text-gray-500">Berat Badan</span>
                                <span class="font-bold text-[#1A6B6B]">{{ $latest->berat_badan ?: '-' }} <small class="font-normal text-gray-400">kg</small></span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                                <span class="text-sm text-gray-500">Tinggi Badan</span>
                                <span class="font-bold text-[#1A6B6B]">{{ $latest->tinggi_badan ?: '-' }} <small class="font-normal text-gray-400">cm</small></span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                                <span class="text-sm text-gray-500">Lingkar Kepala</span>
                                <span class="font-bold text-[#1A6B6B]">{{ $latest->lingkar_kepala ?: '-' }} <small class="font-normal text-gray-400">cm</small></span>
                            </div>
                            <div class="pt-2">
                                <p class="text-[10px] uppercase font-bold text-gray-400 mb-2">Kebersihan (G/T/K)</p>
                                <div class="flex gap-2">
                                    @foreach(['gigi' => 'G', 'telinga' => 'T', 'kuku' => 'K'] as $field => $label)
                                        @php 
                                            $val = $latest->$field;
                                            $isGood = Str::contains(strtolower($val), 'bersih') || Str::contains(strtolower($val), 'rapi');
                                            $bg = $isGood ? 'bg-[#E8F5E9]' : 'bg-[#FFEBEE]';
                                            $text = $isGood ? 'text-[#2E7D32]' : 'text-[#C62828]';
                                        @endphp
                                        <div title="{{ ucfirst($field) }}: {{ $val }}" class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm {{ $bg }} {{ $text }} border-2 border-white shadow-sm cursor-help">
                                            {{ $label }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card p-6">
                    <h3 class="font-bold text-[#2C2C2C] mb-4">Alergi & Catatan Khusus</h3>
                    <p class="font-semibold {{ $anak->kesehatans->whereNotNull('alergi')->first() ? 'text-red-600' : 'text-gray-400' }}">
                        {{ $anak->kesehatans->whereNotNull('alergi')->first()->alergi ?? 'Tidak Ada Alergi Teratat' }}
                    </p>
                </div>
            </div>

            <!-- Right Column: History -->
            <div class="lg:col-span-2">
                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b bg-white">
                        <h3 class="font-bold text-lg text-[#2C2C2C]">Riwayat Kesehatan</h3>
                    </div>
                    <div class="p-6">
                        @if($anak->kesehatans->isEmpty())
                            <div class="text-center py-12 text-gray-400">Belum ada riwayat.</div>
                        @else
                            <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:h-full before:w-0.5 before:bg-gray-100">
                                @foreach($anak->kesehatans as $record)
                                    <div class="relative flex items-start">
                                        <div class="absolute left-0 h-10 w-10 flex items-center justify-center rounded-full bg-white border-2 border-[#1A6B6B] z-10 shadow-sm">
                                            <svg class="h-4 w-4 text-[#1A6B6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="ml-14 flex-1">
                                            <div class="text-sm font-bold text-[#1A6B6B] bg-[#D0E8E8] px-3 py-1 rounded-full w-fit mb-3">
                                                {{ \Carbon\Carbon::parse($record->tanggal_pemeriksaan)->translatedFormat('d F Y') }}
                                            </div>
                                            
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                                <div>
                                                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-0.5">BB</p>
                                                    <p class="font-bold text-sm text-gray-700">{{ $record->berat_badan ?: '-' }} kg</p>
                                                </div>
                                                <div>
                                                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-0.5">TB</p>
                                                    <p class="font-bold text-sm text-gray-700">{{ $record->tinggi_badan ?: '-' }} cm</p>
                                                </div>
                                                <div>
                                                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-0.5">LK</p>
                                                    <p class="font-bold text-sm text-gray-700">{{ $record->lingkar_kepala ?: '-' }} cm</p>
                                                </div>
                                                <div>
                                                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-0.5">G/T/K</p>
                                                    <div class="flex gap-1">
                                                        @foreach(['gigi' => 'G', 'telinga' => 'T', 'kuku' => 'K'] as $f => $l)
                                                            @php $isGood = Str::contains(strtolower($record->$f), 'bersih') || Str::contains(strtolower($record->$f), 'rapi'); @endphp
                                                            <span class="font-bold text-[10px] {{ $isGood ? 'text-green-600' : 'text-red-500' }}">{{ $l }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2">
                                                @foreach(['gigi' => 'Gigi', 'telinga' => 'Telinga', 'kuku' => 'Kuku'] as $f => $label)
                                                    <div class="text-[10px] flex items-center gap-1.5">
                                                        @php $isGood = Str::contains(strtolower($record->$f), 'bersih') || Str::contains(strtolower($record->$f), 'rapi'); @endphp
                                                        <div class="h-1.5 w-1.5 rounded-full {{ $isGood ? 'bg-green-500' : 'bg-red-400' }}"></div>
                                                        <span class="text-gray-500">{{ $label }}:</span>
                                                        <span class="text-gray-700 font-medium">{{ $record->$f ?: '-' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
