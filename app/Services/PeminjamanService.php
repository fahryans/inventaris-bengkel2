<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\PeminjamanAlat;
use App\Models\UnitAlat;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PeminjamanService
{
    protected StokService $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    public function createBorrowing(array $data): PeminjamanAlat
    {
        $idAlat = $data['id_alat'] ?? null;
        $idUnitAlat = $data['id_unit_alat'] ?? null;

        if (!$idAlat && !$idUnitAlat) {
            throw new \Exception('Harus memilih salah satu: alat atau unit alat');
        }

        if ($idAlat && $idUnitAlat) {
            throw new \Exception('Hanya boleh memilih salah satu: alat atau unit alat');
        }

        $availability = $this->checkBorrowingAvailability($idAlat, $idUnitAlat);
        if (!$availability['available']) {
            throw new \Exception($availability['message']);
        }

        return DB::transaction(function () use ($data, $idAlat, $idUnitAlat) {
            $data['status'] = 'terpinjam';
            $data['jumlah'] = $data['jumlah'] ?? 1;

            $peminjaman = PeminjamanAlat::create($data);

            if ($idAlat) {
                $alat = Alat::findOrFail($idAlat);
                $this->stokService->kurangiAlatAgregat($alat, $peminjaman->jumlah);
            } elseif ($idUnitAlat) {
                $unit = UnitAlat::findOrFail($idUnitAlat);

                $taken = DB::table('unit_alat')
                    ->where('id', $unit->id)
                    ->where('status', 'tersedia')
                    ->update(['status' => 'dipinjam']);

                if (!$taken) {
                    throw new \Exception('Unit alat sedang tidak tersedia');
                }
            }

            return $peminjaman;
        });
    }

    public function returnBorrowing(PeminjamanAlat $peminjaman, array $data): bool
    {
        if ($peminjaman->status !== 'terpinjam') {
            throw new \Exception('Peminjaman sudah dikembalikan sebelumnya');
        }

        return DB::transaction(function () use ($peminjaman, $data) {
            $peminjaman->update([
                'waktu_kembali_aktual' => $data['waktu_kembali_aktual'],
                'kondisi_saat_pengembalian' => $data['kondisi_saat_pengembalian'],
                'status' => 'sudah_dikembalikan',
            ]);

            if ($peminjaman->id_alat) {
                $alat = Alat::findOrFail($peminjaman->id_alat);
                $this->stokService->tambahAlatAgregat($alat, $peminjaman->jumlah ?? 1);
            } elseif ($peminjaman->id_unit_alat) {
                $unit = UnitAlat::findOrFail($peminjaman->id_unit_alat);
                $this->stokService->updateUnitStatus($unit, 'tersedia');
                $unit->update(['kondisi_saat_ini' => $data['kondisi_saat_pengembalian']]);
            }

            return true;
        });
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

    public function checkBorrowingAvailability(?int $idAlat, ?int $idUnitAlat): array
    {
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
