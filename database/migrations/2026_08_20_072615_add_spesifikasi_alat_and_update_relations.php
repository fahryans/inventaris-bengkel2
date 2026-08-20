<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel spesifikasi_alat
        Schema::create('spesifikasi_alat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alat')
                ->constrained('alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('kode_spesifikasi');
            $table->string('nama_spesifikasi');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['id_alat', 'kode_spesifikasi']);
        });

        // 2. Update alat - hapus field spesifikasi
        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn('spesifikasi');
        });

        // 3. Update pengadaan_alat - tambah id_spesifikasi_alat & kode_inventaris
        Schema::table('pengadaan_alat', function (Blueprint $table) {
            $table->foreignId('id_spesifikasi_alat')
                ->after('id_alat')
                ->constrained('spesifikasi_alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('kode_inventaris')->nullable()->after('id_spesifikasi_alat');
        });

        // 4. Update unit_alat - tambah id_spesifikasi_alat
        Schema::table('unit_alat', function (Blueprint $table) {
            $table->foreignId('id_spesifikasi_alat')
                ->after('id_alat')
                ->constrained('spesifikasi_alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // 5. Update peminjaman_alat - tambah id_spesifikasi_alat
        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->foreignId('id_spesifikasi_alat')
                ->after('id_alat')
                ->nullable()
                ->constrained('spesifikasi_alat')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Reverse peminjaman_alat
        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->dropForeign(['id_spesifikasi_alat']);
            $table->dropColumn('id_spesifikasi_alat');
        });

        // Reverse unit_alat
        Schema::table('unit_alat', function (Blueprint $table) {
            $table->dropForeign(['id_spesifikasi_alat']);
            $table->dropColumn('id_spesifikasi_alat');
        });

        // Reverse pengadaan_alat
        Schema::table('pengadaan_alat', function (Blueprint $table) {
            $table->dropForeign(['id_spesifikasi_alat']);
            $table->dropColumn(['id_spesifikasi_alat', 'kode_inventaris']);
        });

        // Restore alat spesifikasi
        Schema::table('alat', function (Blueprint $table) {
            $table->text('spesifikasi')->nullable()->after('nama_alat');
        });

        // Drop spesifikasi_alat
        Schema::dropIfExists('spesifikasi_alat');
    }
};
