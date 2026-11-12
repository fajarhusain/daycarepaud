<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah_ai_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->unique()->constrained('sekolahs')->cascadeOnDelete();
            $table->unsignedInteger('balance')->default(0);
            $table->timestamps();
        });

        Schema::create('sekolah_ai_token_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->integer('amount');
            $table->string('type', 32);
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sekolah_id', 'created_at']);
            $table->index('type');
        });

        Schema::create('sekolah_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->unique()->constrained('sekolahs')->cascadeOnDelete();
            $table->text('fallback_monev')->nullable();
            $table->text('fallback_pencapaian')->nullable();
            $table->text('fallback_chat')->nullable();
            $table->text('fallback_persona')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah_ai_settings');
        Schema::dropIfExists('sekolah_ai_token_transactions');
        Schema::dropIfExists('sekolah_ai_tokens');
    }
};
