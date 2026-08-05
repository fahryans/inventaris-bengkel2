<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemakaian_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bahan')
                ->constrained('bahan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            // Batch spesifik yang diambil, biasanya dipilih otomatis oleh sistem
            // berdasarkan masa_expire_bahan terdekat (FIFO by expiry).
            $table->foreignId('id_pengadaan_bahan')
                ->constrained('pengadaan_bahan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_user_pemakai')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_user_verifikasi')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('keperluan');
            $table->unsignedInteger('jumlah_pengambilan');
            $table->unsignedInteger('jumlah_terpakai')->nullable();
            $table->unsignedInteger('jumlah_pengembalian')->nullable();
            $table->dateTime('waktu_pemakaian');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemakaian_bahan');
    }
};
