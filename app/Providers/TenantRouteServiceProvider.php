<?php

namespace App\Providers;

use App\Models\Anak;
use App\Models\BiayaBulananSekolah;
use App\Models\Diskon;
use App\Models\Kegiatan;
use App\Models\KegiatanRutin;
use App\Models\Kelas;
use App\Models\MasterKegiatanRutin;
use App\Models\Matrikulasi;
use App\Models\MenuMakanan;
use App\Models\MonevGuruEvaluasi;
use App\Models\PembayaranBulanan;
use App\Models\Pengajar;
use App\Models\Pengumuman;
use App\Models\Sarana;
use App\Models\SumberDana;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TenantRouteServiceProvider extends ServiceProvider
{
    /** @var array<string, class-string<Model>> */
    private array $tenantRouteBindings = [
        'anak' => Anak::class,
        'kelas' => Kelas::class,
        'kegiatan' => Kegiatan::class,
        'kegiatan_rutin' => KegiatanRutin::class,
        'master_kegiatan_rutin' => MasterKegiatanRutin::class,
        'matrikulasi' => Matrikulasi::class,
        'pengajar' => Pengajar::class,
        'menu_makanan' => MenuMakanan::class,
        'pengumuman' => Pengumuman::class,
        'sarana' => Sarana::class,
        'biayaBulanan' => BiayaBulananSekolah::class,
        'diskon' => Diskon::class,
        'pembayaran' => PembayaranBulanan::class,
        'sumber_dana' => SumberDana::class,
        'monev_guru_evaluasi' => MonevGuruEvaluasi::class,
    ];

    public function boot(): void
    {
        foreach ($this->tenantRouteBindings as $parameter => $modelClass) {
            Route::bind($parameter, function (string $value) use ($modelClass) {
                $query = $modelClass::withoutSekolahScope()->where(
                    (new $modelClass)->getRouteKeyName(),
                    $value
                );

                if (! TenantContext::isBypassed() && TenantContext::sekolahId() !== null) {
                    $query->where('sekolah_id', TenantContext::requireSekolahId());
                }

                return $query->firstOrFail();
            });
        }
    }
}
