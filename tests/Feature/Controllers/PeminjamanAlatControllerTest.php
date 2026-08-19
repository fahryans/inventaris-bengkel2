<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Alat;
use App\Models\PeminjamanAlat;

class PeminjamanAlatControllerTest extends TestCase
{
    private $admin;
    private $teknisi;
    private $dosen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin_jurusan']);
        $this->teknisi = User::factory()->create(['role' => 'teknisi']);
        $this->dosen = User::factory()->create(['role' => 'dosen']);
    }

    public function test_index_requires_auth()
    {
        $this->get(route('peminjaman.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('peminjaman.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('peminjaman.store'), [])
            ->assertSessionHasErrors(['keperluan', 'waktu_peminjaman', 'kondisi_saat_peminjaman']);
    }

    public function test_store_creates_peminjaman()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'agregat']);

        $this->actingAs($this->admin)
            ->post(route('peminjaman.store'), [
                'id_alat' => $alat->id,
                'keperluan' => 'Pengujian',
                'waktu_peminjaman' => now()->format('Y-m-d H:i'),
                'kondisi_saat_peminjaman' => 'baik',
            ]);

        $this->assertDatabaseHas('peminjaman_alat', ['keperluan' => 'Pengujian']);
    }

    public function test_dosen_cannot_access_peminjaman()
    {
        $this->actingAs($this->dosen)
            ->get(route('peminjaman.index'))
            ->assertForbidden();
    }
}
