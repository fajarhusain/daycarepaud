<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sumber_danas', function (Blueprint $table) {
            $table->foreignId('akun_id')->nullable()->after('nama')->constrained('akuns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sumber_danas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('akun_id');
        });
    }
};
