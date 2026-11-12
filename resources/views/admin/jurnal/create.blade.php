<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Buat Jurnal Baru</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{
            lines: [{ akun_id: '', debit: '', kredit: '', keterangan: '' }, { akun_id: '', debit: '', kredit: '', keterangan: '' }],
            totalDebit() { return this.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0); },
            totalKredit() { return this.lines.reduce((s, l) => s + (parseFloat(l.kredit) || 0), 0); },
            selisih() { return Math.abs(this.totalDebit() - this.totalKredit()); },
            isBalanced() { return this.lines.length >= 2 && this.selisih() < 0.01 && this.totalDebit() > 0; },
            tambah() { this.lines.push({ akun_id: '', debit: '', kredit: '', keterangan: '' }); },
            hapus(i) { if (this.lines.length > 2) this.lines.splice(i, 1); },
            setDebit(i, val) { this.lines[i].debit = val; this.lines[i].kredit = ''; },
            setKredit(i, val) { this.lines[i].kredit = val; this.lines[i].debit = ''; },
         }">

        @if($errors->any())
            <div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ route('admin.jurnal.store') }}" method="POST">
            @csrf
            <div class="card mb-6">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Header Jurnal</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Tanggal</label>
                        <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="input-field">
                    </div>
                    <div class="md:col-span-2">
                        <label class="input-label">Deskripsi</label>
                        <input type="text" name="deskripsi" required placeholder="Deskripsi jurnal..." class="input-field" value="{{ old('deskripsi') }}">
                    </div>
                </div>
            </div>

            <div class="card mb-6">
                <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color:rgba(0,0,0,0.06);">
                    <div>
                        <h3 class="section-title">Detail Jurnal</h3>
                        <p class="section-subtitle">Minimal 2 baris, total debit = total kredit</p>
                    </div>
                    <button type="button" @click="tambah()" class="btn-secondary text-xs">
                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Baris
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table table-fixed w-full">
                        <thead>
                            <tr>
                                <th style="width:30%;">Akun</th>
                                <th class="text-right" style="width:22%;">Debit (Rp)</th>
                                <th class="text-right" style="width:22%;">Kredit (Rp)</th>
                                <th style="width:18%;">Keterangan</th>
                                <th style="width:8%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, i) in lines" :key="i">
                                <tr>
                                    <td class="pr-2">
                                        <select :name="`lines[${i}][akun_id]`" x-model="line.akun_id" required class="input-field text-sm w-full" style="min-width:150px;">
                                            <option value=""> Pilih Akun </option>
                                            @foreach($akuns as $jenis => $group)
                                                <optgroup label="{{ ucfirst($jenis) }}">
                                                    @foreach($group as $a)
                                                        <option value="{{ $a->id }}">{{ $a->kode }} - {{ $a->nama }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-1">
                                        <input type="number" :name="`lines[${i}][debit]`" x-model="line.debit" @input="setDebit(i, $event.target.value)" min="0" step="0.01" placeholder="0" class="input-field text-sm text-right w-full">
                                    </td>
                                    <td class="px-1">
                                        <input type="number" :name="`lines[${i}][kredit]`" x-model="line.kredit" @input="setKredit(i, $event.target.value)" min="0" step="0.01" placeholder="0" class="input-field text-sm text-right w-full">
                                    </td>
                                    <td class="pl-2">
                                        <input type="text" :name="`lines[${i}][keterangan]`" x-model="line.keterangan" placeholder="Ket..." class="input-field text-sm w-full">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" @click="hapus(i)" class="text-sm px-2 py-1 rounded" style="color:#C0392B;" title="Hapus baris">✕</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr style="background:#F5F2ED;">
                                <td class="font-bold text-sm" style="color:#2C2C2C;">Total</td>
                                <td class="text-right font-bold text-sm px-1" :class="selisih() < 0.01 && totalDebit() > 0 ? 'text-green-700' : 'text-red-700'">
                                    Rp <span x-text="totalDebit().toLocaleString('id-ID')"></span>
                                </td>
                                <td class="text-right font-bold text-sm px-1" :class="selisih() < 0.01 && totalKredit() > 0 ? 'text-green-700' : 'text-red-700'">
                                    Rp <span x-text="totalKredit().toLocaleString('id-ID')"></span>
                                </td>
                                <td class="pl-2">
                                    <span x-show="selisih() >= 0.01" class="text-xs font-semibold" style="color:#C0392B;">
                                        Selisih: Rp <span x-text="selisih().toLocaleString('id-ID')"></span>
                                    </span>
                                    <span x-show="selisih() < 0.01 && totalDebit() > 0" class="text-xs font-semibold" style="color:#1A6B6B;">Balance</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.jurnal.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary" :disabled="!isBalanced()">Simpan Jurnal</button>
            </div>
        </form>
    </div>
</x-app-layout>
