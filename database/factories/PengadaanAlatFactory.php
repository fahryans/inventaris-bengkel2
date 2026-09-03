<?php

namespace Database\Factories;

use App\Models\Alat;
use App\Models\PengadaanAlat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PengadaanAlatFactory extends Factory
{
    protected $model = PengadaanAlat::class;

    public function definition(): array
    {
        return [
            'id_alat' => Alat::factory(),
            'id_spesifikasi_alat' => fn (array $attrs) => \App\Models\SpesifikasiAlat::factory()->create(['id_alat' => $attrs['id_alat']])->id,
            'id_user_input' => User::factory(),
            'tanggal_pengadaan' => now(),
            'harga_perolehan' => fake()->randomNumber(5),
            'jumlah' => fake()->numberBetween(1, 10),
            'supplier' => fake()->company(),
            'tanggal_masuk' => null,
            'foto_transaksi' => null,
        ];
    }
}