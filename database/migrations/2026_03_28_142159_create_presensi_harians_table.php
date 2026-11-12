<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add a status string column for 4-state attendance on the existing table
        Schema::table('presensis', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->after('hadir'); // hadir|izin|sakit|alpha
            $table->foreignId('kelas_id')->nullable()->after('sekolah_id')->constrained('kelas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn(['status', 'kelas_id']);
        });
    }
};
