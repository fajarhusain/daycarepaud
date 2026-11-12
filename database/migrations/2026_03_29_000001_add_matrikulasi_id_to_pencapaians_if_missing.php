<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan matrikulasi_id jika tabel pencapaians dibuat dari skema lama tanpa kolom ini.
     */
    public function up(): void
    {
        if (! Schema::hasTable('pencapaians')) {
            return;
        }

        if (Schema::hasColumn('pencapaians', 'matrikulasi_id')) {
            return;
        }

        Schema::table('pencapaians', function (Blueprint $table) {
            $table->foreignId('matrikulasi_id')
                ->nullable()
                ->constrained('matrikulasis')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pencapaians') || ! Schema::hasColumn('pencapaians', 'matrikulasi_id')) {
            return;
        }

        Schema::table('pencapaians', function (Blueprint $table) {
            $table->dropForeign(['matrikulasi_id']);
            $table->dropColumn('matrikulasi_id');
        });
    }
};
