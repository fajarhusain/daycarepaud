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
        Schema::create('master_kegiatan_rutins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('pengajar_id')->constrained('pengajars')->cascadeOnDelete();
            $table->foreignId('matrikulasi_id')->nullable()->constrained('matrikulasis')->nullOnDelete();
            $table->string('nama_kegiatan');
            $table->string('aspek');
            $table->timestamps();
        });

        Schema::create('kelas_master_kegiatan_rutin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_kegiatan_rutin_id')->constrained('master_kegiatan_rutins')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_master_kegiatan_rutin');
        Schema::dropIfExists('master_kegiatan_rutins');
    }
};
