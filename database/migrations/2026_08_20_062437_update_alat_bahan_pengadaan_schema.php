<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Perubahan konsep:
     * - Tabel alat & bahan: hanya master data (nama + spesifikasi)
     * - Tabel pengadaan_alat & pengadaan_bahan: mencatat setiap acquisition dengan merek, jumlah, supplier
     * - Total stock = agregat dari semua pengadaan dengan spesifikasi yang sama
     */
    public function up(): void
    {
        // 1. Modify alat table - remove merek & jumlah_alat
        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn(['merek', 'jumlah_alat']);
        });

        // 2. Modify bahan table - remove merek, stok_saat_ini, stok_minimum
        Schema::table('bahan', function (Blueprint $table) {
            $table->dropColumn(['merek', 'stok_saat_ini', 'stok_minimum']);
        });

        // 3. Modify pengadaan_alat table - add merek field
        Schema::table('pengadaan_alat', function (Blueprint $table) {
            $table->string('merek')->nullable()->after('jumlah');
        });

        // 4. Modify pengadaan_bahan table - add merek field
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->string('merek')->nullable()->after('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore alat columns
        Schema::table('alat', function (Blueprint $table) {
            $table->string('merek')->nullable()->after('nama_alat');
            $table->unsignedInteger('jumlah_alat')->default(0)->after('tipe_pelacakan');
        });

        // Restore bahan columns
        Schema::table('bahan', function (Blueprint $table) {
            $table->string('merek')->nullable()->after('satuan');
            $table->unsignedInteger('stok_saat_ini')->default(0)->after('satuan');
            $table->unsignedInteger('stok_minimum')->default(0)->after('stok_saat_ini');
        });

        // Remove merek from pengadaan_alat
        Schema::table('pengadaan_alat', function (Blueprint $table) {
            $table->dropColumn('merek');
        });

        // Remove merek from pengadaan_bahan
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->dropColumn('merek');
        });
    }
};
