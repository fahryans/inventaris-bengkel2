<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kategori')
                ->constrained('kategori')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_labor')
                ->constrained('laboratorium')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('nama_alat');
            $table->string('merek')->nullable();
            $table->text('spesifikasi')->nullable();
            // 'unit'    = alat besar, dilacak per unit fisik lewat tabel unit_alat
            // 'agregat' = alat kecil, dilacak sebagai stok jumlah pada kolom jumlah_alat
            $table->enum('tipe_pelacakan', ['unit', 'agregat'])->default('agregat');
            $table->unsignedInteger('jumlah_alat')->default(0);
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alat');
    }
};
