<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orangtua_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('sekolah_id')->constrained()->cascadeOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();

            $table->index(['sekolah_id', 'updated_at']);
        });

        Schema::create('orangtua_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orangtua_chat_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->timestamps();

            $table->index(['orangtua_chat_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orangtua_chat_messages');
        Schema::dropIfExists('orangtua_chats');
    }
};
