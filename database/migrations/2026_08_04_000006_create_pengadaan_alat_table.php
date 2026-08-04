<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengadaan_alat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alat')
                ->constrained('alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_user_input')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->date('tanggal_pengadaan');
            $table->decimal('harga_perolehan', 15, 2);
            $table->unsignedInteger('jumlah');
            $table->string('supplier')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('foto_transaksi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengadaan_alat');
    }
};
