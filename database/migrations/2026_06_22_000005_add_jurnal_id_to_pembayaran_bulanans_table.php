<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran_bulanans', function (Blueprint $table) {
            $table->foreignId('jurnal_id')->nullable()->after('approved_at')->constrained('jurnals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_bulanans', function (Blueprint $table) {
            $table->dropForeign(['jurnal_id']);
            $table->dropColumn('jurnal_id');
        });
    }
};
