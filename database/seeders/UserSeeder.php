<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@inventaris.com'
            ],
            [
                'role'      => 'admin_jurusan',
                'nama'      => 'Administrator',
                'email'     => 'admin@inventaris.com',
                'password'  => Hash::make('password'),
                'status'    => 'aktif',
                'no_hp'     => null,
                'no_induk'  => null,
                'foto'      => 'users/profilelogo.webp',
            ]
        );
    }
}