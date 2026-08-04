<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kategori')
                ->constrained('kategori')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_labor')
                ->constrained('laboratorium')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('nama_bahan');
            // Kolom cache/agregat, disinkronkan otomatis dari SUM(stok_tersisa_batch)
            // di pengadaan_bahan setiap ada transaksi pengadaan/pemakaian.
            $table->unsignedInteger('stok_saat_ini')->default(0);
            $table->unsignedInteger('stok_minimum')->default(0);
            $table->string('satuan');
            $table->string('merek')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan');
    }
};
