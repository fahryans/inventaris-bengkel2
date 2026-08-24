<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->string('status_pengembalian', 20)->nullable()->after('jumlah_pengembalian');
            $table->timestamp('waktu_pengembalian')->nullable()->after('status_pengembalian');
        });
    }

    public function down(): void
    {
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->dropColumn(['status_pengembalian', 'waktu_pengembalian']);
        });
    }
};
