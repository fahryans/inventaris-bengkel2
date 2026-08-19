<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Alat;
use App\Models\UnitAlat;
use App\Models\PeminjamanAlat;

class UnitAlatTest extends TestCase
{
    public function test_unit_alat_belongs_to_alat()
    {
        $alat = Alat::factory()->create();
        $unit = UnitAlat::factory()->create(['id_alat' => $alat->id]);

        $this->assertInstanceOf(Alat::class, $unit->alat);
        $this->assertEquals($alat->id, $unit->alat->id);
    }

    public function test_unit_alat_has_many_peminjaman_alat()
    {
        $unit = UnitAlat::factory()->create();
        $alat = Alat::factory()->create();
        PeminjamanAlat::factory()->count(3)->create([
            'id_alat' => $alat->id,
            'id_unit_alat' => $unit->id,
        ]);

        $this->assertCount(3, $unit->peminjamanAlat);
        $this->assertInstanceOf(PeminjamanAlat::class, $unit->peminjamanAlat->first());
    }

    public function test_unit_alat_has_pemeliharaan()
    {
        $unit = UnitAlat::factory()->create();

        $this->assertIsIterable($unit->pemeliharaanAlat);
        $this->assertCount(0, $unit->pemeliharaanAlat);
    }

    public function test_unit_alat_status_is_cast()
    {
        $unit = UnitAlat::factory()->create(['status' => 'tersedia']);

        $this->assertIsString($unit->status);
        $this->assertEquals('tersedia', $unit->status);
    }

    public function test_unit_alat_kode_inventaris_is_unique()
    {
        $unit = UnitAlat::factory()->create(['kode_inventaris' => 'UNIT-001']);

        $this->assertEquals('UNIT-001', $unit->kode_inventaris);
    }

    public function test_unit_alat_uses_soft_deletes()
    {
        $unit = UnitAlat::factory()->create();
        $unit->delete();

        $this->assertSoftDeleted('unit_alat', ['id' => $unit->id]);
    }

    public function test_unit_alat_available_scope()
    {
        $unit = UnitAlat::factory()->create(['status' => 'tersedia']);

        $this->assertEquals('tersedia', $unit->status);
        $this->assertContains('tersedia', ['tersedia', 'dipinjam', 'rusak', 'maintenance']);
    }
}
