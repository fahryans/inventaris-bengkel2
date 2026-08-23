<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->foreignId('id_laboratorium')
                ->nullable()
                ->constrained('laboratorium')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->after('id_user_pemakai');
        });
    }

    public function down(): void
    {
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->dropForeign(['id_laboratorium']);
            $table->dropColumn('id_laboratorium');
        });
    }
};
