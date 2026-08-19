<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\PeminjamanAlat;

class UserTest extends TestCase
{
    public function test_user_has_many_peminjaman_alat()
    {
        $user = User::factory()->create();
        PeminjamanAlat::factory()->count(3)->create(['id_user_peminjam' => $user->id]);

        $this->assertCount(3, $user->peminjamanAlat);
    }

    public function test_user_can_be_admin()
    {
        $user = User::factory()->create(['role' => 'admin_jurusan']);

        $this->assertEquals('admin_jurusan', $user->role);
    }

    public function test_user_can_be_dosen()
    {
        $user = User::factory()->create(['role' => 'dosen']);

        $this->assertEquals('dosen', $user->role);
    }

    public function test_user_can_be_mahasiswa()
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $this->assertEquals('mahasiswa', $user->role);
    }

    public function test_user_nama_is_accessible()
    {
        $user = User::factory()->create(['nama' => 'John Doe']);

        $this->assertEquals('John Doe', $user->nama);
    }

    public function test_user_password_is_hidden()
    {
        $user = User::factory()->create();

        $array = $user->toArray();
        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_user_is_active_by_default()
    {
        $user = User::factory()->create();

        $this->assertEquals('aktif', $user->status);
    }
}
