<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Alat;
use App\Models\UnitAlat;
use App\Models\PeminjamanAlat;
use App\Models\User;

class PeminjamanAlatTest extends TestCase
{
    public function test_peminjaman_belongs_to_alat()
    {
        $alat = Alat::factory()->create();
        $peminjaman = PeminjamanAlat::factory()->create(['id_alat' => $alat->id]);

        $this->assertInstanceOf(Alat::class, $peminjaman->alat);
        $this->assertEquals($alat->id, $peminjaman->alat->id);
    }

    public function test_peminjaman_belongs_to_unit_alat()
    {
        $unit = UnitAlat::factory()->create();
        $peminjaman = PeminjamanAlat::factory()->create(['id_unit_alat' => $unit->id]);

        $this->assertInstanceOf(UnitAlat::class, $peminjaman->unitAlat);
        $this->assertEquals($unit->id, $peminjaman->unitAlat->id);
    }

    public function test_peminjaman_belongs_to_user_peminjam()
    {
        $user = User::factory()->create();
        $peminjaman = PeminjamanAlat::factory()->create(['id_user_peminjam' => $user->id]);

        $this->assertInstanceOf(User::class, $peminjaman->userPeminjam);
        $this->assertEquals($user->id, $peminjaman->userPeminjam->id);
    }

    public function test_peminjaman_status_is_cast()
    {
        $peminjaman = PeminjamanAlat::factory()->create(['status' => 'terpinjam']);

        $this->assertEquals('terpinjam', $peminjaman->status);
    }

    public function test_peminjaman_jumlah_is_cast_to_int()
    {
        $peminjaman = PeminjamanAlat::factory()->create(['jumlah' => 3]);

        $this->assertIsInt($peminjaman->jumlah);
        $this->assertEquals(3, $peminjaman->jumlah);
    }

    public function test_peminjaman_uses_soft_deletes()
    {
        $peminjaman = PeminjamanAlat::factory()->create();
        $peminjaman->delete();

        $this->assertSoftDeleted('peminjaman_alat', ['id' => $peminjaman->id]);
    }
}
