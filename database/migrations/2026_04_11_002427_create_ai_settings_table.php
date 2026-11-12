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
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lembaga_id')->unique();
            $table->foreign('lembaga_id')->references('id')->on('lembagas')->onDelete('cascade');
            $table->string('ai_provider')->default('sumopod');
            $table->text('ai_api_key')->nullable(); // stored encrypted
            $table->string('ai_model')->nullable()->default('deepseek/deepseek-r1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
