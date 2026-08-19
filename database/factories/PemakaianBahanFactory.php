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
        $bahan = Bahan::factory()->create();
        $pengadaan = PengadaanBahan::factory()->create(['id_bahan' => $bahan->id]);

        return [
            'id_bahan' => $bahan->id,
            'id_pengadaan_bahan' => $pengadaan->id,
            'id_user_pemakai' => User::factory(),
            'id_user_verifikasi' => null,
            'keperluan' => fake()->sentence(),
            'jumlah_pengambilan' => fake()->numberBetween(1, 10),
            'jumlah_terpakai' => fake()->numberBetween(1, 10),
            'jumlah_pengembalian' => 0,
            'waktu_pemakaian' => now(),
        ];
    }
}