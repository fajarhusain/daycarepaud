<?php

use App\Models\AkuntansiSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akuntansi_settings', function (Blueprint $table) {
            $table->foreignId('akun_kas_gaji_id')
                ->nullable()
                ->after('sumber_dana_gaji_id')
                ->constrained('akuns')
                ->restrictOnDelete();
        });

        AkuntansiSetting::query()->each(function (AkuntansiSetting $setting) {
            if ($setting->akun_kas_gaji_id || ! $setting->akun_kas_id) {
                return;
            }

            $setting->update(['akun_kas_gaji_id' => $setting->akun_kas_id]);
        });
    }

    public function down(): void
    {
        Schema::table('akuntansi_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('akun_kas_gaji_id');
        });
    }
};
