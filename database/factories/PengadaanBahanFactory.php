<?php

namespace Database\Factories;

use App\Models\Bahan;
use App\Models\PengadaanBahan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PengadaanBahanFactory extends Factory
{
    protected $model = PengadaanBahan::class;

    public function definition(): array
    {
        return [
            'id_bahan' => Bahan::factory(),
            'id_user_input' => User::factory(),
            'tanggal_pengadaan' => now(),
            'harga_perolehan' => fake()->randomNumber(4),
            'jumlah' => fake()->numberBetween(1, 50),
            'stok_tersisa_batch' => fake()->numberBetween(1, 100),
            'masa_expire_bahan' => now()->addMonths(6),
            'supplier' => fake()->company(),
            'tanggal_masuk' => now(),
            'foto_transaksi' => null,
        ];
    }
}
