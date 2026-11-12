<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pencapaians', function (Blueprint $table) {
            $table->dropColumn('score');
        });

        Schema::table('pencapaians', function (Blueprint $table) {
            $table->string('score', 50)->nullable()->after('feedback');
        });
    }

    public function down(): void
    {
        Schema::table('pencapaians', function (Blueprint $table) {
            $table->dropColumn('score');
        });

        Schema::table('pencapaians', function (Blueprint $table) {
            $table->integer('score')->nullable()->after('feedback');
        });
    }
};
