<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembayaran_bulanans')) {
            return;
        }

        Schema::create('pembayaran_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolahs')->cascadeOnDelete();
            $table->foreignId('anak_id')->constrained('anaks')->cascadeOnDelete();
            $table->foreignId('biaya_bulanan_sekolah_id')->nullable()->constrained('biaya_bulanan_sekolahs')->nullOnDelete();
            $table->integer('periode_bulan');
            $table->integer('periode_tahun');
            $table->integer('hari_efektif')->default(0);
            $table->integer('hari_hadir')->default(0);
            $table->decimal('biaya_per_hari', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->foreignId('diskon_id')->nullable()->constrained('diskons')->nullOnDelete();
            $table->decimal('nilai_diskon', 12, 2)->default(0);
            $table->decimal('total_bayar', 12, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('bukti_transfer')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['anak_id', 'biaya_bulanan_sekolah_id', 'periode_bulan', 'periode_tahun'], 'pembayaran_bulanan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_bulanans');
    }
};
