<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar hari libur khusus per sekolah (tanggal merah, libur semester, dll).
     * Di tanggal libur, presensi guru ditutup.
     */
    public function up(): void
    {
        Schema::create('sekolah_hari_liburs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->unique(['sekolah_id', 'tanggal'], 'sekolah_hari_libur_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah_hari_liburs');
    }
};
