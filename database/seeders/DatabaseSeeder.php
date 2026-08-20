<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed users saja
        $this->seedUsers();

        // Seed data inventory (alat, bahan, pengadaan, dll)
        $this->call(InventorySeeder::class);
    }

    private function seedUsers(): void
    {
        $users = [
            ['role' => 'admin_jurusan', 'nama' => 'Admin Jurusan', 'email' => 'admin@inventaris.test', 'no_induk' => 'ADM001'],
            ['role' => 'kadep', 'nama' => 'Kepala Departemen', 'email' => 'kadep@inventaris.test', 'no_induk' => 'KAD001'],
            ['role' => 'kepala_labor', 'nama' => 'Kepala Lab Elektronika', 'email' => 'kalab1@inventaris.test', 'no_induk' => 'KL001'],
            ['role' => 'kepala_labor', 'nama' => 'Kepala Lab Mekanik', 'email' => 'kalab2@inventaris.test', 'no_induk' => 'KL002'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 1', 'email' => 'teknisi1@inventaris.test', 'no_induk' => 'TK001'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 2', 'email' => 'teknisi2@inventaris.test', 'no_induk' => 'TK002'],
            ['role' => 'dosen', 'nama' => 'Dosen Elektronika', 'email' => 'dosen1@inventaris.test', 'no_induk' => 'DS001'],
            ['role' => 'mahasiswa', 'nama' => 'Mahasiswa Test', 'email' => 'mahasiswa@inventaris.test', 'no_induk' => 'MH001'],
        ];

        foreach ($users as $i => $user) {
            User::create([
                'role' => $user['role'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'password' => Hash::make('password'),
                'status' => 'aktif',
                'no_hp' => '0812' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'no_induk' => $user['no_induk'],
            ]);
        }
    }
}
