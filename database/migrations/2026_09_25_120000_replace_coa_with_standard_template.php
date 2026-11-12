<?php

use App\Support\StandardCoa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ganti seluruh COA per sekolah dengan template standar (database/data/coa_standard.php).
 *
 * PERINGATAN: Menghapus jurnal, baris jurnal, dan melepaskan relasi akun di cashflow/pembayaran.
 * Jalankan backup database sebelum migrate di production.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $sekolahIds = DB::table('sekolahs')->pluck('id');

            foreach ($sekolahIds as $sekolahId) {
                StandardCoa::replaceForSekolah((int) $sekolahId);
            }
        });
    }

    public function down(): void
    {
        // Tidak dapat mengembalikan COA lama (RKAS/SYS.*) secara otomatis.
    }
};
