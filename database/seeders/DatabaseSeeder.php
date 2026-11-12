<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Default seeder  tidak mengubah data.
     *
     * Panggil seeder spesifik sesuai kebutuhan:
     *
     *   php artisan db:seed --class=Database\Seeders\ReferenceSeeder   (role + permission)
     *   php artisan db:seed --class=Database\Seeders\AccountingSeeder  (COA + sumber dana)
     *   php artisan db:seed --class=Database\Seeders\DemoSeeder        (data demo, local/staging)
     *   php artisan db:seed --class=Database\Seeders\DevSetupSeeder    (setup dev lengkap)
     */
    public function run(): void
    {
        $this->command->warn('DatabaseSeeder tidak menjalankan seeder apa pun (aman untuk DB real).');
        $this->command->line('');
        $this->command->line('Seeder yang tersedia:');
        $this->command->line('  ReferenceSeeder   role + permission');
        $this->command->line('  AccountingSeeder  COA + sumber dana per sekolah');
        $this->command->line('  DemoSeeder        data demo (local/staging saja)');
        $this->command->line('  DevSetupSeeder    setup dev lengkap (pengganti db:seed lama)');
        $this->command->line('');
        $this->command->line('Contoh: php artisan db:seed --class=Database\\Seeders\\DevSetupSeeder');
    }
}
