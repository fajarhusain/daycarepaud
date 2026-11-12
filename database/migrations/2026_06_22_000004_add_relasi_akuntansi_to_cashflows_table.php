<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->foreignId('akun_id')->nullable()->after('sekolah_id')->constrained('akuns')->nullOnDelete();
            $table->foreignId('jurnal_id')->nullable()->after('amount')->constrained('jurnals')->nullOnDelete();
            $table->nullableMorphs('reference');
        });
    }

    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropMorphs('reference');
            $table->dropForeign(['jurnal_id']);
            $table->dropColumn('jurnal_id');
            $table->dropForeign(['akun_id']);
            $table->dropColumn('akun_id');
        });
    }
};
