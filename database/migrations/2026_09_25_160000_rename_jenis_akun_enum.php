<?php

use App\Support\JenisAkun;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('akuns')->orderBy('id')->select('id', 'jenis')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $jenis = JenisAkun::normalize((string) $row->jenis);
                if (! in_array($jenis, JenisAkun::ALL, true)) {
                    $jenis = JenisAkun::ASSETS;
                }
                if ($jenis !== $row->jenis) {
                    DB::table('akuns')->where('id', $row->id)->update(['jenis' => $jenis]);
                }
            }
        });

        foreach (DB::table('akuntansi_settings')->get() as $setting) {
            $list = json_decode($setting->jenis_akun_aset ?? 'null', true);
            if (! is_array($list)) {
                continue;
            }
            $next = [];
            foreach ($list as $item) {
                $jenis = JenisAkun::normalize((string) $item);
                if (in_array($jenis, JenisAkun::ALL, true)) {
                    $next[] = $jenis;
                }
            }
            $next = array_values(array_unique($next));
            DB::table('akuntansi_settings')->where('id', $setting->id)->update([
                'jenis_akun_aset' => json_encode($next !== [] ? $next : [JenisAkun::ASSETS]),
            ]);
        }

        $enum = implode("','", JenisAkun::ALL);
        DB::statement("ALTER TABLE akuns MODIFY jenis ENUM('{$enum}') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE akuns MODIFY jenis VARCHAR(50) NOT NULL");
    }
};
