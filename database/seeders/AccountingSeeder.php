<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Akuntansi  COA dan sumber dana default per sekolah yang ada.
 *
 *   php artisan db:seed --class=Database\Seeders\AccountingSeeder
 *
 * Relatif aman diulang (additive firstOrCreate).
 */
class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AkunSeeder::class,
            SumberDanaSeeder::class,
        ]);
    }
}
