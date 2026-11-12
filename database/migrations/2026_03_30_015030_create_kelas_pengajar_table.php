<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kelas_pengajar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('pengajar_id')->constrained('pengajars')->cascadeOnDelete();
            $table->timestamps();
        });

        // Migrate existing data
        DB::statement('
            INSERT INTO kelas_pengajar (kelas_id, pengajar_id, created_at, updated_at)
            SELECT u.kelas_id, p.id, NOW(), NOW()
            FROM users u
            JOIN pengajars p ON p.user_id = u.id
            WHERE u.kelas_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_pengajar');
    }
};
