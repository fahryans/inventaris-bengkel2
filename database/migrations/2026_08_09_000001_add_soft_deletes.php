<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('alat', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('bahan', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('unit_alat', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('pemeliharaan_alat', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('alat', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('bahan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('unit_alat', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('peminjaman_alat', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('pemeliharaan_alat', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
