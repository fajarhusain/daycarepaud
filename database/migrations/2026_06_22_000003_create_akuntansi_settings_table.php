<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akuntansi_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete()->unique();

            $table->enum('metode_pencatatan', ['cash', 'accrual'])->default('cash');

            $table->foreignId('akun_kas_id')->constrained('akuns');
            $table->foreignId('akun_piutang_id')->nullable()->constrained('akuns');
            $table->foreignId('akun_pendapatan_id')->nullable()->constrained('akuns');

            $table->foreignId('akun_untuk_in')->constrained('akuns');
            $table->foreignId('akun_untuk_out')->constrained('akuns');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akuntansi_settings');
    }
};
