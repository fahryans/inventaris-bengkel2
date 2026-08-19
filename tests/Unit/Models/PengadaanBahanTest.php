<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Bahan;
use App\Models\PengadaanBahan;

class PengadaanBahanTest extends TestCase
{
    public function test_pengadaan_bahan_belongs_to_bahan()
    {
        $bahan = Bahan::factory()->create();
        $pengadaan = PengadaanBahan::factory()->create(['id_bahan' => $bahan->id]);

        $this->assertInstanceOf(Bahan::class, $pengadaan->bahan);
        $this->assertEquals($bahan->id, $pengadaan->bahan->id);
    }

    public function test_pengadaan_bahan_jumlah_is_cast_to_int()
    {
        $pengadaan = PengadaanBahan::factory()->create(['jumlah' => 100]);

        $this->assertIsInt($pengadaan->jumlah);
        $this->assertEquals(100, $pengadaan->jumlah);
    }

    public function test_pengadaan_bahan_stok_tersisa_batch_is_cast_to_int()
    {
        $pengadaan = PengadaanBahan::factory()->create(['stok_tersisa_batch' => 50]);

        $this->assertIsInt($pengadaan->stok_tersisa_batch);
        $this->assertEquals(50, $pengadaan->stok_tersisa_batch);
    }

    public function test_pengadaan_bahan_harga_perolehan_is_accessible()
    {
        $pengadaan = PengadaanBahan::factory()->create(['harga_perolehan' => 50000.50]);

        $this->assertNotNull($pengadaan->harga_perolehan);
        $this->assertEquals(50000.50, $pengadaan->harga_perolehan);
    }

    public function test_pengadaan_bahan_received_status()
    {
        $pengadaan = PengadaanBahan::factory()->create(['tanggal_masuk' => now()]);

        $this->assertNotNull($pengadaan->tanggal_masuk);
    }

    public function test_pengadaan_bahan_pending_status()
    {
        $pengadaan = PengadaanBahan::factory()->create(['tanggal_masuk' => null]);

        $this->assertNull($pengadaan->tanggal_masuk);
    }
}
