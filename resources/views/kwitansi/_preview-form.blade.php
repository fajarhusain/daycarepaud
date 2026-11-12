@php $prefix = $prefix ?? ''; @endphp
<div class="space-y-4 text-sm">
    <input type="hidden" name="jenis" :value="kwitansiJenis">

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="input-label">Tahun Anggaran</label>
            <input type="text" name="tahun_anggaran" x-model="kwitansiData.tahun_anggaran" class="input-field">
        </div>
        <div>
            <label class="input-label">Nomor Bukti</label>
            <input type="text" name="nomor_bukti" x-model="kwitansiData.nomor_bukti" class="input-field">
        </div>
    </div>

    <div>
        <label class="input-label">Sudah terima dari</label>
        <input type="text" name="sudah_terima_dari" x-model="kwitansiData.sudah_terima_dari" class="input-field">
    </div>

    <template x-if="kwitansiJenis === 'pembayaran'">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="input-label">Nama RA</label>
                <input type="text" name="nama_ra" x-model="kwitansiData.nama_ra" class="input-field">
            </div>
            <div>
                <label class="input-label">Desa/Kecamatan</label>
                <input type="text" name="desa_kecamatan" x-model="kwitansiData.desa_kecamatan" class="input-field">
            </div>
            <div>
                <label class="input-label">Kabupaten</label>
                <input type="text" name="kabupaten" x-model="kwitansiData.kabupaten" class="input-field">
            </div>
            <div class="col-span-2">
                <label class="input-label">Provinsi</label>
                <input type="text" name="provinsi" x-model="kwitansiData.provinsi" class="input-field">
            </div>
        </div>
    </template>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="input-label">Jumlah Uang (Rp)</label>
            <input type="number" name="jumlah_uang" x-model="kwitansiData.jumlah_uang" min="0" step="1" class="input-field">
        </div>
        <template x-if="kwitansiJenis === 'pembayaran'">
            <div>
                <label class="input-label">Tanggal Lunas</label>
                <input type="text" name="tanggal_lunas" x-model="kwitansiData.tanggal_lunas" class="input-field" placeholder="contoh: 25 Juni 2026">
            </div>
        </template>
    </div>

    <div>
        <label class="input-label">Terbilang</label>
        <textarea name="terbilang" x-model="kwitansiData.terbilang" rows="2" class="input-field"></textarea>
    </div>

    <div>
        <label class="input-label" x-text="kwitansiJenis === 'penerimaan' ? 'Untuk penerimaan' : 'Untuk pembayaran'"></label>
        <textarea name="untuk_pembayaran" x-model="kwitansiData.untuk_pembayaran" rows="3" class="input-field"></textarea>
    </div>

    <template x-if="kwitansiJenis === 'pembayaran'">
        <div class="space-y-4">
            <div>
                <label class="input-label">Sumber dana</label>
                <input type="text" name="sumber_dana" x-model="kwitansiData.sumber_dana" class="input-field">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="input-label">Periode awal</label>
                    <input type="text" name="sumber_dana_periode_awal" x-model="kwitansiData.sumber_dana_periode_awal" class="input-field">
                </div>
                <div>
                    <label class="input-label">Periode akhir</label>
                    <input type="text" name="sumber_dana_periode_akhir" x-model="kwitansiData.sumber_dana_periode_akhir" class="input-field">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="input-label">Penerima Uang</label>
                    <input type="text" name="penerima_nama" x-model="kwitansiData.penerima_nama" class="input-field">
                </div>
                <div>
                    <label class="input-label">Kepala RA</label>
                    <input type="text" name="kepala_ra_nama" x-model="kwitansiData.kepala_ra_nama" class="input-field">
                </div>
                <div>
                    <label class="input-label">Bendahara RA</label>
                    <input type="text" name="bendahara_nama" x-model="kwitansiData.bendahara_nama" class="input-field">
                </div>
            </div>
        </div>
    </template>

    <template x-if="kwitansiJenis === 'penerimaan'">
        <div class="space-y-4">
            <div>
                <label class="input-label">Tempat, tanggal</label>
                <input type="text" name="tempat_tanggal" x-model="kwitansiData.tempat_tanggal" class="input-field">
            </div>
            <div>
                <label class="input-label">Kepala RA (nama jelas)</label>
                <input type="text" name="kepala_ra_nama" x-model="kwitansiData.kepala_ra_nama" class="input-field">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="input-label">PPK (nama jelas)</label>
                    <input type="text" name="ppk_nama" x-model="kwitansiData.ppk_nama" class="input-field">
                </div>
                <div>
                    <label class="input-label">NIP PPK</label>
                    <input type="text" name="ppk_nip" x-model="kwitansiData.ppk_nip" class="input-field">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="input-label">SK PPK No.</label>
                    <input type="text" name="sk_ppk_nomor" x-model="kwitansiData.sk_ppk_nomor" class="input-field">
                </div>
                <div>
                    <label class="input-label">SK PPK Tanggal</label>
                    <input type="text" name="sk_ppk_tanggal" x-model="kwitansiData.sk_ppk_tanggal" class="input-field">
                </div>
            </div>
        </div>
    </template>
</div>
