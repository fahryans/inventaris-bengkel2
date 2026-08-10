<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\UnitAlat;
use Illuminate\Database\QueryException;

class StokService
{
    public function tambahAlatAgregat(Alat $alat, int $jumlah): bool
    {
        try {
            \DB::beginTransaction();
            
            if ($alat->jumlah_alat + $jumlah < 0) {
                throw new \Exception('Stok alat tidak boleh negatif');
            }

            $alat->increment('jumlah_alat', $jumlah);
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function kurangiAlatAgregat(Alat $alat, int $jumlah): bool
    {
        try {
            \DB::beginTransaction();
            
            if ($alat->jumlah_alat - $jumlah < 0) {
                throw new \Exception('Stok alat tidak cukup');
            }

            $alat->decrement('jumlah_alat', $jumlah);
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function tambahBahan(Bahan $bahan, int $jumlah): bool
    {
        try {
            \DB::beginTransaction();
            
            if ($bahan->stok_saat_ini + $jumlah < 0) {
                throw new \Exception('Stok bahan tidak boleh negatif');
            }

            $bahan->increment('stok_saat_ini', $jumlah);
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function kurangiBahan(Bahan $bahan, int $jumlah): bool
    {
        try {
            \DB::beginTransaction();
            
            if ($bahan->stok_saat_ini - $jumlah < 0) {
                throw new \Exception('Stok bahan tidak cukup');
            }

            $bahan->decrement('stok_saat_ini', $jumlah);
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function updateUnitStatus(UnitAlat $unit, string $status): bool
    {
        try {
            \DB::beginTransaction();
            
            if (!in_array($status, ['tersedia', 'terpinjam', 'rusak'])) {
                throw new \Exception('Status unit tidak valid');
            }

            $unit->update(['status' => $status]);
            
            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function getStokMinimumItems(): \Illuminate\Database\Eloquent\Collection
    {
        return Bahan::whereColumn('stok_saat_ini', '<=', 'stok_minimum')->get();
    }

    public function getAvailableAlatQuantity(Alat $alat): int
    {
        if ($alat->isUnitTracked()) {
            return $alat->unitAlat()->where('status', 'tersedia')->count();
        }
        return $alat->jumlah_alat;
    }
}
