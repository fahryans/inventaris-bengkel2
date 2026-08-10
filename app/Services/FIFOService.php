<?php

namespace App\Services;

use App\Models\Bahan;
use App\Models\PemakaianBahan;
use App\Models\PengadaanBahan;

class FIFOService
{
    public function consumeFromBatches(int $idBahan, int $jumlahTerpakai): array
    {
        try {
            \DB::beginTransaction();

            $batches = PengadaanBahan::query()
                ->where('id_bahan', $idBahan)
                ->where('stok_tersisa_batch', '>', 0)
                ->orderByRaw('masa_expire_bahan IS NULL, masa_expire_bahan ASC')
                ->get();

            if ($batches->isEmpty()) {
                throw new \Exception('Tidak ada batch yang tersedia untuk bahan ini');
            }

            $sisaPemakaian = $jumlahTerpakai;
            $batchesUsed = [];

            foreach ($batches as $batch) {
                if ($sisaPemakaian <= 0) {
                    break;
                }

                $ambilDariBatch = min($sisaPemakaian, $batch->stok_tersisa_batch);

                $batch->decrement('stok_tersisa_batch', $ambilDariBatch);

                $batchesUsed[] = [
                    'id_pengadaan_bahan' => $batch->id,
                    'jumlah_diambil' => $ambilDariBatch,
                    'masa_expire' => $batch->masa_expire_bahan,
                ];

                $sisaPemakaian -= $ambilDariBatch;
            }

            if ($sisaPemakaian > 0) {
                throw new \Exception("Stok bahan tidak cukup. Kurang: {$sisaPemakaian}");
            }

            \DB::commit();
            return $batchesUsed;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function getBatchesForBahan(int $idBahan): \Illuminate\Database\Eloquent\Collection
    {
        return PengadaanBahan::query()
            ->where('id_bahan', $idBahan)
            ->where('stok_tersisa_batch', '>', 0)
            ->orderByRaw('masa_expire_bahan IS NULL, masa_expire_bahan ASC')
            ->get();
    }

    public function getExpiredBatches(): \Illuminate\Database\Eloquent\Collection
    {
        return PengadaanBahan::query()
            ->whereNotNull('masa_expire_bahan')
            ->where('masa_expire_bahan', '<', now())
            ->where('stok_tersisa_batch', '>', 0)
            ->with('bahan')
            ->get();
    }

    public function getExpiringBatches(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return PengadaanBahan::query()
            ->whereNotNull('masa_expire_bahan')
            ->whereBetween('masa_expire_bahan', [now(), now()->addDays($days)])
            ->where('stok_tersisa_batch', '>', 0)
            ->with('bahan')
            ->orderBy('masa_expire_bahan')
            ->get();
    }

    public function markBatchAsExpired(PengadaanBahan $batch): bool
    {
        try {
            \DB::beginTransaction();

            $batch->update(['stok_tersisa_batch' => 0]);

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
