<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan data check-in/check-out, koordinat GPS, dan status telat
     * pada presensi guru. Semua kolom nullable supaya data presensi lama
     * (input manual admin) tetap valid tanpa backfill.
     */
    public function up(): void
    {
        Schema::table('presensi_pengajars', function (Blueprint $table) {
            $table->timestamp('check_in_at')->nullable()->after('status');
            $table->timestamp('check_out_at')->nullable()->after('check_in_at');

            $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_out_at');
            $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_in_longitude');
            $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');

            $table->decimal('check_in_accuracy', 8, 2)->nullable()->after('check_out_longitude');
            $table->decimal('check_out_accuracy', 8, 2)->nullable()->after('check_in_accuracy');

            $table->decimal('jarak_meter', 8, 2)->nullable()->after('check_out_accuracy');
            $table->boolean('di_luar_area')->default(false)->after('jarak_meter');
            $table->string('sumber', 10)->nullable()->after('di_luar_area');

            $table->boolean('terlambat_masuk')->default(false)->after('sumber');
            $table->unsignedSmallInteger('menit_telat_masuk')->nullable()->after('terlambat_masuk');
            $table->boolean('telat_pulang')->default(false)->after('menit_telat_masuk');
            $table->unsignedSmallInteger('menit_telat_pulang')->nullable()->after('telat_pulang');

            $table->string('catatan', 255)->nullable()->after('menit_telat_pulang');
        });
    }

    public function down(): void
    {
        Schema::table('presensi_pengajars', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_at',
                'check_out_at',
                'check_in_latitude',
                'check_in_longitude',
                'check_out_latitude',
                'check_out_longitude',
                'check_in_accuracy',
                'check_out_accuracy',
                'jarak_meter',
                'di_luar_area',
                'sumber',
                'terlambat_masuk',
                'menit_telat_masuk',
                'telat_pulang',
                'menit_telat_pulang',
                'catatan',
            ]);
        });
    }
};
