<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_bulanan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_bulanan_id')->constrained('pembayaran_bulanans')->cascadeOnDelete();
            $table->string('nama_item');
            $table->decimal('jumlah', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_bulanan_items');
    }
};
