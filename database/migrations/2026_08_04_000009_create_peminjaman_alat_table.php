<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_alat', function (Blueprint $table) {
            $table->id();
            // Diisi salah satu sesuai tipe_pelacakan pada alat terkait:
            // id_alat untuk alat agregat (pakai kolom jumlah),
            // id_unit_alat untuk alat unit (jumlah selalu 1).
            $table->foreignId('id_alat')
                ->nullable()
                ->constrained('alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_unit_alat')
                ->nullable()
                ->constrained('unit_alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_user_peminjam')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('keperluan');
            $table->dateTime('waktu_peminjaman');
            $table->dateTime('waktu_pengembalian')->nullable();
            $table->dateTime('waktu_kembali_aktual')->nullable();
            $table->unsignedInteger('jumlah')->default(1);
            $table->string('kondisi_saat_peminjaman')->nullable();
            $table->string('kondisi_saat_pengembalian')->nullable();
            $table->enum('status', ['terpinjam', 'sudah_dikembalikan'])->default('terpinjam');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_alat');
    }
};
