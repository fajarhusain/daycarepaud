<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Sekolah;
use App\Support\StandardCoa;
use Illuminate\Database\Seeder;

/**
 * Seed COA standar per sekolah.
 *
 *   php artisan db:seed --class=Database\\Seeders\\AkunSeeder
 */
class AkunSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Sekolah::all() as $sekolah) {
            if (Akun::where('sekolah_id', $sekolah->id)->exists()) {
                continue;
            }

            StandardCoa::insertForSekolah($sekolah->id);
        }
    }
}
