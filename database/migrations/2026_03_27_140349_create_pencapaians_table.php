<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pencapaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matrikulasi_id')->constrained('matrikulasis')->cascadeOnDelete();
            $table->foreignId('pengajar_id')->constrained()->cascadeOnDelete();
            $table->text('feedback')->nullable();
            $table->integer('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pencapaians');
    }
};
