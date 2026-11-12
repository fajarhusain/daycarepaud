<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('biaya_bulanan_siswas')) {
            Schema::create('biaya_bulanan_siswas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
                $table->foreignId('anak_id')->constrained('anaks')->cascadeOnDelete();
                $table->foreignId('biaya_bulanan_sekolah_id')->constrained('biaya_bulanan_sekolahs')->cascadeOnDelete();
                $table->decimal('biaya_harian', 12, 2)->default(0);
                $table->timestamps();

                $table->unique(['anak_id', 'biaya_bulanan_sekolah_id']);
            });

            return;
        }

        if (Schema::hasColumn('biaya_bulanan_siswas', 'nominal_override')
            && ! Schema::hasColumn('biaya_bulanan_siswas', 'biaya_harian')) {
            Schema::table('biaya_bulanan_siswas', function (Blueprint $table) {
                $table->renameColumn('nominal_override', 'biaya_harian');
            });
        }

        if (Schema::hasTable('biaya_bulanan_kelas')) {
            Schema::drop('biaya_bulanan_kelas');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('biaya_bulanan_siswas')) {
            return;
        }

        if (Schema::hasColumn('biaya_bulanan_siswas', 'biaya_harian')
            && ! Schema::hasColumn('biaya_bulanan_siswas', 'nominal_override')) {
            Schema::table('biaya_bulanan_siswas', function (Blueprint $table) {
                $table->renameColumn('biaya_harian', 'nominal_override');
            });

            return;
        }

        Schema::dropIfExists('biaya_bulanan_siswas');
    }
};
