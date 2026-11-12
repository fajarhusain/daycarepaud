<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('monev_guru_kriterias', 'is_presensi')) {
            return;
        }

        Schema::table('monev_guru_kriterias', function (Blueprint $table) {
            $table->dropColumn('is_presensi');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('monev_guru_kriterias', 'is_presensi')) {
            return;
        }

        Schema::table('monev_guru_kriterias', function (Blueprint $table) {
            $table->boolean('is_presensi')->default(false)->after('is_active');
        });
    }
};
