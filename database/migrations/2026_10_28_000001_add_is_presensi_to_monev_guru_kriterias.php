<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monev_guru_kriterias', function (Blueprint $table) {
            $table->boolean('is_presensi')->default(false)->after('is_active');
        });

        DB::table('monev_guru_kriterias')
            ->where('nama', 'Kedisiplinan & kehadiran')
            ->update(['is_presensi' => true]);
    }

    public function down(): void
    {
        Schema::table('monev_guru_kriterias', function (Blueprint $table) {
            $table->dropColumn('is_presensi');
        });
    }
};
