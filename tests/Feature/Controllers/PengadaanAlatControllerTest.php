<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Alat;
use App\Models\PengadaanAlat;

class PengadaanAlatControllerTest extends TestCase
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
        $this->get(route('pengadaan_alat.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('pengadaan_alat.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('pengadaan_alat.store'), [])
            ->assertSessionHasErrors(['id_alat', 'tanggal_pengadaan', 'harga_perolehan', 'jumlah', 'supplier']);
    }

    public function test_store_creates_pengadaan_alat()
    {
        $alat = Alat::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('pengadaan_alat.store'), [
                'id_alat' => $alat->id,
                'tanggal_pengadaan' => now()->format('Y-m-d'),
                'harga_perolehan' => 100000,
                'jumlah' => 5,
                'supplier' => 'Supplier A',
            ]);

        $this->assertDatabaseHas('pengadaan_alat', ['id_alat' => $alat->id]);
    }

    public function test_mark_received_requires_auth()
    {
        $pengadaan = PengadaanAlat::factory()->create();
        $this->actingAs($this->dosen)
            ->post(route('pengadaan_alat.mark_received', $pengadaan))
            ->assertForbidden();
    }

    public function test_dosen_cannot_access_pengadaan_alat()
    {
        $this->actingAs($this->dosen)
            ->get(route('pengadaan_alat.index'))
            ->assertForbidden();
    }
}
