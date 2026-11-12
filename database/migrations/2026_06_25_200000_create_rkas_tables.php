<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_rekenings', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->index();
            $table->enum('jenis', ['belanja', 'pendapatan'])->default('belanja');
            $table->string('snp')->nullable();
            $table->string('komponen')->nullable();
            $table->text('uraian');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('sumber_danas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('kode', 20);
            $table->string('nama');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->unique(['sekolah_id', 'kode']);
        });

        Schema::create('rkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('tahun_ajaran', 9);
            $table->unsignedTinyInteger('semester');
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['sekolah_id', 'tahun_ajaran', 'semester']);
        });

        Schema::create('rkas_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rkas_id')->constrained('rkas')->cascadeOnDelete();
            $table->foreignId('kode_rekening_id')->constrained('kode_rekenings')->cascadeOnDelete();
            $table->decimal('volume', 12, 2)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->decimal('harga_satuan', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['rkas_id', 'kode_rekening_id']);
        });

        Schema::create('rkas_line_anggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rkas_line_id')->constrained('rkas_lines')->cascadeOnDelete();
            $table->foreignId('sumber_dana_id')->constrained('sumber_danas')->cascadeOnDelete();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['rkas_line_id', 'sumber_dana_id']);
        });

        Schema::create('rkas_realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rkas_line_id')->constrained('rkas_lines')->cascadeOnDelete();
            $table->foreignId('sumber_dana_id')->constrained('sumber_danas')->cascadeOnDelete();
            $table->decimal('nominal_otomatis', 15, 2)->default(0);
            $table->decimal('nominal_manual', 15, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['rkas_line_id', 'sumber_dana_id']);
        });

        Schema::create('kode_rekening_akun_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('kode_rekening_id')->constrained('kode_rekenings')->cascadeOnDelete();
            $table->foreignId('akun_id')->constrained('akuns')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sekolah_id', 'akun_id']);
            $table->unique(['sekolah_id', 'kode_rekening_id']);
        });

        Schema::table('cashflows', function (Blueprint $table) {
            $table->foreignId('kode_rekening_id')->nullable()->after('akun_lawan_id')->constrained('kode_rekenings')->nullOnDelete();
            $table->foreignId('sumber_dana_id')->nullable()->after('kode_rekening_id')->constrained('sumber_danas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sumber_dana_id');
            $table->dropConstrainedForeignId('kode_rekening_id');
        });

        Schema::dropIfExists('kode_rekening_akun_mappings');
        Schema::dropIfExists('rkas_realisasis');
        Schema::dropIfExists('rkas_line_anggarans');
        Schema::dropIfExists('rkas_lines');
        Schema::dropIfExists('rkas');
        Schema::dropIfExists('sumber_danas');
        Schema::dropIfExists('kode_rekenings');
    }
};
