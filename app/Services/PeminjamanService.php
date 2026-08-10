<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\PeminjamanAlat;
use App\Models\UnitAlat;
use App\Models\User;

class PeminjamanService
{
    protected StokService $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    public function createBorrowing(array $data): PeminjamanAlat
    {
        try {
            \DB::beginTransaction();

            $peminjaman = PeminjamanAlat::create($data);

            if ($data['id_alat']) {
                $this->stokService->kurangiAlatAgregat(
                    Alat::find($data['id_alat']),
                    $data['jumlah'] ?? 1
                );
            } elseif ($data['id_unit_alat']) {
                $unit = UnitAlat::find($data['id_unit_alat']);
                $this->stokService->updateUnitStatus($unit, 'terpinjam');
            }

            \DB::commit();
            return $peminjaman;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function returnBorrowing(PeminjamanAlat $peminjaman, array $data): bool
    {
        try {
            \DB::beginTransaction();

            $peminjaman->update([
                'waktu_kembali_aktual' => $data['waktu_kembali_aktual'],
                'kondisi_saat_pengembalian' => $data['kondisi_saat_pengembalian'],
                'status' => 'sudah_dikembalikan',
            ]);

            if ($peminjaman->id_alat) {
                $this->stokService->tambahAlatAgregat(
                    Alat::find($peminjaman->id_alat),
                    $peminjaman->jumlah ?? 1
                );
            } elseif ($peminjaman->id_unit_alat) {
                $unit = UnitAlat::find($peminjaman->id_unit_alat);
                $this->stokService->updateUnitStatus($unit, 'tersedia');
                $unit->update(['kondisi_saat_ini' => $data['kondisi_saat_pengembalian']]);
            }

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function getActiveBorrowings(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return PeminjamanAlat::where('id_user_peminjam', $user->id)
            ->where('status', 'terpinjam')
            ->with(['alat', 'unitAlat'])
            ->get();
    }

    public function getOverdueBorrowings(): \Illuminate\Database\Eloquent\Collection
    {
        return PeminjamanAlat::where('status', 'terpinjam')
            ->where('waktu_pengembalian', '<', now())
            ->with(['userPeminjam', 'alat', 'unitAlat'])
            ->get();
    }

    public function checkBorrowingAvailability(int $idAlat = null, int $idUnitAlat = null): array
    {
        $available = true;
        $message = '';

        if ($idAlat) {
            $alat = Alat::find($idAlat);
            if (!$alat) {
                return ['available' => false, 'message' => 'Alat tidak ditemukan'];
            }
            if ($alat->jumlah_alat <= 0) {
                return ['available' => false, 'message' => 'Stok alat tidak tersedia'];
            }
        } elseif ($idUnitAlat) {
            $unit = UnitAlat::find($idUnitAlat);
            if (!$unit) {
                return ['available' => false, 'message' => 'Unit alat tidak ditemukan'];
            }
            if ($unit->status !== 'tersedia') {
                return ['available' => false, 'message' => 'Unit alat sedang tidak tersedia'];
            }
        }

        return ['available' => true, 'message' => 'Alat tersedia untuk dipinjam'];
    }
}
