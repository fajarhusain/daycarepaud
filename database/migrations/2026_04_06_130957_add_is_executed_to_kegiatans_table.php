<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tidak diperlukan kolom tambahan.
        // Kegiatan yang "sudah dilaksanakan" ditentukan dari keberadaan foto dokumentasi (photos).
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
