<?php

namespace Database\Factories;

use App\Models\Alat;
use App\Models\PeminjamanAlat;
use App\Models\UnitAlat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeminjamanAlatFactory extends Factory
{
    protected $model = PeminjamanAlat::class;

    public function definition(): array
    {
        return [
            'id_alat' => Alat::factory()->create(['tipe_pelacakan' => 'agregat']),
            'id_unit_alat' => null,
            'id_user_peminjam' => User::factory(),
            'keperluan' => fake()->sentence(),
            'waktu_peminjaman' => now(),
            'waktu_pengembalian' => now()->addDays(7),
            'waktu_kembali_aktual' => null,
            'jumlah' => 1,
            'kondisi_saat_peminjaman' => 'baik',
            'kondisi_saat_pengembalian' => null,
            'status' => 'terpinjam',
        ];
    }

    public function forUnit(UnitAlat $unit): static
    {
        return $this->state(fn () => [
            'id_alat' => null,
            'id_unit_alat' => $unit->id,
        ]);
    }
}