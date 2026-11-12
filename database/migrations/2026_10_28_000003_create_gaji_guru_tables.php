<?php

use App\Models\KomponenGaji;
use App\Models\Sekolah;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komponen_gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('nama');
            $table->string('jenis', 10);
            $table->decimal('nominal_default', 12, 2)->default(0);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('gaji_pengajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('pengajar_id')->constrained('pengajars')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->json('komponen');
            $table->decimal('total_gaji', 12, 2)->default(0);
            $table->foreignId('akun_kas_id')->constrained('akuns')->restrictOnDelete();
            $table->foreignId('sumber_dana_id')->nullable()->constrained('sumber_danas')->nullOnDelete();
            $table->foreignId('akun_beban_id')->constrained('akuns')->restrictOnDelete();
            $table->string('status', 10)->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('jurnal_id')->nullable()->constrained('jurnals')->nullOnDelete();
            $table->foreignId('cashflow_id')->nullable()->constrained('cashflows')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pengajar_id', 'periode_bulan', 'periode_tahun'], 'gaji_pengajar_periode_unique');
        });

        Sekolah::query()->pluck('id')->each(function (int $sekolahId) {
            KomponenGaji::seedDefaultsForSekolah($sekolahId);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaji_pengajars');
        Schema::dropIfExists('komponen_gajis');
    }
};
