<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa']);
            $table->string('nama');
            $table->string('no_hp')->nullable();
            $table->string('no_induk')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('foto')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
