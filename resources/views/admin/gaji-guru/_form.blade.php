@php
    $gajiModel = $gaji ?? null;
    $initialKomponen = old('komponen', $gajiModel?->komponen ?? $komponenDefaults);
@endphp
<div class="space-y-6" x-data="{
    rows: @js($initialKomponen),
    addRow() { this.rows.push({ nama: '', jenis: 'tambah', nominal: 0 }); },
    removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); },
    total() {
        return this.rows.reduce((sum, r) => {
            const n = parseFloat(r.nominal) || 0;
            return sum + (r.jenis === 'potong' ? -Math.abs(n) : n);
        }, 0);
    },
    formatRp(v) {
        const n = Math.max(0, Math.round(v));
        return 'Rp ' + n.toLocaleString('id-ID');
    }
}">
    <div class="rounded-lg px-4 py-3 text-sm" style="background:#F0F7F7;color:#2C2C2C;">
        Akun kas, beban, dan sumber dana gaji diatur di
        <a href="{{ route('admin.akuntansi-setting.index') }}" class="font-semibold underline" style="color:#1A6B6B;">Pengaturan Akuntansi → Akun Gaji Guru</a>.
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="input-label">Guru <span class="text-red-600">*</span></label>
            <select name="pengajar_id" required class="input-field">
                <option value="">— Pilih —</option>
                @foreach($pengajars as $p)
                    <option value="{{ $p->id }}" @selected(old('pengajar_id', $gajiModel?->pengajar_id) == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="input-label">Bulan <span class="text-red-600">*</span></label>
                <select name="periode_bulan" required class="input-field">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int) old('periode_bulan', $gajiModel?->periode_bulan ?? now()->format('n')) === $m)>{{ sprintf('%02d', $m) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="input-label">Tahun <span class="text-red-600">*</span></label>
                <input type="number" name="periode_tahun" min="2000" max="2100" required class="input-field"
                    value="{{ old('periode_tahun', $gajiModel?->periode_tahun ?? now()->format('Y')) }}">
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
            <h3 class="section-title">Komponen gaji</h3>
            <button type="button" @click="addRow()" class="btn-secondary text-sm">+ Baris</button>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="data-table w-full">
                <thead><tr><th>Nama</th><th>Jenis</th><th>Nominal</th><th></th></tr></thead>
                <tbody>
                    <template x-for="(row, i) in rows" :key="i">
                        <tr>
                            <td><input type="text" class="input-field" x-model="row.nama" :name="'komponen['+i+'][nama]'" required></td>
                            <td>
                                <select class="input-field" x-model="row.jenis" :name="'komponen['+i+'][jenis]'">
                                    <option value="tambah">Tambah</option>
                                    <option value="potong">Potong</option>
                                </select>
                            </td>
                            <td><input type="number" min="0" step="1" class="input-field" x-model="row.nominal" :name="'komponen['+i+'][nominal]'" required></td>
                            <td><button type="button" @click="removeRow(i)" class="text-xs text-red-700">Hapus</button></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="mt-4 text-right font-semibold" style="color:#1A6B6B;" x-text="formatRp(total())"></p>
        </div>
    </div>

    <div>
        <label class="input-label">Catatan</label>
        <textarea name="catatan" rows="3" class="input-field">{{ old('catatan', $gajiModel?->catatan) }}</textarea>
    </div>
</div>
