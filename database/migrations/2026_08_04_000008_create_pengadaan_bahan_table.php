<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengadaan_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bahan')
                ->constrained('bahan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_user_input')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->date('tanggal_pengadaan');
            $table->decimal('harga_perolehan', 15, 2);
            // Jumlah awal saat batch ini masuk (histori, tidak berubah)
            $table->unsignedInteger('jumlah');
            // Stok yang masih tersisa dari batch ini (berkurang tiap ada pemakaian_bahan)
            $table->unsignedInteger('stok_tersisa_batch')->default(0);
            $table->date('masa_expire_bahan')->nullable();
            $table->string('supplier')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('foto_transaksi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengadaan_bahan');
    }
};
