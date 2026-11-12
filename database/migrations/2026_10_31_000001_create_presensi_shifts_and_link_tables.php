<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('name');
            $table->string('location_coordinate', 100)->nullable();
            $table->unsignedSmallInteger('radius_m')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['sekolah_id', 'is_default']);
        });

        Schema::table('sekolah_jam_kerjas', function (Blueprint $table) {
            $table->foreignId('presensi_shift_id')->nullable()->after('sekolah_id')->constrained('presensi_shifts')->cascadeOnDelete();
        });

        Schema::table('pengajars', function (Blueprint $table) {
            $table->foreignId('presensi_shift_id')->nullable()->after('sekolah_id')->constrained('presensi_shifts')->restrictOnDelete();
        });

        Schema::table('presensi_pengajars', function (Blueprint $table) {
            $table->foreignId('presensi_shift_id')->nullable()->after('pengajar_id')->constrained('presensi_shifts')->nullOnDelete();
        });

        $this->backfillShifts();

        Schema::table('sekolah_jam_kerjas', function (Blueprint $table) {
            $table->index('sekolah_id', 'sekolah_jam_kerjas_sekolah_id_index');
        });

        Schema::table('sekolah_jam_kerjas', function (Blueprint $table) {
            $table->dropUnique('sekolah_jam_kerja_unik');
        });

        Schema::table('sekolah_jam_kerjas', function (Blueprint $table) {
            $table->unsignedBigInteger('presensi_shift_id')->nullable(false)->change();
            $table->unique(['presensi_shift_id', 'hari'], 'sekolah_jam_kerja_shift_hari_unik');
        });

        Schema::table('pengajars', function (Blueprint $table) {
            $table->dropForeign(['presensi_shift_id']);
        });

        Schema::table('pengajars', function (Blueprint $table) {
            $table->unsignedBigInteger('presensi_shift_id')->nullable(false)->change();
            $table->foreign('presensi_shift_id')->references('id')->on('presensi_shifts')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('presensi_pengajars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presensi_shift_id');
        });

        Schema::table('pengajars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presensi_shift_id');
        });

        Schema::table('sekolah_jam_kerjas', function (Blueprint $table) {
            $table->dropUnique('sekolah_jam_kerja_shift_hari_unik');
            $table->dropConstrainedForeignId('presensi_shift_id');
            $table->unique(['sekolah_id', 'hari'], 'sekolah_jam_kerja_unik');
        });

        Schema::dropIfExists('presensi_shifts');
    }

    private function backfillShifts(): void
    {
        $now = now();

        foreach (DB::table('sekolahs')->orderBy('id')->get() as $sekolah) {
            $shiftId = DB::table('presensi_shifts')->insertGetId([
                'sekolah_id' => $sekolah->id,
                'name' => 'Reguler',
                'location_coordinate' => $sekolah->location_coordinate,
                'radius_m' => $sekolah->radius_m,
                'is_default' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $jamRows = DB::table('sekolah_jam_kerjas')
                ->where('sekolah_id', $sekolah->id)
                ->get();

            if ($jamRows->isEmpty()) {
                foreach (range(1, 7) as $hari) {
                    $kerja = $hari <= 5;
                    DB::table('sekolah_jam_kerjas')->insert([
                        'sekolah_id' => $sekolah->id,
                        'presensi_shift_id' => $shiftId,
                        'hari' => $hari,
                        'is_kerja' => $kerja,
                        'jam_masuk' => $kerja ? '07:00:00' : null,
                        'jam_pulang' => $kerja ? '13:00:00' : null,
                        'toleransi_masuk_menit' => $kerja ? 10 : 0,
                        'toleransi_pulang_menit' => $kerja ? 10 : 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            } else {
                DB::table('sekolah_jam_kerjas')
                    ->where('sekolah_id', $sekolah->id)
                    ->update(['presensi_shift_id' => $shiftId]);
            }

            DB::table('pengajars')
                ->where('sekolah_id', $sekolah->id)
                ->update(['presensi_shift_id' => $shiftId]);
        }
    }
};
