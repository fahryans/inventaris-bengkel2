<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PeminjamanService;
use App\Services\StokService;
use App\Models\Alat;
use App\Models\User;
use App\Models\UnitAlat;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\PengadaanAlat;

class PeminjamanServiceTest extends TestCase
{
    public function test_create_peminjaman_decrements_available_quantity()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $laboratorium = Laboratorium::factory()->create(['id_user_kalab' => $user->id]);
        $alat = Alat::factory()->create([
            'id_kategori' => $kategori->id,
            'id_labor' => $laboratorium->id,
            'tipe_pelacakan' => 'agregat',
        ]);
        PengadaanAlat::factory()->create(['id_alat' => $alat->id, 'jumlah' => 10]);
        $service = new PeminjamanService(new StokService());

        $peminjaman = $service->createBorrowing([
            'id_alat' => $alat->id,
            'id_user_peminjam' => $user->id,
            'jumlah' => 3,
            'keperluan' => 'Praktikum',
            'waktu_peminjaman' => now(),
            'waktu_pengembalian' => now()->addDays(7),
        ]);

        $this->assertEquals(7, $alat->fresh()->getAvailableQuantity());
        $this->assertEquals('terpinjam', $peminjaman->status);
    }

    public function test_return_peminjaman_restores_available_quantity()
    {
        $user = User::factory()->create();
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $laboratorium = Laboratorium::factory()->create(['id_user_kalab' => $user->id]);
        $alat = Alat::factory()->create([
            'id_kategori' => $kategori->id,
            'id_labor' => $laboratorium->id,
            'tipe_pelacakan' => 'agregat',
        ]);
        PengadaanAlat::factory()->create(['id_alat' => $alat->id, 'jumlah' => 10]);
        $peminjaman = \App\Models\PeminjamanAlat::factory()->create([
            'id_alat' => $alat->id,
            'id_user_peminjam' => $user->id,
            'keperluan' => 'Praktikum',
            'jumlah' => 3,
            'status' => 'terpinjam',
            'waktu_peminjaman' => now(),
            'waktu_pengembalian' => now()->addDays(7),
        ]);
        $service = new PeminjamanService(new StokService());

        $this->assertEquals(7, $alat->fresh()->getAvailableQuantity());

        $service->returnBorrowing($peminjaman, ['kondisi_saat_pengembalian' => 'Baik', 'waktu_kembali_aktual' => now()]);

        $this->assertEquals(10, $alat->fresh()->getAvailableQuantity());
        $this->assertEquals('sudah_dikembalikan', $peminjaman->fresh()->status);
    }
}