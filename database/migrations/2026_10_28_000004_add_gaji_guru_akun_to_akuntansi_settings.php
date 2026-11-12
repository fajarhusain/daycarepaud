<?php

use App\Models\Akun;
use App\Models\AkuntansiSetting;
use App\Support\StandardCoa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akuntansi_settings', function (Blueprint $table) {
            $table->foreignId('akun_beban_gaji_id')
                ->nullable()
                ->after('akun_untuk_out')
                ->constrained('akuns')
                ->restrictOnDelete();
            $table->foreignId('sumber_dana_gaji_id')
                ->nullable()
                ->after('akun_beban_gaji_id')
                ->constrained('sumber_danas')
                ->nullOnDelete();
        });

        AkuntansiSetting::query()->each(function (AkuntansiSetting $setting) {
            if ($setting->akun_beban_gaji_id) {
                return;
            }

            $bebanId = $setting->akun_untuk_out
                ?? Akun::query()
                    ->where('sekolah_id', $setting->sekolah_id)
                    ->where('kode', StandardCoa::DEFAULT_COUNTER_OUT)
                    ->value('id');

            if ($bebanId) {
                $setting->update(['akun_beban_gaji_id' => $bebanId]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('akuntansi_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sumber_dana_gaji_id');
            $table->dropConstrainedForeignId('akun_beban_gaji_id');
        });
    }
};
