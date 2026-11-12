<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembayaran_bulanan_details')) {
            return;
        }

        Schema::create('pembayaran_bulanan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_bulanan_id')->constrained('pembayaran_bulanans')->cascadeOnDelete();
            $table->string('field_name');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_bulanan_details');
    }
};
