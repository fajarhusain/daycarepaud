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
        Schema::create('kegiatan_rutins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained()->cascadeOnDelete();
            $table->foreignId('anak_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pengajar_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('aspek'); // e.g., Agama, Kognitif, Motorik
            $table->string('kegiatan'); // e.g., Mengaji, Membaca
            $table->string('status_pencapaian'); // e.g., Sudah Lancar, Belum Lancar
            $table->timestamps();

            $table->index(['anak_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_rutins');
    }
};
