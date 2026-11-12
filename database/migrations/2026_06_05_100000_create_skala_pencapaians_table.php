<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skala_pencapaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('label');
            $table->string('color', 7)->default('#EEEEEE');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sekolah_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skala_pencapaians');
    }
};
