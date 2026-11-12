<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE akuns MODIFY jenis VARCHAR(50) NOT NULL');

        Schema::table('akuntansi_settings', function (Blueprint $table) {
            $table->json('jenis_akun_aset')->nullable()->after('akun_untuk_out');
        });
    }

    public function down(): void
    {
        Schema::table('akuntansi_settings', function (Blueprint $table) {
            $table->dropColumn('jenis_akun_aset');
        });

        DB::statement("ALTER TABLE akuns MODIFY jenis ENUM('aset','liabilitas','ekuitas','pendapatan','beban') NOT NULL");
    }
};
