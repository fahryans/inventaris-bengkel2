<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->index('tipe_pelacakan');
        });

        Schema::table('unit_alat', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('pengadaan_alat', function (Blueprint $table) {
            $table->index('tanggal_masuk');
        });

        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->index('tanggal_masuk');
        });

        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->index('status');
            $table->index('waktu_peminjaman');
        });

        Schema::table('pemeliharaan_alat', function (Blueprint $table) {
            $table->index('tanggal_cek');
            $table->index('tanggal_cek_berikutnya');
        });
    }

    public function down(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->dropIndex(['tipe_pelacakan']);
        });

        Schema::table('unit_alat', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('pengadaan_alat', function (Blueprint $table) {
            $table->dropIndex(['tanggal_masuk']);
        });

        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->dropIndex(['tanggal_masuk']);
        });

        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['waktu_peminjaman']);
        });

        Schema::table('pemeliharaan_alat', function (Blueprint $table) {
            $table->dropIndex(['tanggal_cek']);
            $table->dropIndex(['tanggal_cek_berikutnya']);
        });
    }
};
