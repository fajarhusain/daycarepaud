<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah_ai_personas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sekolah_id')->unique();
            $table->foreign('sekolah_id')->references('id')->on('sekolahs')->onDelete('cascade');
            $table->string('assistant_name')->default('Asisten PAUD');
            $table->text('personality')->nullable();
            $table->text('communication_style')->nullable();
            $table->text('greeting_style')->nullable();
            $table->text('boundaries')->nullable();
            $table->text('custom_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('ai_generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah_ai_personas');
    }
};
