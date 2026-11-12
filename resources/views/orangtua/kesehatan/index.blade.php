<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Laporan Kesehatan Anak</h2>
        </div>
    </x-slot>

    <div class="app-page-shell space-y-6">
        @forelse($anaks as $anak)
            <div class="card overflow-hidden" data-tour="ortu-kesehatan-metrics">
                <div class="card-pad bg-[#F8F5F1] border-b flex items-center justify-between min-w-0" style="border-color: rgba(0,0,0,0.06);">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-foto-profil :path="$anak->photo" :name="$anak->name" size="lg" rounded="xl" />
                        <div>
                            <h3 class="font-bold text-base sm:text-lg text-[#2C2C2C] truncate">{{ $anak->name }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ $anak->kelas ? $anak->kelas->name : 'Belum ada kelas' }}
                                @if($anak->dob)<span class="ml-1 text-[#1A6B6B] font-bold text-xs">• {{ $anak->age }}</span>@endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-body-pad">
                    @if($anak->kesehatans->isEmpty())
                        <div class="text-center py-8 text-gray-400">
                            <svg class="h-12 w-12 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Belum ada catatan kesehatan untuk {{ $anak->name }}.
                        </div>
                    @else
                        <div class="space-y-6" data-tour="ortu-kesehatan-timeline">
                            @foreach($anak->kesehatans as $record)
                                <div class="relative pl-8 border-l-2 border-[#1A6B6B]/20 pb-6 last:pb-0">
                                    <div class="absolute -left-[9px] top-0 h-4 w-4 rounded-full border-2 border-white bg-[#1A6B6B]"></div>
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-3">
                                        <div class="text-sm font-bold text-[#1A6B6B] bg-[#D0E8E8] px-3 py-1 rounded-full w-fit">
                                            {{ \Carbon\Carbon::parse($record->tanggal_pemeriksaan)->translatedFormat('d F Y') }}
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4">
                                        <div class="p-2.5 sm:p-3 rounded-xl bg-white border border-gray-100 shadow-sm min-w-0">
                                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Berat Badan</p>
                                            <p class="font-bold text-[#2C2C2C]">{{ $record->berat_badan ?? '-' }} <span class="text-xs font-normal text-gray-500">kg</span></p>
                                        </div>
                                        <div class="p-2.5 sm:p-3 rounded-xl bg-white border border-gray-100 shadow-sm min-w-0">
                                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Tinggi Badan</p>
                                            <p class="font-bold text-[#2C2C2C]">{{ $record->tinggi_badan ?? '-' }} <span class="text-xs font-normal text-gray-500">cm</span></p>
                                        </div>
                                        <div class="p-2.5 sm:p-3 rounded-xl bg-white border border-gray-100 shadow-sm min-w-0">
                                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Lingkar Kepala</p>
                                            <p class="font-bold text-[#2C2C2C]">{{ $record->lingkar_kepala ?? '-' }} <span class="text-xs font-normal text-gray-500">cm</span></p>
                                        </div>
                                        <div class="p-2.5 sm:p-3 rounded-xl bg-white border border-gray-100 shadow-sm min-w-0 col-span-2 md:col-span-1">
                                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">Alergi</p>
                                            <p class="font-bold {{ $record->alergi ? 'text-red-600' : 'text-gray-400' }}">{{ $record->alergi ?: 'Tidak ada' }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2 sm:gap-3">
                                        @foreach(['gigi' => 'Gigi', 'telinga' => 'Telinga', 'kuku' => 'Kuku'] as $field => $label)
                                            <div class="flex items-center gap-2 sm:gap-3 p-2 sm:px-3 rounded-xl bg-gray-50 border border-gray-100 min-w-0 basis-full sm:basis-[calc(50%-0.25rem)] lg:flex-1 lg:min-w-[120px]">
                                                @php 
                                                    $val = $record->$field;
                                                    $isGood = Str::contains(strtolower($val), 'bersih') || Str::contains(strtolower($val), 'rapi');
                                                    $bg = $isGood ? 'bg-[#E8F5E9]' : 'bg-[#FFEBEE]';
                                                    $text = $isGood ? 'text-[#2E7D32]' : 'text-[#C62828]';
                                                    $letter = strtoupper(substr($field, 0, 1));
                                                @endphp
                                                <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full flex items-center justify-center font-bold text-xs sm:text-sm {{ $bg }} {{ $text }} border-2 border-white shadow-sm shrink-0">
                                                    {{ $letter }}
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-gray-400 font-bold uppercase">Kebersihan {{ $label }}</p>
                                                    <p class="text-sm font-bold text-[#2C2C2C]">{{ $val ?: '-' }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card card-body-pad text-center text-gray-400 py-10 sm:py-12">
                Data anak tidak ditemukan.
            </div>
        @endforelse
    </div>
</x-app-layout>
