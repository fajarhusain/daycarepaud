<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Referensi  role, lembaga/sekolah dasar, dan permission.
 *
 *   php artisan db:seed --class=Database\Seeders\ReferenceSeeder
 *
 * Risiko: PermissionSeeder memanggil syncPermissions()  reset hak akses role ke default.
 */
class ReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);
    }
}
