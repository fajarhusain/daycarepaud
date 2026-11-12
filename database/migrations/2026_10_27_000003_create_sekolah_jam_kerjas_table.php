<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadwal jam kerja per sekolah, satu baris per hari (ISO: 1=Senin ... 7=Minggu).
     * Dipakai untuk menentukan hari kerja, jam masuk/pulang, dan toleransi telat.
     */
    public function up(): void
    {
        Schema::create('sekolah_jam_kerjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->unsignedTinyInteger('hari'); // ISO 1=Senin ... 7=Minggu
            $table->boolean('is_kerja')->default(true);
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->unsignedSmallInteger('toleransi_masuk_menit')->default(0);
            $table->unsignedSmallInteger('toleransi_pulang_menit')->default(0);
            $table->timestamps();

            $table->unique(['sekolah_id', 'hari'], 'sekolah_jam_kerja_unik');
        });

        $now = now();

        DB::table('sekolahs')->pluck('id')->each(function (int $sekolahId) use ($now) {
            $rows = [];

            foreach (range(1, 7) as $hari) {
                $kerja = $hari <= 5; // Senin-Jumat kerja, Sabtu-Minggu libur

                $rows[] = [
                    'sekolah_id' => $sekolahId,
                    'hari' => $hari,
                    'is_kerja' => $kerja,
                    'jam_masuk' => $kerja ? '07:00:00' : null,
                    'jam_pulang' => $kerja ? '13:00:00' : null,
                    'toleransi_masuk_menit' => $kerja ? 10 : 0,
                    'toleransi_pulang_menit' => $kerja ? 10 : 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('sekolah_jam_kerjas')->insertOrIgnore($rows);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah_jam_kerjas');
    }
};
