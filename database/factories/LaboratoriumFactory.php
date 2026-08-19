<?php

namespace Database\Factories;

use App\Models\Laboratorium;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaboratoriumFactory extends Factory
{
    protected $model = Laboratorium::class;

    public function definition(): array
    {
        return [
            'id_user_kalab' => User::factory(),
            'nama_labor' => fake()->word(),
            'lokasi' => fake()->city(),
            'sop' => fake()->text(),
        ];
    }
}
