<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_alat', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique(['kode_inventaris']);
            // Make nullable
            $table->string('kode_inventaris')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('unit_alat', function (Blueprint $table) {
            $table->string('kode_inventaris')->nullable(false)->change();
            $table->unique('kode_inventaris');
        });
    }
};