<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Setup dev lengkap  pengganti behavior lama `php artisan db:seed`.
 *
 *   php artisan db:seed --class=Database\Seeders\DevSetupSeeder
 *
 * Diblokir di production.
 */
class DevSetupSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('DevSetupSeeder diblokir di production.');

            return;
        }

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AkunSeeder::class,
            SumberDanaSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
