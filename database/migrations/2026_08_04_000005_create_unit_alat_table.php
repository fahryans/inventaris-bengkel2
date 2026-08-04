<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_alat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alat')
                ->constrained('alat')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('kode_inventaris')->unique();
            $table->string('kondisi_saat_ini')->nullable();
            $table->enum('status', ['tersedia', 'dipinjam', 'rusak', 'maintenance'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_alat');
    }
};
