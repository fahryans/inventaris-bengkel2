<?php

namespace Database\Factories;

use App\Models\Alat;
use App\Models\SpesifikasiAlat;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpesifikasiAlatFactory extends Factory
{
    protected $model = SpesifikasiAlat::class;

    public function definition(): array
    {
        return [
            'id_alat' => Alat::factory(),
            'kode_spesifikasi' => strtoupper('SPK-' . fake()->unique()->bothify('###????')),
            'nama_spesifikasi' => fake()->words(3, true),
            'deskripsi' => fake()->sentence(),
        ];
    }
}