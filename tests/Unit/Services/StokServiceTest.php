<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StokService;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\UnitAlat;
use App\Models\User;
use App\Models\Alat;

class StokServiceTest extends TestCase
{
    public function test_add_stok_increases_bahan_stock()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        $laboratorium = Laboratorium::factory(['id_user_kalab' => $user->id]);
        $bahan = Bahan::factory()->create(['id_kategori' => $kategori->id, 'id_labor' => $laboratorium->id, 'stok_saat_ini' => 50]);
        $service = new StokService();

        $service->tambahBahan($bahan, 20);

        $this->assertEquals(70, $bahan->fresh()->stok_saat_ini);
    }

    public function test_subtract_stok_decreases_bahan_stock()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        $laboratorium = Laboratorium::factory(['id_user_kalab' => $user->id]);
        $bahan = Bahan::factory()->create(['id_kategori' => $kategori->id, 'id_labor' => $laboratorium->id, 'stok_saat_ini' => 50]);
        $service = new StokService();

        $service->kurangiBahan($bahan, 20);

        $this->assertEquals(30, $bahan->fresh()->stok_saat_ini);
    }

    public function test_subtract_stok_throws_on_insufficient()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        $laboratorium = Laboratorium::factory(['id_user_kalab' => $user->id]);
        $bahan = Bahan::factory()->create(['id_kategori' => $kategori->id, 'id_labor' => $laboratorium->id, 'stok_saat_ini' => 10]);
        $service = new StokService();

        $this->expectException(\Exception::class);
        $service->kurangiBahan($bahan, 20);
    }

    public function test_update_unit_status()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        $laboratorium = Laboratorium::factory(['id_user_kalab' => $user->id]);
        $alat = Alat::factory()->create(['id_kategori' => $kategori->id, 'id_labor' => $laboratorium->id, 'tipe_pelacakan' => 'unit']);
        $unit = UnitAlat::factory()->create(['id_alat' => $alat->id, 'status' => 'tersedia']);
        $service = new StokService();

        $service->updateUnitStatus($unit, 'terpinjam');

        $this->assertEquals('terpinjam', $unit->fresh()->status);
    }
}