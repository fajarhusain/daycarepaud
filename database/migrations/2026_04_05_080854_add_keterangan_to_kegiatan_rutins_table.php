<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kegiatan_rutins', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('status_pencapaian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_rutins', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
