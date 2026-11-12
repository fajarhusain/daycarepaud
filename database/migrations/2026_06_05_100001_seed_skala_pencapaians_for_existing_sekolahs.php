<?php

use App\Models\Sekolah;
use App\Models\SkalaPencapaian;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Sekolah::query()->pluck('id')->each(function (int $sekolahId) {
            SkalaPencapaian::seedDefaultsForSekolah($sekolahId);
        });
    }

    public function down(): void
    {
        // Data seed; tidak di-rollback agar tidak menghapus kustomisasi admin.
    }
};
