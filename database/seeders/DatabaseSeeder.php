<?php

namespace Database\Seeders;

use App\Models\Laboratorium;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();

        $this->call(InventorySeeder::class);

        $this->seedLaborTeknisi();
    }

    private function seedUsers(): void
    {
        $users = [
            ['role' => 'admin_jurusan', 'nama' => 'Admin Jurusan', 'email' => 'admin@inventaris.test', 'no_induk' => 'ADM001'],
            ['role' => 'kadep', 'nama' => 'Kepala Departemen', 'email' => 'kadep@inventaris.test', 'no_induk' => 'KAD001'],
            ['role' => 'kepala_labor', 'nama' => 'Kepala Lab Elektronika', 'email' => 'kalab1@inventaris.test', 'no_induk' => 'KL001'],
            ['role' => 'kepala_labor', 'nama' => 'Kepala Lab Motor', 'email' => 'kalab2@inventaris.test', 'no_induk' => 'KL002'],
            ['role' => 'kepala_labor', 'nama' => 'Kepala Lab Motor Bakar', 'email' => 'kalab3@inventaris.test', 'no_induk' => 'KL003'],
            ['role' => 'kepala_labor', 'nama' => 'Kepala Lab Pengujian Kendaraan', 'email' => 'kalab4@inventaris.test', 'no_induk' => 'KL004'],
            ['role' => 'kepala_labor', 'nama' => 'Kepala Lab Dasar Teknologi Bengkel', 'email' => 'kalab5@inventaris.test', 'no_induk' => 'KL005'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 1', 'email' => 'teknisi1@inventaris.test', 'no_induk' => 'TK001'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 2', 'email' => 'teknisi2@inventaris.test', 'no_induk' => 'TK002'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 3', 'email' => 'teknisi3@inventaris.test', 'no_induk' => 'TK003'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 4', 'email' => 'teknisi4@inventaris.test', 'no_induk' => 'TK004'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 5', 'email' => 'teknisi5@inventaris.test', 'no_induk' => 'TK005'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 6', 'email' => 'teknisi6@inventaris.test', 'no_induk' => 'TK006'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 7', 'email' => 'teknisi7@inventaris.test', 'no_induk' => 'TK007'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 8', 'email' => 'teknisi8@inventaris.test', 'no_induk' => 'TK008'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 9', 'email' => 'teknisi9@inventaris.test', 'no_induk' => 'TK009'],
            ['role' => 'teknisi', 'nama' => 'Teknisi 10', 'email' => 'teknisi10@inventaris.test', 'no_induk' => 'TK010'],
            ['role' => 'dosen', 'nama' => 'Dosen 1', 'email' => 'dosen1@inventaris.test', 'no_induk' => 'DS001'],
            ['role' => 'mahasiswa', 'nama' => 'Mahasiswa 1', 'email' => 'mahasiswa@inventaris.test', 'no_induk' => 'MH001'],
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
                'foto' => 'users/profilelogo.webp',
            ]);
        }
    }

    private function seedLaborTeknisi(): void
    {
        $labs = Laboratorium::all();
        $teknisis = User::where('role', 'teknisi')->get();

        $labIndex = 0;
        foreach ($teknisis as $teknisi) {
            $lab = $labs[$labIndex % $labs->count()];
            DB::table('labor_teknisi')->insert([
                'id_laboratorium' => $lab->id,
                'id_user' => $teknisi->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $labIndex++;
        }
    }
}
