<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StandardCoa
{
    /** Kode akun default untuk pengaturan akuntansi setelah seed. */
    public const DEFAULT_KAS = '1101';

    public const DEFAULT_PIUTANG = '1105';

    public const DEFAULT_PENDAPATAN = '4101';

    public const DEFAULT_COUNTER_IN = '4101';

    public const DEFAULT_COUNTER_OUT = '5101';

    /** Akun kas/bank untuk mutasi tabungan (sumber/tujuan). */
    public const KAS_BANK_KODES = ['1101', '1102', '1103', '1104'];

    public static function rows(): array
    {
        return require database_path('data/coa_standard.php');
    }

    /** @return list<string> */
    public static function kodes(): array
    {
        return array_column(self::rows(), 'kode');
    }

    public static function kategoriArusKas(string $jenis, string $kelompok): string
    {
        $kelompok = strtolower($kelompok);
        if (str_contains($kelompok, 'jangka panjang')) {
            return 'pendanaan';
        }
        if ($jenis === JenisAkun::MODAL) {
            return 'pendanaan';
        }
        if ($jenis === JenisAkun::ASSETS && str_contains($kelompok, 'tetap')) {
            return 'investasi';
        }

        return 'operasi';
    }

    /**
     * Hapus COA lama + transaksi akuntansi terkait, lalu seed COA standar untuk satu sekolah.
     *
     * @return array<string, int> kode => akun id
     */
    public static function replaceForSekolah(int $sekolahId): array
    {
        self::purgeAccountingForSekolah($sekolahId);

        return self::insertForSekolah($sekolahId);
    }

    public static function purgeAccountingForSekolah(int $sekolahId): void
    {
        DB::table('cashflows')->where('sekolah_id', $sekolahId)->update([
            'akun_id' => null,
            'akun_lawan_id' => null,
            'jurnal_id' => null,
        ]);

        if (Schema::hasTable('pembayaran_bulanans')) {
            DB::table('pembayaran_bulanans')->where('sekolah_id', $sekolahId)->update(['jurnal_id' => null]);
        }

        DB::table('jurnals')->where('sekolah_id', $sekolahId)->delete();

        if (Schema::hasTable('akuntansi_tabungan_akuns')) {
            DB::table('akuntansi_tabungan_akuns')->where('sekolah_id', $sekolahId)->delete();
        }

        DB::table('akuntansi_settings')->where('sekolah_id', $sekolahId)->delete();

        if (Schema::hasTable('kode_rekening_akun_mappings')) {
            DB::table('kode_rekening_akun_mappings')->where('sekolah_id', $sekolahId)->delete();
        }

        DB::table('akuns')->where('sekolah_id', $sekolahId)->update(['induk_id' => null]);
        DB::table('akuns')->where('sekolah_id', $sekolahId)->delete();
    }

    /**
     * @return array<string, int>
     */
    public static function insertForSekolah(int $sekolahId): array
    {
        $now = now();
        $byKode = [];

        foreach (self::rows() as $row) {
            $id = DB::table('akuns')->insertGetId([
                'sekolah_id' => $sekolahId,
                'tipe' => 'rkas',
                'kode' => $row['kode'],
                'nama' => $row['nama'],
                'snp' => $row['kelompok'],
                'komponen' => $row['subkelompok'],
                'uraian' => $row['uraian'],
                'jenis' => $row['jenis'],
                'kategori_arus_kas' => self::kategoriArusKas($row['jenis'], $row['kelompok']),
                'saldo_normal' => $row['saldo_normal'],
                'is_aktif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $byKode[$row['kode']] = $id;
        }

        self::insertDefaultSettings($sekolahId, $byKode);

        return $byKode;
    }

    /** @param array<string, int> $byKode */
    public static function insertDefaultSettings(int $sekolahId, array $byKode): void
    {
        $kas = $byKode[self::DEFAULT_KAS] ?? null;
        $in = $byKode[self::DEFAULT_COUNTER_IN] ?? null;
        $out = $byKode[self::DEFAULT_COUNTER_OUT] ?? null;

        if (! $kas || ! $in || ! $out) {
            return;
        }

        $now = now();
        DB::table('akuntansi_settings')->insert([
            'sekolah_id' => $sekolahId,
            'metode_pencatatan' => 'cash',
            'akun_kas_id' => $kas,
            'akun_piutang_id' => $byKode[self::DEFAULT_PIUTANG] ?? null,
            'akun_pendapatan_id' => $byKode[self::DEFAULT_PENDAPATAN] ?? null,
            'akun_untuk_in' => $in,
            'akun_untuk_out' => $out,
            'akun_beban_gaji_id' => $out,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
