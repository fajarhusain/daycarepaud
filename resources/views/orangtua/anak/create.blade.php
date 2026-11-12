<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Tambah Anak</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto py-4 md:py-8 px-3 md:px-4 sm:px-6 lg:px-8">
        <div class="card overflow-hidden">
            <div class="card-pad border-b" style="border-color: rgba(0,0,0,0.06); background: #FAF6F0;">
                <h3 class="section-title">Daftarkan Anak Baru</h3>
                <p class="section-subtitle mt-1">
                    Data akan ditinjau Admin Sekolah sebelum anak aktif di sistem.
                </p>
            </div>

            @if($errors->any())
                <div class="card-pad pt-5 pb-0">
                    <div class="alert-danger">
                        <ul class="list-disc pl-5 text-sm">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('orangtua.anak.store') }}" enctype="multipart/form-data" class="card-pad space-y-5" data-tour="anak-create-form">
                @csrf

                <div>
                    <label class="input-label" for="sekolah_id">Sekolah</label>
                    <select id="sekolah_id" name="sekolah_id" class="input-field" required>
                        <option value="">-- Pilih sekolah --</option>
                        @foreach($sekolahs as $s)
                            <option value="{{ $s->id }}" @selected(old('sekolah_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('sekolah_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="input-label" for="name">Nama anak</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required class="input-field" placeholder="Nama sesuai akta / panggilan">
                    @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="input-label" for="dob">Tanggal lahir anak</label>
                    <input id="dob" type="date" name="dob" value="{{ old('dob') }}" required max="{{ now()->subDay()->format('Y-m-d') }}" class="input-field" onchange="updateAgePreviewOrtu(this.value)">
                    <p id="age-preview-ortu" class="text-[10px] font-bold text-[#1A6B6B] mt-1" style="display: none;"></p>
                    @error('dob')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="input-label" for="photo">Foto anak</label>
                    <input id="photo" type="file" name="photo" class="input-field py-2" accept="image/*">
                    @error('photo')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="input-label" for="catatan_ortu">Catatan (opsional)</label>
                    <textarea id="catatan_ortu" name="catatan_ortu" rows="2" class="input-field" placeholder="Mis. alergi, kebutuhan khusus…">{{ old('catatan_ortu') }}</textarea>
                    @error('catatan_ortu')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2" data-tour="anak-create-actions">
                    <button type="submit" class="btn-primary justify-center">Kirim pendaftaran anak</button>
                    <a href="{{ route('dashboard') }}" class="btn-secondary justify-center text-center">Batal</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateAgePreviewOrtu(dobString) {
            const preview = document.getElementById('age-preview-ortu');
            if (!dobString) {
                preview.style.display = 'none';
                return;
            }

            const birthDate = new Date(dobString);
            const today = new Date();

            let years = today.getFullYear() - birthDate.getFullYear();
            let months = today.getMonth() - birthDate.getMonth();

            if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) {
                years--;
                months += 12;
            }

            if (today.getDate() < birthDate.getDate()) {
                months--;
            }

            if (months < 0) {
                months += 12;
            }

            let text = '';
            if (years > 0) text += years + ' thn ';
            if (months > 0) text += months + ' bln';
            if (text === '') text = '0 bln';

            preview.textContent = 'Umur saat ini: ' + text.trim();
            preview.style.display = 'block';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const dob = document.getElementById('dob')?.value;
            if (dob) updateAgePreviewOrtu(dob);
        });
    </script>
    @endpush
</x-app-layout>
