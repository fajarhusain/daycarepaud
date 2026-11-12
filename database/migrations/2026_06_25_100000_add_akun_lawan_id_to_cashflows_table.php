<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->foreignId('akun_lawan_id')->nullable()->after('akun_id')->constrained('akuns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropForeign(['akun_lawan_id']);
            $table->dropColumn('akun_lawan_id');
        });
    }
};
