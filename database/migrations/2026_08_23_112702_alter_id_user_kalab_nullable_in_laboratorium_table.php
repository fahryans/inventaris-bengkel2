<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratorium', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user_kalab')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laboratorium', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user_kalab')->nullable(false)->change();
        });
    }
};
