<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\UnitAlat;
use Illuminate\Support\Facades\DB;

class StokService
{
    public function tambahAlatAgregat(Alat $alat, int $jumlah): bool
    {
        if ($jumlah <= 0) {
            throw new \Exception('Jumlah penambahan harus lebih dari 0');
        }

        $updated = DB::table('alat')
            ->where('id', $alat->id)
            ->where('jumlah_alat', '>=', 0)
            ->update(['jumlah_alat' => DB::raw("jumlah_alat + {$jumlah}")]);

        if (!$updated) {
            throw new \Exception('Gagal menambah stok alat');
        }

        return true;
    }

    public function kurangiAlatAgregat(Alat $alat, int $jumlah): bool
    {
        if ($jumlah <= 0) {
            throw new \Exception('Jumlah pengurangan harus lebih dari 0');
        }

        $updated = DB::table('alat')
            ->where('id', $alat->id)
            ->where('jumlah_alat', '>=', $jumlah)
            ->update(['jumlah_alat' => DB::raw("jumlah_alat - {$jumlah}")]);

        if (!$updated) {
            throw new \Exception('Stok alat tidak cukup');
        }

        return true;
    }

    public function tambahBahan(Bahan $bahan, int $jumlah): bool
    {
        if ($jumlah <= 0) {
            throw new \Exception('Jumlah penambahan harus lebih dari 0');
        }

        $updated = DB::table('bahan')
            ->where('id', $bahan->id)
            ->where('stok_saat_ini', '>=', 0)
            ->update(['stok_saat_ini' => DB::raw("stok_saat_ini + {$jumlah}")]);

        if (!$updated) {
            throw new \Exception('Gagal menambah stok bahan');
        }

        return true;
    }

    public function kurangiBahan(Bahan $bahan, int $jumlah): bool
    {
        if ($jumlah <= 0) {
            throw new \Exception('Jumlah pengurangan harus lebih dari 0');
        }

        $updated = DB::table('bahan')
            ->where('id', $bahan->id)
            ->where('stok_saat_ini', '>=', $jumlah)
            ->update(['stok_saat_ini' => DB::raw("stok_saat_ini - {$jumlah}")]);

        if (!$updated) {
            throw new \Exception('Stok bahan tidak cukup');
        }

        return true;
    }

    public function updateUnitStatus(UnitAlat $unit, string $status): bool
    {
        if (!in_array($status, ['tersedia', 'dipinjam', 'rusak', 'maintenance'])) {
            throw new \Exception('Status unit tidak valid');
        }

        $unit->update(['status' => $status]);

        return true;
    }

    public function getStokMinimumItems(): \Illuminate\Database\Eloquent\Collection
    {
        return Bahan::lowStock()->get();
    }

    public function getAvailableAlatQuantity(Alat $alat): int
    {
        if ($alat->isUnitTracked()) {
            return $alat->unitAlat()->where('status', 'tersedia')->count();
        }

        return $alat->jumlah_alat;
    }
}
