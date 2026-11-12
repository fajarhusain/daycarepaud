<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Pengaturan Akuntansi</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <x-settings-tabs />
        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('admin.akuntansi-setting.update') }}" method="POST" data-tour="admin-akuntansi-setting-form">
            @csrf @method('PUT')

            <div class="card mb-6">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Jenis di Akun Aset (Catat Transaksi)</h3>
                    <p class="section-subtitle">Hanya jenis yang dicentang yang muncul di kolom Akun Aset saat mencatat cashflow. Kosongkan centang tidak bisa  minimal satu.</p>
                </div>
                <div class="p-6 flex flex-wrap gap-3">
                    @foreach($jenisOptions as $jenis)
                        <label class="flex items-center gap-2 text-sm border rounded-lg px-3 py-2" style="border-color:rgba(0,0,0,0.08);">
                            <input type="checkbox" name="jenis_akun_aset[]" value="{{ $jenis }}" @checked(in_array($jenis, $jenisAkunAset, true))>
                            {{ $jenis }}
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Section 2: Default Akun Cashflow -->
            <div class="card mb-6">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Default Akun Cashflow Manual</h3>
                    <p class="section-subtitle">Akun yang digunakan saat mencatat transaksi di menu Cashflow. Jurnal double-entry dibuat otomatis.</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Akun Kas</label>
                        <select name="akun_kas_id" class="input-field">
                            @foreach($akunAset as $a)
                                <option value="{{ $a->id }}" {{ $setting->akun_kas_id == $a->id ? 'selected' : '' }}>
                                    {{ $a->kode }} - {{ $a->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs mt-1" style="color:#9E9790;">Akun yang didebit/kredit saat catat cashflow</p>
                    </div>
                    <div>
                        <label class="input-label">Counter Pemasukan</label>
                        <select name="akun_untuk_in" class="input-field">
                            @foreach($akunPendapatan as $a)
                                <option value="{{ $a->id }}" {{ $setting->akun_untuk_in == $a->id ? 'selected' : '' }}>
                                    {{ $a->kode }} - {{ $a->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs mt-1" style="color:#9E9790;">Akun counter saat catat cashflow masuk</p>
                    </div>
                    <div>
                        <label class="input-label">Counter Pengeluaran</label>
                        <select name="akun_untuk_out" class="input-field">
                            @foreach($akunBeban as $a)
                                <option value="{{ $a->id }}" {{ $setting->akun_untuk_out == $a->id ? 'selected' : '' }}>
                                    {{ $a->kode }} - {{ $a->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs mt-1" style="color:#9E9790;">Default cashflow keluar (mis. Beban ATK). <strong>Bukan</strong> akun slip gaji — atur di Akun Beban Gaji di bawah.</p>
                    </div>
                </div>
            </div>

            <!-- SPP / Tagihan Bulanan -->
            <div class="card mb-6">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Akun SPP (Tagihan Bulanan)</h3>
                    <p class="section-subtitle">Dipakai otomatis saat generate tagihan dan saat pembayaran disetujui (lunas).</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Metode pencatatan SPP</label>
                        <select name="metode_pencatatan" class="input-field" required>
                            <option value="cash" @selected($setting->metode_pencatatan === 'cash')>Tunai — Pendapatan saat lunas</option>
                            <option value="accrual" @selected($setting->metode_pencatatan === 'accrual')>Piutang — Belum lunas tercatat</option>
                        </select>
                    </div>
                    <div>
                        <label class="input-label">Akun piutang SPP</label>
                        <select name="akun_piutang_id" class="input-field" required>
                            @foreach($akunPiutangOptions as $a)
                                <option value="{{ $a->id }}" @selected($setting->akun_piutang_id == $a->id)>{{ $a->kode }} - {{ $a->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="input-label">Akun pendapatan SPP</label>
                        <select name="akun_pendapatan_id" class="input-field" required>
                            @foreach($akunPendapatan as $a)
                                <option value="{{ $a->id }}" @selected($setting->akun_pendapatan_id == $a->id)>{{ $a->kode }} - {{ $a->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card mb-6">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Akun Gaji Guru</h3>
                    <p class="section-subtitle">Dipakai otomatis saat slip gaji disetujui (debit beban, kredit kas). Tidak perlu diisi lagi di form slip.</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Akun Kas/Bank Gaji</label>
                        <select name="akun_kas_gaji_id" class="input-field" required>
                            @foreach($akunAset as $a)
                                <option value="{{ $a->id }}" @selected($setting->akun_kas_gaji_id == $a->id)>{{ $a->kode }} - {{ $a->nama }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs mt-1" style="color:#9E9790;">Dikredit saat slip disetujui. Boleh beda dari Akun Kas cashflow di atas.</p>
                    </div>
                    <div>
                        <label class="input-label">Akun Beban Gaji</label>
                        <select name="akun_beban_gaji_id" class="input-field" required>
                            @foreach($akunBeban as $a)
                                <option value="{{ $a->id }}" @selected($setting->akun_beban_gaji_id == $a->id)>{{ $a->kode }} - {{ $a->nama }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs mt-1" style="color:#9E9790;">Didebit saat slip disetujui; bukan Counter Pengeluaran cashflow (ATK).</p>
                    </div>
                    <div>
                        <label class="input-label">Sumber Dana</label>
                        <select name="sumber_dana_gaji_id" class="input-field">
                            <option value="">— Opsional —</option>
                            @foreach($sumberDanas as $sd)
                                <option value="{{ $sd->id }}" @selected($setting->sumber_dana_gaji_id == $sd->id)>{{ $sd->nama }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs mt-1" style="color:#9E9790;">Opsional; tercatat di cashflow gaji. Isi master Sumber Dana jika kosong.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary px-8">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</x-app-layout>
