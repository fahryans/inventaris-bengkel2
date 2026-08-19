<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Bahan;
use App\Models\UnitAlat;
use App\Models\PemakaianBahan;
use App\Models\PengadaanBahan;
use App\Models\User;

class PemakaianBahanTest extends TestCase
{
    public function test_pemakaian_belongs_to_bahan()
    {
        $bahan = Bahan::factory()->create();
        $pemakaian = PemakaianBahan::factory()->create(['id_bahan' => $bahan->id]);

        $this->assertInstanceOf(Bahan::class, $pemakaian->bahan);
        $this->assertEquals($bahan->id, $pemakaian->bahan->id);
    }

    public function test_pemakaian_belongs_to_pengadaan_bahan()
    {
        $bahan = Bahan::factory()->create();
        $pengadaan = PengadaanBahan::factory()->create(['id_bahan' => $bahan->id]);
        $pemakaian = PemakaianBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'id_pengadaan_bahan' => $pengadaan->id,
        ]);

        $this->assertInstanceOf(PengadaanBahan::class, $pemakaian->pengadaanBahan);
        $this->assertEquals($pengadaan->id, $pemakaian->pengadaanBahan->id);
    }

    public function test_pemakaian_belongs_to_user_pemakai()
    {
        $user = User::factory()->create();
        $pemakaian = PemakaianBahan::factory()->create(['id_user_pemakai' => $user->id]);

        $this->assertInstanceOf(User::class, $pemakaian->userPemakai);
        $this->assertEquals($user->id, $pemakaian->userPemakai->id);
    }

    public function test_pemakaian_belongs_to_user_verifikasi()
    {
        $user = User::factory()->create();
        $pemakaian = PemakaianBahan::factory()->create(['id_user_verifikasi' => $user->id]);

        $this->assertInstanceOf(User::class, $pemakaian->userVerifikasi);
        $this->assertEquals($user->id, $pemakaian->userVerifikasi->id);
    }

    public function test_pemakaian_jumlah_is_cast_to_int()
    {
        $pemakaian = PemakaianBahan::factory()->create([
            'jumlah_pengambilan' => 20,
            'jumlah_terpakai' => 15,
            'jumlah_pengembalian' => 3,
        ]);

        $this->assertIsInt($pemakaian->jumlah_pengambilan);
        $this->assertEquals(20, $pemakaian->jumlah_pengambilan);
    }

    public function test_pemakaian_null_verifikator()
    {
        $pemakaian = PemakaianBahan::factory()->create(['id_user_verifikasi' => null]);

        $this->assertNull($pemakaian->id_user_verifikasi);
    }
}
