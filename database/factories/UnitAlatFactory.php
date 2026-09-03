<?php

namespace Database\Factories;

use App\Models\UnitAlat;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitAlatFactory extends Factory
{
    protected $model = UnitAlat::class;

    public function definition(): array
    {
        return [
            'id_alat' => \App\Models\Alat::factory()->unit(),
            'id_spesifikasi_alat' => fn (array $attrs) => \App\Models\SpesifikasiAlat::factory()->create(['id_alat' => $attrs['id_alat']])->id,
            'kode_inventaris' => strtoupper('INV-' . fake()->unique()->bothify('####??????')),
            'kondisi_saat_ini' => 'baik',
            'status' => 'tersedia',
        ];
    }
}