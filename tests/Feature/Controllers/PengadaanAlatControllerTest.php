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
            ->assertSessionHasErrors(['id_alat', 'id_spesifikasi_alat', 'tanggal_pengadaan', 'harga_perolehan', 'jumlah', 'supplier']);
    }

    public function test_store_creates_pengadaan_alat()
    {
        $alat = Alat::factory()->create();
        $spesifikasi = \App\Models\SpesifikasiAlat::factory()->create(['id_alat' => $alat->id]);

        $this->actingAs($this->admin)
            ->post(route('pengadaan_alat.store'), [
                'id_alat' => $alat->id,
                'id_spesifikasi_alat' => $spesifikasi->id,
                'tanggal_pengadaan' => now()->format('Y-m-d'),
                'harga_perolehan' => 100000,
                'jumlah' => 5,
                'supplier' => 'Supplier A',
            ]);

        $this->assertDatabaseHas('pengadaan_alat', ['id_alat' => $alat->id, 'id_spesifikasi_alat' => $spesifikasi->id]);
    }

    public function test_mark_received_requires_auth()
    {
        $this->actingAs($this->dosen)
            ->post(route('pengadaan_alat.mark_received', 1))
            ->assertForbidden();
    }

public function test_dosen_cannot_access_pengadaan_alat()
    {
        $this->actingAs($this->dosen)
            ->get(route('pengadaan_alat.index'))
            ->assertForbidden();
    }

    public function test_mark_received_sets_received_units_available_for_unit_tracked_alat()
    {
        $kategori = \App\Models\Kategori::factory()->create(['jenis' => 'alat']);
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'unit', 'id_kategori' => $kategori->id]);
        \App\Models\UnitAlat::factory()->count(3)->create([
            'id_alat' => $alat->id,
            'kode_inventaris' => null,
            'status' => 'maintenance',
        ]);
        $pengadaan = PengadaanAlat::factory()->create([
            'id_alat' => $alat->id,
            'jumlah' => 3,
            'tanggal_masuk' => null,
        ]);

        $this->actingAs($this->admin)
            ->post(route('pengadaan_alat.mark_received', $pengadaan), [
                'tanggal_masuk' => now()->format('Y-m-d'),
            ]);

        $this->assertEquals(3, $alat->fresh()->unitAlat()->count());
        $this->assertEquals(3, $alat->fresh()->unitAlat()->where('status', 'tersedia')->count());
        $this->assertNotNull($pengadaan->fresh()->tanggal_masuk);
    }

    public function test_mark_received_tracks_aggregate_stock_via_pengadaan_record()
    {
        $kategori = \App\Models\Kategori::factory()->create(['jenis' => 'alat']);
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'agregat', 'id_kategori' => $kategori->id]);
        $pengadaan = PengadaanAlat::factory()->create([
            'id_alat' => $alat->id,
            'jumlah' => 5,
            'tanggal_masuk' => null,
        ]);

        $this->actingAs($this->admin)
            ->post(route('pengadaan_alat.mark_received', $pengadaan), [
                'tanggal_masuk' => now()->format('Y-m-d'),
            ]);

        $this->assertEquals(5, $alat->fresh()->getAvailableQuantity());
    }
}
