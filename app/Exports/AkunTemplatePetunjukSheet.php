<?php

namespace App\Exports;

use App\Support\JenisAkun;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AkunTemplatePetunjukSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function array(): array
    {
        $rows = [
            ['Petunjuk import kode rekening'],
            [],
            ['1. Isi data di sheet "Data Akun". Hapus baris contoh jika tidak dipakai.'],
            ['2. Kolom wajib: kode_akun, jenis, nama_akun, saldo_normal.'],
            ['3. Saldo awal: angka (0 jika tidak ada). Hanya untuk Aset, Liabilitas, Modal  bukan Pendapatan/Beban.'],
            ['4. Upload file lalu klik Periksa File di aplikasi sebelum Import.'],
            [],
            ['Nilai jenis yang valid:'],
        ];

        foreach (JenisAkun::ALL as $jenis) {
            $rows[] = [$jenis];
        }

        $rows[] = [];
        $rows[] = ['saldo_normal: debit atau kredit'];

        return $rows;
    }

    public function title(): string
    {
        return 'Petunjuk';
    }
}
