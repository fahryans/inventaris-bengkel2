<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeliharaan_alat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_unit_alat')
                ->constrained('unit_alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_teknisi')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->date('tanggal_cek');
            $table->date('tanggal_cek_berikutnya')->nullable();
            $table->string('kondisi')->nullable();
            $table->decimal('biaya', 15, 2)->nullable();
            $table->string('detail_biaya')->nullable();
            $table->text('catatan')->nullable();
            $table->text('hasil_pemeliharaan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeliharaan_alat');
    }
};
