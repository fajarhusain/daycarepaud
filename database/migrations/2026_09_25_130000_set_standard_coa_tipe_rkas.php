<?php

use App\Support\StandardCoa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * COA template memakai tipe rkas (sama seperti tambah manual di Kode Rekening).
 */
return new class extends Migration
{
    public function up(): void
    {
        $kodes = StandardCoa::kodes();

        if ($kodes === []) {
            return;
        }

        DB::table('akuns')
            ->whereIn('kode', $kodes)
            ->update(['tipe' => 'rkas', 'updated_at' => now()]);
    }

    public function down(): void
    {
        $kodes = StandardCoa::kodes();

        if ($kodes === []) {
            return;
        }

        DB::table('akuns')
            ->whereIn('kode', $kodes)
            ->update(['tipe' => 'sistem', 'updated_at' => now()]);
    }
};
