<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah_ai_settings', function (Blueprint $table) {
            $table->boolean('chat_orangtua_enabled')->default(true)->after('sekolah_id');
        });
    }

    public function down(): void
    {
        Schema::table('sekolah_ai_settings', function (Blueprint $table) {
            $table->dropColumn('chat_orangtua_enabled');
        });
    }
};
