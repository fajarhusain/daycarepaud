<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('judul');
            $table->string('kategori');
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->date('mulai_tayang');
            $table->date('selesai_tayang');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pengumuman_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumuman_id')->constrained('pengumumans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->unique(['pengumuman_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman_reads');
        Schema::dropIfExists('pengumumans');
    }
};
