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
            'tipe_pelacakan' => fake()->randomElement(['unit', 'agregat']),
            'foto' => null,
        ];
    }

    public function agregat(): static
    {
        return $this->state(fn () => ['tipe_pelacakan' => 'agregat']);
    }

    public function unit(): static
    {
        return $this->state(fn () => ['tipe_pelacakan' => 'unit']);
    }
}