<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->index('id_user_peminjam');
            $table->index('waktu_pengembalian');
        });

        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->index('id_bahan');
            $table->index('id_user_pemakai');
            $table->index('status_pengembalian');
            $table->index('id_user_verifikasi');
        });

        Schema::table('pemeliharaan_alat', function (Blueprint $table) {
            $table->index('id_teknisi');
        });

        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->index('stok_tersisa_batch');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->dropIndex(['id_user_peminjam']);
            $table->dropIndex(['waktu_pengembalian']);
        });

        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->dropIndex(['id_bahan']);
            $table->dropIndex(['id_user_pemakai']);
            $table->dropIndex(['status_pengembalian']);
            $table->dropIndex(['id_user_verifikasi']);
        });

        Schema::table('pemeliharaan_alat', function (Blueprint $table) {
            $table->dropIndex(['id_teknisi']);
        });

        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->dropIndex(['stok_tersisa_batch']);
        });
    }
};