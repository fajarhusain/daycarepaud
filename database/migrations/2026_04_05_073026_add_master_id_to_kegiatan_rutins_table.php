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
            $table->foreignId('master_kegiatan_rutin_id')->nullable()->constrained('master_kegiatan_rutins')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_rutins', function (Blueprint $table) {
            $table->dropForeign(['master_kegiatan_rutin_id']);
            $table->dropColumn('master_kegiatan_rutin_id');
        });
    }
};
