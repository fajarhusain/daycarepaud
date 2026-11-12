<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3" data-tour="page-header">
                <a href="{{ route('admin.anak.index') }}" data-tour="anak-actions" class="h-8 w-8 rounded-lg flex items-center justify-center bg-gray-100 hover:bg-gray-200 transition">
                    <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </a>
                <h2 class="font-bold text-xl" style="color: #2C2C2C;">Detail Siswa</h2>
            </div>
        </div>
    </x-slot>

    <div class="app-page-shell space-y-6" x-data="{ showImageModal: false, activeImage: null, activeDownloadUrl: null }">
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

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mt-8 pt-8 border-t border-gray-100">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Nama Panggilan</p>
                        <p class="font-bold text-[#2C2C2C]">{{ $anak->nickname ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Tanggal Lahir</p>
                        <p class="font-bold text-[#2C2C2C]">
                            {{ $anak->dob ? \Carbon\Carbon::parse($anak->dob)->translatedFormat('d F Y') : '-' }}
                            @if($anak->dob)<span class="ml-1 text-[#1A6B6B] font-bold text-sm">({{ $anak->age }})</span>@endif
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Nama Wali</p>
                        <p class="font-bold text-[#2C2C2C]">{{ $anak->parent_name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Email Wali</p>
                        <p class="font-bold text-[#2C2C2C]">{{ $anak->user ? $anak->user->email : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Status Akun</p>
                        <span class="badge {{ $anak->status === 'approved' ? 'badge-teal' : 'bg-yellow-100 text-yellow-700' }} font-bold">
                            {{ ucfirst($anak->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" data-tour="anak-detail">
            <div class="space-y-6">
                <!-- Most Recent Health Stats -->
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

                <!-- Parent Details -->
                <div class="card p-6">
                    <h3 class="font-bold text-[#2C2C2C] mb-4">Informasi Orang Tua</h3>
                    <div class="space-y-4 text-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">Nama Bapak</p>
                                <p class="text-[#2C2C2C]">{{ $anak->nama_bapak ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">NIK Bapak</p>
                                <p class="text-[#2C2C2C]">{{ $anak->nik_bapak ?: '-' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">Nama Ibu</p>
                                <p class="text-[#2C2C2C]">{{ $anak->nama_ibu ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">NIK Ibu</p>
                                <p class="text-[#2C2C2C]">{{ $anak->nik_ibu ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alamat & Catatan -->
                <div class="card p-6">
                    <h3 class="font-bold text-[#2C2C2C] mb-4">Informasi Tambahan</h3>
                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">Alamat</p>
                            <p class="text-[#2C2C2C] leading-relaxed">{{ $anak->alamat ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">Alergi</p>
                            <p class="font-semibold {{ $anak->kesehatans->whereNotNull('alergi')->first() ? 'text-red-600' : 'text-gray-400' }}">
                                {{ $anak->kesehatans->whereNotNull('alergi')->first()->alergi ?? 'Tidak Ada' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: History Timeline -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b flex items-center justify-between bg-white" style="border-color: rgba(0,0,0,0.06);">
                        <h3 class="font-bold text-lg text-[#2C2C2C]">Riwayat Perkembangan Kesehatan</h3>
                    </div>
                    <div class="p-6">
                        @if($anak->kesehatans->isEmpty())
                            <div class="text-center py-12 text-gray-400">
                                <svg class="h-12 w-12 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Belum ada riwayat pemeriksaan kesehatan.
                            </div>
                        @else
                            <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 before:to-transparent">
                                @foreach($anak->kesehatans as $record)
                                    <div class="relative flex items-start group">
                                        <div class="absolute left-0 h-10 w-10 flex items-center justify-center rounded-full bg-white border-2 border-[#1A6B6B] shadow-sm shrink-0 z-10">
                                            <svg class="h-4 w-4 text-[#1A6B6B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="ml-14 flex-1">
                                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-2">
                                                <div class="text-sm font-bold text-[#1A6B6B] bg-[#D0E8E8] px-3 py-1 rounded-full w-fit">
                                                    {{ \Carbon\Carbon::parse($record->tanggal_pemeriksaan)->translatedFormat('d F Y') }}
                                                </div>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                                <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-0.5">BB</p>
                                                    <p class="font-bold text-sm">{{ $record->berat_badan ?: '-' }} <span class="text-[10px] font-normal">kg</span></p>
                                                </div>
                                                <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-0.5">TB</p>
                                                    <p class="font-bold text-sm">{{ $record->tinggi_badan ?: '-' }} <span class="text-[10px] font-normal">cm</span></p>
                                                </div>
                                                <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-0.5">LK</p>
                                                    <p class="font-bold text-sm">{{ $record->lingkar_kepala ?: '-' }} <span class="text-[10px] font-normal">cm</span></p>
                                                </div>
                                                <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                                    <p class="text-[9px] uppercase font-bold text-gray-400 mb-0.5">G/T/K</p>
                                                    <div class="flex gap-1.5 mt-0.5">
                                                        @foreach(['gigi' => 'G', 'telinga' => 'T', 'kuku' => 'K'] as $field => $label)
                                                            @php 
                                                                $val = $record->$field;
                                                                $isGood = Str::contains(strtolower($val), 'bersih') || Str::contains(strtolower($val), 'rapi');
                                                                $text = $isGood ? 'text-green-600' : 'text-red-600';
                                                            @endphp
                                                            <span title="{{ $val }}" class="font-bold text-[11px] {{ $text }}">{{ $label }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            @if($record->alergi)
                                                <div class="mt-3 p-3 rounded-xl bg-red-50 border border-red-100 flex items-center gap-3">
                                                    <svg class="h-4 w-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                                    <p class="text-xs font-semibold text-red-700">Alergi: {{ $record->alergi }}</p>
                                                </div>
                                            @endif
                                            
                                            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                                                @foreach(['gigi' => 'Gigi', 'telinga' => 'Telinga', 'kuku' => 'Kuku'] as $f => $l)
                                                    <div class="flex items-center gap-2">
                                                        @php 
                                                            $val = $record->$f;
                                                            $isGood = Str::contains(strtolower($val), 'bersih') || Str::contains(strtolower($val), 'rapi');
                                                        @endphp
                                                        <div class="h-1.5 w-1.5 rounded-full {{ $isGood ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                                        <span class="text-[10px] font-medium text-gray-500">{{ $l }}: <span class="text-gray-700">{{ $val ?: '-' }}</span></span>
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

                <!-- ACHIEVEMENT HISTORY -->
                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b flex items-center justify-between bg-white" style="border-color: rgba(0,0,0,0.06);">
                        <h3 class="font-bold text-lg text-[#2C2C2C]">Riwayat Pencapaian & Matrikulasi</h3>
                    </div>
                    <div class="p-6">
                        @if($anak->pencapaians->isEmpty())
                            <div class="text-center py-12 text-gray-400">
                                <svg class="h-12 w-12 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Belum ada riwayat pencapaian.
                            </div>
                        @else
                            <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 before:to-transparent">
                                @foreach($anak->pencapaians->groupBy('kegiatan_id') as $kgId => $rows)
                                    @php $first = $rows->first(); @endphp
                                    <div class="relative flex items-start group">
                                        <div class="absolute left-0 h-10 w-10 flex items-center justify-center rounded-full bg-white border-2 border-teal-600 shadow-sm shrink-0 z-10 font-bold text-teal-600 text-xs">
                                            {{ $loop->iteration }}
                                        </div>
                                        <div class="ml-14 flex-1">
                                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                                                <div>
                                                    <div class="text-sm font-bold text-teal-700 bg-teal-50 px-3 py-1 rounded-full w-fit mb-1">
                                                        {{ \Carbon\Carbon::parse($first->created_at)->translatedFormat('d F Y') }}
                                                    </div>
                                                    <h4 class="font-bold text-gray-800 text-sm italic">{{ $first->kegiatan->title ?? '-' }}</h4>
                                                </div>
                                                @if($first->photo)
                                                    <img src="{{ Storage::url($first->photo) }}" class="h-16 w-16 object-cover rounded-xl border border-black/5 shadow-sm cursor-pointer" @click="activeImage = '{{ Storage::url($first->photo) }}'; activeDownloadUrl = '{{ route('admin.pencapaian.photos.download-bundle', ['anak_id' => $first->anak_id, 'kegiatan_id' => $first->kegiatan_id]) }}'; showImageModal = true">
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($rows as $p)
                                                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 space-y-1.5">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <span class="text-[10px] font-black text-teal-800 uppercase">{{ $p->matrikulasi->aspek ?: 'Umum' }}</span>
                                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background:{{ \App\Support\LabelSkorPencapaian::color($p->score, $anak->sekolah_id) }};">
                                                                {{ \App\Support\LabelSkorPencapaian::label($p->score, $anak->sekolah_id) }}
                                                            </span>
                                                        </div>
                                                        <p class="text-xs font-medium text-gray-600 leading-tight">
                                                            {{ $p->matrikulasi->indicator ?? '' }}
                                                        </p>
                                                        @if($p->feedback)
                                                            <p class="text-[11px] italic text-gray-400 mt-1">"{{ $p->feedback }}"</p>
                                                        @endif
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

        <x-image-lightbox />
    </div>
</x-app-layout>
