<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akuns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('kode', 20);
            $table->string('nama', 200);
            $table->enum('jenis', ['aset', 'liabilitas', 'ekuitas', 'pendapatan', 'beban']);
            $table->enum('kategori_arus_kas', ['operasi', 'investasi', 'pendanaan'])->nullable();
            $table->enum('saldo_normal', ['debit', 'kredit']);
            $table->foreignId('induk_id')->nullable()->constrained('akuns')->nullOnDelete();
            $table->boolean('is_aktif')->default(true);
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['sekolah_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akuns');
    }
};
