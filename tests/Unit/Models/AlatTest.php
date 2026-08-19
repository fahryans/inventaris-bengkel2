<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\UnitAlat;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlatTest extends TestCase
{
    public function test_alat_belongs_to_kategori()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $alat = Alat::factory()->create(['id_kategori' => $kategori->id]);

        $this->assertInstanceOf(Kategori::class, $alat->kategori);
        $this->assertEquals($kategori->id, $alat->kategori->id);
    }

    public function test_alat_belongs_to_laboratorium()
    {
        $lab = Laboratorium::factory()->create();
        $alat = Alat::factory()->create(['id_labor' => $lab->id]);

        $this->assertInstanceOf(Laboratorium::class, $alat->laboratorium);
        $this->assertEquals($lab->id, $alat->laboratorium->id);
    }

    public function test_alat_has_many_unit_alat()
    {
        $alat = Alat::factory()->create();
        UnitAlat::factory()->count(3)->create(['id_alat' => $alat->id]);

        $this->assertCount(3, $alat->unitAlat);
        $this->assertInstanceOf(UnitAlat::class, $alat->unitAlat->first());
    }

    public function test_alat_tipe_pelacakan_is_cast()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'unit']);

        $this->assertIsString($alat->tipe_pelacakan);
        $this->assertEquals('unit', $alat->tipe_pelacakan);
    }

    public function test_alat_uses_soft_deletes()
    {
        $alat = Alat::factory()->create();
        $alat->delete();

        $this->assertSoftDeleted('alat', ['id' => $alat->id]);
    }

    public function test_alat_is_unit_tracked()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'unit']);

        $this->assertTrue($alat->isUnitTracked());
    }

    public function test_alat_is_not_unit_tracked()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'agregat']);

        $this->assertFalse($alat->isUnitTracked());
    }
}
