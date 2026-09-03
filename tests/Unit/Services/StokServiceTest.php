<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StokService;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\UnitAlat;
use App\Models\User;
use App\Models\Alat;

class StokServiceTest extends TestCase
{
    public function test_update_unit_status()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $laboratorium = Laboratorium::factory()->create(['id_user_kalab' => $user->id]);
        $alat = Alat::factory()->create(['id_kategori' => $kategori->id, 'id_labor' => $laboratorium->id, 'tipe_pelacakan' => 'unit']);
        $unit = UnitAlat::factory()->create(['id_alat' => $alat->id, 'status' => 'tersedia']);
        $service = new StokService();

        $service->updateUnitStatus($unit, 'dipinjam');

        $this->assertEquals('dipinjam', $unit->fresh()->status);
    }
}