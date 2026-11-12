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
        Schema::create('presensi_pengajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pengajar_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->boolean('hadir')->default(true);
            $table->string('status')->nullable(); // e.g., Sakit, Izin, Alpa
            $table->timestamps();

            $table->unique(['pengajar_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_pengajars');
    }
};
