<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_provider_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lembaga_id');
            $table->foreign('lembaga_id')->references('id')->on('lembagas')->onDelete('cascade');
            $table->unsignedTinyInteger('slot');
            $table->string('ai_provider')->default('sumopod');
            $table->text('ai_api_key')->nullable();
            $table->string('ai_model')->nullable();
            $table->string('ai_base_url', 500)->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['lembaga_id', 'slot']);
        });

        if (Schema::hasTable('ai_settings')) {
            $rows = DB::table('ai_settings')->get();
            foreach ($rows as $row) {
                DB::table('ai_provider_slots')->insert([
                    'lembaga_id' => $row->lembaga_id,
                    'slot' => 1,
                    'ai_provider' => $row->ai_provider ?? 'sumopod',
                    'ai_api_key' => $row->ai_api_key,
                    'ai_model' => $row->ai_model,
                    'ai_base_url' => $row->ai_base_url ?? null,
                    'is_enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_slots');
    }
};
