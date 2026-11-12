<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monev_manual_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->nullable()->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');
            $table->foreignId('triggered_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('triggered_at');
            $table->timestamps();

            $table->unique(['sekolah_id', 'tahun', 'bulan'], 'monev_manual_triggers_sekolah_unique');
            $table->unique(['kelas_id', 'tahun', 'bulan'], 'monev_manual_triggers_kelas_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monev_manual_triggers');
    }
};
