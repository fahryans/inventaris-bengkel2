<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\PemakaianBahan;
use App\Models\PengadaanBahan;

class BahanTest extends TestCase
{
    public function test_bahan_belongs_to_kategori()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'bahan']);
        $bahan = Bahan::factory()->create(['id_kategori' => $kategori->id]);

        $this->assertInstanceOf(Kategori::class, $bahan->kategori);
        $this->assertEquals($kategori->id, $bahan->kategori->id);
    }

    public function test_bahan_has_many_pemakaian_bahan()
    {
        $bahan = Bahan::factory()->create();
        PemakaianBahan::factory()->count(3)->create(['id_bahan' => $bahan->id]);

        $this->assertCount(3, $bahan->pemakaianBahan);
        $this->assertInstanceOf(PemakaianBahan::class, $bahan->pemakaianBahan->first());
    }

    public function test_bahan_has_many_pengadaan_bahan()
    {
        $bahan = Bahan::factory()->create();
        PengadaanBahan::factory()->count(2)->create(['id_bahan' => $bahan->id]);

        $this->assertCount(2, $bahan->pengadaanBahan);
        $this->assertInstanceOf(PengadaanBahan::class, $bahan->pengadaanBahan->first());
    }

    public function test_bahan_stok_saat_ini_is_cast_to_int()
    {
        $bahan = Bahan::factory()->create();
        PengadaanBahan::factory()->create(['id_bahan' => $bahan->id, 'stok_tersisa_batch' => 50]);

        $this->assertIsInt($bahan->getTotalStock());
        $this->assertEquals(50, $bahan->getTotalStock());
    }

    public function test_bahan_uses_soft_deletes()
    {
        $bahan = Bahan::factory()->create();
        $bahan->delete();

        $this->assertSoftDeleted('bahan', ['id' => $bahan->id]);
    }

    public function test_bahan_is_stok_menipis()
    {
        $bahan = Bahan::factory()->create(['stok_minimum' => 10]);
        PengadaanBahan::factory()->create(['id_bahan' => $bahan->id, 'stok_tersisa_batch' => 5]);

        $this->assertTrue($bahan->isStokMenipis());
    }

    public function test_bahan_is_not_stok_menipis()
    {
        $bahan = Bahan::factory()->create(['stok_minimum' => 10]);
        PengadaanBahan::factory()->create(['id_bahan' => $bahan->id, 'stok_tersisa_batch' => 20]);

        $this->assertFalse($bahan->isStokMenipis());
    }
}
