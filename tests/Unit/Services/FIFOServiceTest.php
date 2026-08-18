<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FIFOService;
use App\Models\Bahan;
use App\Models\PengadaanBahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\User;
use App\Services\StokService;

class FIFOServiceTest extends TestCase
{
    public function test_consume_fifo_uses_oldest_batch_first()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        $laboratorium = Laboratorium::factory(['id_user_kalab' => $user->id]);
        $bahan = Bahan::factory()->create(['id_kategori' => $kategori->id, 'id_labor' => $laboratorium->id, 'stok_saat_ini' => 0]);
        $batch1 = PengadaanBahan::create([
            'id_bahan' => $bahan->id,
            'id_user_input' => $user->id,
            'tanggal_pengadaan' => now(),
            'harga_perolehan' => 5724,
            'jumlah' => 50,
            'stok_tersisa_batch' => 50,
            'masa_expire_bahan' => now()->addYear(),
            'supplier' => 'Test Supplier',
            'tanggal_masuk' => now(),
        ]);
        $batch2 = PengadaanBahan::create([
            'id_bahan' => $bahan->id,
            'id_user_input' => $user->id,
            'tanggal_pengadaan' => now(),
            'harga_perolehan' => 5725,
            'jumlah' => 50,
            'stok_tersisa_batch' => 50,
            'masa_expire_bahan' => now()->addYears(2),
            'supplier' => 'Test Supplier',
            'tanggal_masuk' => now(),
        ]);
        $stokService = new StokService();
        $service = new FIFOService($stokService);

        $service->consumeFromBatches($bahan->id, 30);

        $this->assertEquals(20, $batch1->fresh()->stok_tersisa_batch);
        $this->assertEquals(50, $batch2->fresh()->stok_tersisa_batch);
    }

    public function test_consume_fifo_handles_partial_batch()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create();
        $laboratorium = Laboratorium::factory(['id_user_kalab' => $user->id]);
        $bahan = Bahan::factory()->create(['id_kategori' => $kategori->id, 'id_labor' => $laboratorium->id, 'stok_saat_ini' => 0]);
        $batch1 = PengadaanBahan::create([
            'id_bahan' => $bahan->id,
            'id_user_input' => $user->id,
            'tanggal_pengadaan' => now(),
            'harga_perolehan' => 5724,
            'jumlah' => 20,
            'stok_tersisa_batch' => 20,
            'masa_expire_bahan' => null,
            'supplier' => 'Test Supplier',
            'tanggal_masuk' => now(),
        ]);
        $batch2 = PengadaanBahan::create([
            'id_bahan' => $bahan->id,
            'id_user_input' => $user->id,
            'tanggal_pengadaan' => now(),
            'harga_perolehan' => 5725,
            'jumlah' => 50,
            'stok_tersisa_batch' => 50,
            'masa_expire_bahan' => now()->addYears(2),
            'supplier' => 'Test Supplier',
            'tanggal_masuk' => now(),
        ]);
        $stokService = new StokService();
        $service = new FIFOService($stokService);

        $service->consumeFromBatches($bahan->id, 30);

        $this->assertEquals(0, $batch1->fresh()->stok_tersisa_batch);
        $this->assertEquals(40, $batch2->fresh()->stok_tersisa_batch);
    }
}