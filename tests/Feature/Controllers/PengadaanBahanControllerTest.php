<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bahan;
use App\Models\PengadaanBahan;

class PengadaanBahanControllerTest extends TestCase
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
        $this->get(route('pengadaan_bahan.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('pengadaan_bahan.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('pengadaan_bahan.store'), [])
            ->assertSessionHasErrors(['id_bahan', 'tanggal_pengadaan', 'harga_perolehan', 'jumlah', 'stok_tersisa_batch', 'supplier']);
    }

    public function test_store_creates_pengadaan_bahan()
    {
        $bahan = Bahan::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('pengadaan_bahan.store'), [
                'id_bahan' => $bahan->id,
                'tanggal_pengadaan' => now()->format('Y-m-d'),
                'harga_perolehan' => 50000,
                'jumlah' => 10,
                'stok_tersisa_batch' => 10,
                'supplier' => 'Supplier A',
            ]);

        $this->assertDatabaseHas('pengadaan_bahan', ['id_bahan' => $bahan->id]);
    }

    public function test_mark_received_requires_auth()
    {
        $pengadaan = PengadaanBahan::factory()->create();
        $this->actingAs($this->dosen)
            ->post(route('pengadaan_bahan.mark_received', $pengadaan))
            ->assertForbidden();
    }

    public function test_dosen_cannot_access_pengadaan_bahan()
    {
        $this->actingAs($this->dosen)
            ->get(route('pengadaan_bahan.index'))
            ->assertForbidden();
    }
}
