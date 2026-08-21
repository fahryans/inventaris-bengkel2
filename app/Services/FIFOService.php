<?php

namespace App\Services;

use App\Models\PengadaanBahan;
use Illuminate\Support\Facades\DB;

class FIFOService
{
    protected StokService $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    public function consumeFromBatches(int $idBahan, int $jumlahTerpakai): array
    {
        if ($jumlahTerpakai <= 0) {
            throw new \Exception('Jumlah pemakaian harus lebih dari 0');
        }

        return DB::transaction(function () use ($idBahan, $jumlahTerpakai) {
            $batches = PengadaanBahan::query()
                ->where('id_bahan', $idBahan)
                ->whereNotNull('tanggal_masuk')
                ->where('stok_tersisa_batch', '>', 0)
                ->orderByRaw('masa_expire_bahan IS NULL, masa_expire_bahan ASC')
                ->lockForUpdate()
                ->get();

            if ($batches->isEmpty()) {
                throw new \Exception('Tidak ada batch yang tersedia untuk bahan ini');
            }

            $totalStokBatch = $batches->sum('stok_tersisa_batch');
            if ($totalStokBatch < $jumlahTerpakai) {
                throw new \Exception("Stok bahan tidak cukup. Tersedia: {$totalStokBatch}, Diminta: {$jumlahTerpakai}");
            }

            $sisaPemakaian = $jumlahTerpakai;
            $batchesUsed = [];

            foreach ($batches as $batch) {
                if ($sisaPemakaian <= 0) {
                    break;
                }

                $ambilDariBatch = min($sisaPemakaian, $batch->stok_tersisa_batch);

                DB::table('pengadaan_bahan')
                    ->where('id', $batch->id)
                    ->where('stok_tersisa_batch', '>=', $ambilDariBatch)
                    ->update(['stok_tersisa_batch' => DB::raw("stok_tersisa_batch - {$ambilDariBatch}")]);

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

            return $batchesUsed;
        });
    }

    public function reverseConsumeFromBatches(int $idBahan, int $jumlahDikembalikan): void
    {
        if ($jumlahDikembalikan <= 0) {
            throw new \Exception('Jumlah pengembalian harus lebih dari 0');
        }

        DB::transaction(function () use ($idBahan, $jumlahDikembalikan) {
            $batches = PengadaanBahan::query()
                ->where('id_bahan', $idBahan)
                ->orderByRaw('masa_expire_bahan IS NULL, masa_expire_bahan DESC')
                ->lockForUpdate()
                ->get();

            $sisaPengembalian = $jumlahDikembalikan;

            foreach ($batches as $batch) {
                if ($sisaPengembalian <= 0) {
                    break;
                }

                $kembalikanKeBatch = $sisaPengembalian;

                DB::table('pengadaan_bahan')
                    ->where('id', $batch->id)
                    ->update(['stok_tersisa_batch' => DB::raw("stok_tersisa_batch + {$kembalikanKeBatch}")]);

                $sisaPengembalian -= $kembalikanKeBatch;
            }
        });
    }

    public function getBatchesForBahan(int $idBahan): \Illuminate\Database\Eloquent\Collection
    {
        return PengadaanBahan::tersediaUrutExpiry($idBahan)->get();
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
        DB::table('pengadaan_bahan')
            ->where('id', $batch->id)
            ->where('stok_tersisa_batch', '>', 0)
            ->update(['stok_tersisa_batch' => 0]);

        return true;
    }
}
