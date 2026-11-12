<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akuntansi_tabungan_akuns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained()->cascadeOnDelete();
            $table->foreignId('akun_id')->constrained('akuns')->cascadeOnDelete();
            $table->unique(['sekolah_id', 'akun_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akuntansi_tabungan_akuns');
    }
};
