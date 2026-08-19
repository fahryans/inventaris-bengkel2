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
            ->assertSessionHasErrors(['id_bahan', 'tanggal_pengadaan', 'harga_perolehan', 'jumlah', 'supplier']);
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
                'supplier' => 'Supplier A',
            ]);

        $this->assertDatabaseHas('pengadaan_bahan', ['id_bahan' => $bahan->id]);
    }

    public function test_mark_received_requires_auth()
    {
        $this->actingAs($this->dosen)
            ->post(route('pengadaan_bahan.mark_received', 1))
            ->assertForbidden();
    }

public function test_dosen_cannot_access_pengadaan_bahan()
    {
        $this->actingAs($this->dosen)
            ->get(route('pengadaan_bahan.index'))
            ->assertForbidden();
    }

    public function test_destroy_received_pengadaan_reverses_stock()
    {
        $bahan = Bahan::factory()->create(['stok_saat_ini' => 20]);
        $pengadaan = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 10,
            'stok_tersisa_batch' => 10,
            'tanggal_masuk' => now(),
        ]);
        $bahan->update(['stok_saat_ini' => 30]);

        $this->actingAs($this->admin)
            ->delete(route('pengadaan_bahan.destroy', $pengadaan));

        $this->assertDatabaseMissing('pengadaan_bahan', ['id' => $pengadaan->id]);
        $this->assertEquals(20, $bahan->fresh()->stok_saat_ini);
    }

    public function test_update_received_pengadaan_jumlah_adjusts_stock()
    {
        $bahan = Bahan::factory()->create(['stok_saat_ini' => 30]);
        $pengadaan = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 10,
            'stok_tersisa_batch' => 10,
            'tanggal_masuk' => now(),
        ]);

        $this->actingAs($this->admin)
            ->put(route('pengadaan_bahan.update', $pengadaan), [
                'id_bahan' => $bahan->id,
                'tanggal_pengadaan' => now()->format('Y-m-d'),
                'harga_perolehan' => 50000,
                'jumlah' => 15,
                'supplier' => 'Supplier A',
            ]);

        $this->assertEquals(35, $bahan->fresh()->stok_saat_ini);
        $this->assertEquals(15, $pengadaan->fresh()->stok_tersisa_batch);
    }

    public function test_update_received_pengadaan_rejects_below_used_amount()
    {
        $bahan = Bahan::factory()->create(['stok_saat_ini' => 30]);
        $pengadaan = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 10,
            'stok_tersisa_batch' => 5,
            'tanggal_masuk' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('pengadaan_bahan.edit', $pengadaan))
            ->assertOk();

        $this->actingAs($this->admin)
            ->put(route('pengadaan_bahan.update', $pengadaan), [
                'id_bahan' => $bahan->id,
                'tanggal_pengadaan' => now()->format('Y-m-d'),
                'harga_perolehan' => 50000,
                'jumlah' => 3,
                'supplier' => 'Supplier A',
            ])
            ->assertSessionHasErrors();

        $this->assertEquals(30, $bahan->fresh()->stok_saat_ini);
        $this->assertEquals(5, $pengadaan->fresh()->stok_tersisa_batch);
    }
}
