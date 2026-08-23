<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labor_teknisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_laboratorium')
                ->constrained('laboratorium')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('id_user')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['id_laboratorium', 'id_user']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labor_teknisi');
    }
};
