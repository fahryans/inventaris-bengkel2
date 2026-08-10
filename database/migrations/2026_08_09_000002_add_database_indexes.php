<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->index('id_kategori');
            $table->index('id_labor');
            $table->index('tipe_pelacakan');
        });

        Schema::table('bahan', function (Blueprint $table) {
            $table->index('id_kategori');
            $table->index('id_labor');
        });

        Schema::table('unit_alat', function (Blueprint $table) {
            $table->index('id_alat');
            $table->index('status');
        });

        Schema::table('pengadaan_alat', function (Blueprint $table) {
            $table->index('id_alat');
            $table->index('id_user_input');
            $table->index('tanggal_masuk');
        });

        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->index('id_bahan');
            $table->index('id_user_input');
            $table->index('tanggal_masuk');
        });

        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->index('id_alat');
            $table->index('id_unit_alat');
            $table->index('id_user_peminjam');
            $table->index('status');
            $table->index('waktu_peminjaman');
        });

        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->index('id_bahan');
            $table->index('id_pengadaan_bahan');
            $table->index('id_user_pemakai');
            $table->index('id_user_verifikasi');
        });

        Schema::table('pemeliharaan_alat', function (Blueprint $table) {
            $table->index('id_unit_alat');
            $table->index('id_teknisi');
            $table->index('tanggal_cek');
            $table->index('tanggal_cek_berikutnya');
        });

        Schema::table('laboratorium', function (Blueprint $table) {
            $table->index('id_user_kalab');
        });
    }

    public function down(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->dropIndex(['id_kategori']);
            $table->dropIndex(['id_labor']);
            $table->dropIndex(['tipe_pelacakan']);
        });

        Schema::table('bahan', function (Blueprint $table) {
            $table->dropIndex(['id_kategori']);
            $table->dropIndex(['id_labor']);
        });

        Schema::table('unit_alat', function (Blueprint $table) {
            $table->dropIndex(['id_alat']);
            $table->dropIndex(['status']);
        });

        Schema::table('pengadaan_alat', function (Blueprint $table) {
            $table->dropIndex(['id_alat']);
            $table->dropIndex(['id_user_input']);
            $table->dropIndex(['tanggal_masuk']);
        });

        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->dropIndex(['id_bahan']);
            $table->dropIndex(['id_user_input']);
            $table->dropIndex(['tanggal_masuk']);
        });

        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->dropIndex(['id_alat']);
            $table->dropIndex(['id_unit_alat']);
            $table->dropIndex(['id_user_peminjam']);
            $table->dropIndex(['status']);
            $table->dropIndex(['waktu_peminjaman']);
        });

        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->dropIndex(['id_bahan']);
            $table->dropIndex(['id_pengadaan_bahan']);
            $table->dropIndex(['id_user_pemakai']);
            $table->dropIndex(['id_user_verifikasi']);
        });

        Schema::table('pemeliharaan_alat', function (Blueprint $table) {
            $table->dropIndex(['id_unit_alat']);
            $table->dropIndex(['id_teknisi']);
            $table->dropIndex(['tanggal_cek']);
            $table->dropIndex(['tanggal_cek_berikutnya']);
        });

        Schema::table('laboratorium', function (Blueprint $table) {
            $table->dropIndex(['id_user_kalab']);
        });
    }
};
