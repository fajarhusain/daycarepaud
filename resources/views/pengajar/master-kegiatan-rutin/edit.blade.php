<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Edit Master Kegiatan</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <div class="mb-4">
            <a href="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.index') }}" class="btn-secondary text-sm">← Kegiatan Rutin</a>
        </div>

        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @php
            $aspekList = $matrikulasiList->pluck('aspek')->filter()->unique()->values();
            $oldAspek = old('aspek', $masterKegiatanRutin->aspek);
            $oldMatrikulasi = old('matrikulasi_id', $masterKegiatanRutin->matrikulasi_id);
        @endphp

        <div class="card overflow-hidden max-w-4xl" data-tour="kegiatan-rutin-form" x-data="{
                aspek: '{{ $oldAspek }}',
                allMatrikulasi: {{ $matrikulasiList->toJson() }},
                get filteredMatrikulasi() {
                    return this.aspek
                        ? this.allMatrikulasi.filter(m => m.aspek === this.aspek)
                        : this.allMatrikulasi;
                }
            }">
            <form action="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.update', $masterKegiatanRutin) }}" method="POST" class="card-pad space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Nama Kegiatan *</label>
                        <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $masterKegiatanRutin->nama_kegiatan) }}" class="input-field w-full" required>
                    </div>
                    <div>
                        <label class="input-label">Aspek Perkembangan *</label>
                        <select name="aspek" x-model="aspek" class="input-field w-full" required>
                            <option value="">— Pilih aspek —</option>
                            @foreach($aspekList as $asp)
                                <option value="{{ $asp }}" @selected($oldAspek == $asp)>{{ $asp }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="input-label">Indikator matrikulasi (opsional)</label>
                    <select name="matrikulasi_id" class="input-field w-full">
                        <option value="">— Tidak ada —</option>
                        <template x-for="m in filteredMatrikulasi" :key="m.id">
                            <option :value="m.id" x-text="m.indicator + ' — ' + m.description.substring(0, 50)" :selected="m.id == '{{ $oldMatrikulasi }}'"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="input-label">Peserta (kelas) *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mt-1">
                        @php $selectedKelasIds = old('kelas_ids', $masterKegiatanRutin->kelas->pluck('id')->toArray()); @endphp
                        @foreach($classList as $c)
                            <label class="flex items-center p-3 rounded-xl border cursor-pointer transition" style="border-color:rgba(0,0,0,0.08);">
                                <input type="checkbox" name="kelas_ids[]" value="{{ $c->id }}" class="h-4 w-4 rounded" style="accent-color:#1A6B6B;" @checked(is_array($selectedKelasIds) && in_array($c->id, $selectedKelasIds))>
                                <span class="ml-3 text-sm font-semibold" style="color:#2C2C2C;">{{ $c->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                    <a href="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
