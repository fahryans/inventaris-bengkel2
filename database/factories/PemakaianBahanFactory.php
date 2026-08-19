<?php

namespace Database\Factories;

use App\Models\Bahan;
use App\Models\PemakaianBahan;
use App\Models\PengadaanBahan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PemakaianBahanFactory extends Factory
{
    protected $model = PemakaianBahan::class;

    public function definition(): array
    {
        return [
            'id_bahan' => Bahan::factory(),
            'id_pengadaan_bahan' => PengadaanBahan::factory(),
            'id_user_pemakai' => User::factory(),
            'id_user_verifikasi' => User::factory(),
            'keperluan' => fake()->sentence(),
            'jumlah_pengambilan' => fake()->numberBetween(1, 50),
            'jumlah_terpakai' => fake()->numberBetween(1, 50),
            'jumlah_pengembalian' => 0,
            'waktu_pemakaian' => now(),
        ];
    }
}
