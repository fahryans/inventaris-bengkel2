<?php

namespace Database\Factories;

use App\Models\Alat;
use App\Models\UnitAlat;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitAlatFactory extends Factory
{
    protected $model = UnitAlat::class;

    public function definition(): array
    {
        return [
            'id_alat' => Alat::factory(),
            'kode_inventaris' => strtoupper('INV-' . fake()->unique()->bothify('####??????')),
            'kondisi_saat_ini' => 'baik',
            'status' => fake()->randomElement(['tersedia', 'dipinjam', 'rusak', 'maintenance']),
        ];
    }
}
