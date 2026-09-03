<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat tabel spesifikasi_bahan
        Schema::create('spesifikasi_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_bahan')
                ->constrained('bahan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('kode_spesifikasi');
            $table->string('nama_spesifikasi');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['id_bahan', 'kode_spesifikasi']);
        });

        // 2. Update bahan - hapus field spesifikasi text dan tambah nullable
        Schema::table('bahan', function (Blueprint $table) {
            $table->dropColumn('spesifikasi');
        });

        // 3. Update pengadaan_bahan - tambah id_spesifikasi_bahan
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->foreignId('id_spesifikasi_bahan')
                ->after('id_bahan')
                ->nullable()
                ->constrained('spesifikasi_bahan')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse pengadaan_bahan
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->dropForeign(['id_spesifikasi_bahan']);
            $table->dropColumn('id_spesifikasi_bahan');
        });

        // Restore bahan spesifikasi text field
        Schema::table('bahan', function (Blueprint $table) {
            $table->text('spesifikasi')->nullable()->after('satuan');
        });

        // Drop spesifikasi_bahan
        Schema::dropIfExists('spesifikasi_bahan');
    }
};
