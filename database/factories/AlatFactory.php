<?php

namespace Database\Factories;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Laboratorium;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlatFactory extends Factory
{
    protected $model = Alat::class;

    public function definition(): array
    {
        return [
            'id_kategori' => Kategori::factory(['jenis' => 'alat']),
            'id_labor' => Laboratorium::factory(),
            'nama_alat' => fake()->word(),
            'merek' => fake()->company(),
            'spesifikasi' => fake()->text(),
            'tipe_pelacakan' => fake()->randomElement(['unit', 'agregat']),
            'jumlah_alat' => fake()->numberBetween(1, 100),
            'foto' => null,
        ];
    }
}
