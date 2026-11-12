<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('biaya_bulanan_siswas', 'biaya_harian')
            && ! Schema::hasColumn('biaya_bulanan_siswas', 'biaya_bulanan')) {
            Schema::table('biaya_bulanan_siswas', function (Blueprint $table) {
                $table->renameColumn('biaya_harian', 'biaya_bulanan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('biaya_bulanan_siswas', 'biaya_bulanan')
            && ! Schema::hasColumn('biaya_bulanan_siswas', 'biaya_harian')) {
            Schema::table('biaya_bulanan_siswas', function (Blueprint $table) {
                $table->renameColumn('biaya_bulanan', 'biaya_harian');
            });
        }
    }
};
