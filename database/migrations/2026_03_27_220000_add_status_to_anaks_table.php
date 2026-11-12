<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anaks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('photo');
            $table->text('catatan_ortu')->nullable()->after('status');
            $table->text('catatan_admin')->nullable()->after('catatan_ortu');
        });
    }

    public function down(): void
    {
        Schema::table('anaks', function (Blueprint $table) {
            $table->dropColumn(['status', 'catatan_ortu', 'catatan_admin']);
        });
    }
};
