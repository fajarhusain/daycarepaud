<?php

use App\Models\MonevGuruKriteria;
use App\Models\Sekolah;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monev_guru_kriterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedTinyInteger('bobot')->default(1);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('monev_guru_evaluasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('pengajar_id')->constrained('pengajars')->cascadeOnDelete();
            $table->foreignId('evaluator_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul')->nullable();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->text('catatan_umum')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->unsignedTinyInteger('skor_keseluruhan')->nullable();
            $table->string('status', 10)->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });

        Schema::create('monev_guru_penilaian_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monev_guru_evaluasi_id')->constrained('monev_guru_evaluasis')->cascadeOnDelete();
            $table->foreignId('monev_guru_kriteria_id')->constrained('monev_guru_kriterias')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['monev_guru_evaluasi_id', 'monev_guru_kriteria_id'], 'monev_guru_penilaian_unique');
        });

        Sekolah::query()->pluck('id')->each(function (int $sekolahId) {
            MonevGuruKriteria::seedDefaultsForSekolah($sekolahId);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monev_guru_penilaian_items');
        Schema::dropIfExists('monev_guru_evaluasis');
        Schema::dropIfExists('monev_guru_kriterias');
    }
};
