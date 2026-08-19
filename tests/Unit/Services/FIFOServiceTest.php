<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FIFOService;
use App\Services\StokService;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\User;

class FIFOServiceTest extends TestCase
{
    private function makeBahan(): array
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create(['jenis' => 'bahan']);
        $laboratorium = Laboratorium::factory()->create(['id_user_kalab' => $user->id]);
        $bahan = Bahan::factory()->create(['id_kategori' => $kategori->id, 'id_labor' => $laboratorium->id, 'stok_saat_ini' => 100]);

        return [$user, $bahan];
    }

    private function makeBatch($bahan, $user, array $overrides = []): \App\Models\PengadaanBahan
    {
        return \App\Models\PengadaanBahan::create(array_merge([
            'id_bahan' => $bahan->id,
            'id_user_input' => $user->id,
            'tanggal_pengadaan' => now(),
            'harga_perolehan' => 5000,
            'jumlah' => 50,
            'stok_tersisa_batch' => 50,
            'masa_expire_bahan' => null,
            'supplier' => 'Test Supplier',
            'tanggal_masuk' => now(),
        ], $overrides));
    }

    public function test_consume_fifo_uses_oldest_batch_first()
    {
        [$user, $bahan] = $this->makeBahan();
        $batch1 = $this->makeBatch($bahan, $user, ['tanggal_pengadaan' => now()->subDays(10)]);
        $batch2 = $this->makeBatch($bahan, $user, ['tanggal_pengadaan' => now()]);

        $service = new FIFOService(new StokService());
        $service->consumeFromBatches($bahan->id, 30);

        $this->assertEquals(20, $batch1->fresh()->stok_tersisa_batch);
        $this->assertEquals(50, $batch2->fresh()->stok_tersisa_batch);
    }

    public function test_consume_fifo_handles_partial_batch()
    {
        [$user, $bahan] = $this->makeBahan();
        $batch1 = $this->makeBatch($bahan, $user, ['jumlah' => 20, 'stok_tersisa_batch' => 20, 'tanggal_pengadaan' => now()->subDays(10)]);
        $batch2 = $this->makeBatch($bahan, $user, ['tanggal_pengadaan' => now()]);

        $service = new FIFOService(new StokService());
        $service->consumeFromBatches($bahan->id, 30);

        $this->assertEquals(0, $batch1->fresh()->stok_tersisa_batch);
        $this->assertEquals(40, $batch2->fresh()->stok_tersisa_batch);
    }
}