<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE menu_makanans MODIFY menu TEXT NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite: recreate not automated here; VARCHAR default is OK for typical lengths
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE menu_makanans MODIFY menu VARCHAR(255) NOT NULL');
        }
    }
};
