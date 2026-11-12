<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Radius geofence presensi guru per sekolah (meter).
     * Nullable: kalau kosong, service memakai default 100 meter.
     */
    public function up(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->unsignedSmallInteger('radius_m')->nullable()->after('location_coordinate');
        });
    }

    public function down(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->dropColumn('radius_m');
        });
    }
};
