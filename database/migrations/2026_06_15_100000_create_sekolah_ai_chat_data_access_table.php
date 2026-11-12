<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah_ai_chat_data_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sekolah_id')->unique();
            $table->foreign('sekolah_id')->references('id')->on('sekolahs')->onDelete('cascade');
            $table->boolean('access_monev')->default(true);
            $table->boolean('access_pencapaian')->default(true);
            $table->boolean('access_presensi')->default(true);
            $table->boolean('access_kesehatan')->default(true);
            $table->boolean('access_agenda')->default(true);
            $table->boolean('access_kegiatan_rutin')->default(true);
            $table->boolean('access_menu_makanan')->default(true);
            $table->boolean('include_tanggal')->default(true);
            $table->unsignedTinyInteger('agenda_days_back')->default(7);
            $table->unsignedTinyInteger('agenda_days_forward')->default(7);
            $table->unsignedTinyInteger('kegiatan_rutin_days_back')->default(7);
            $table->unsignedTinyInteger('kegiatan_rutin_days_forward')->default(7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah_ai_chat_data_access');
    }
};
