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
            'jumlah_alat' => fn (array $attributes) => $attributes['tipe_pelacakan'] === 'unit' ? 0 : fake()->numberBetween(1, 100),
            'foto' => null,
        ];
    }

    public function agregat(): static
    {
        return $this->state(fn () => ['tipe_pelacakan' => 'agregat', 'jumlah_alat' => fake()->numberBetween(1, 100)]);
    }

    public function unit(): static
    {
        return $this->state(fn () => ['tipe_pelacakan' => 'unit', 'jumlah_alat' => 0]);
    }
}