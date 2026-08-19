<?php

namespace Database\Factories;

use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use Illuminate\Database\Eloquent\Factories\Factory;

class BahanFactory extends Factory
{
    protected $model = Bahan::class;

    public function definition(): array
    {
        return [
            'id_kategori' => Kategori::factory(['jenis' => 'bahan']),
            'id_labor' => Laboratorium::factory(),
            'nama_bahan' => fake()->word(),
            'stok_saat_ini' => fake()->numberBetween(0, 100),
            'stok_minimum' => fake()->numberBetween(1, 50),
            'satuan' => fake()->randomElement(['pcs', 'kg', 'liter', 'unit']),
            'merek' => fake()->company(),
            'spesifikasi' => fake()->text(),
            'foto' => null,
        ];
    }
}
