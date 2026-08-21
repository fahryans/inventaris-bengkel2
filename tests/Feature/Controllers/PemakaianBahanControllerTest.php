<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bahan;
use App\Models\PengadaanBahan;
use App\Models\PemakaianBahan;

class PemakaianBahanControllerTest extends TestCase
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
        $this->get(route('pemakaian_bahan.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('pemakaian_bahan.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('pemakaian_bahan.store'), [])
            ->assertSessionHasErrors(['id_bahan', 'id_pengadaan_bahan', 'keperluan', 'jumlah_pengambilan', 'jumlah_terpakai', 'waktu_pemakaian']);
    }

    public function test_store_creates_pemakaian_bahan()
    {
        $bahan = Bahan::factory()->create();
        $pengadaan = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 10,
            'stok_tersisa_batch' => 10,
        ]);

        $this->actingAs($this->admin)
            ->post(route('pemakaian_bahan.store'), [
                'id_bahan' => $bahan->id,
                'id_pengadaan_bahan' => $pengadaan->id,
                'keperluan' => 'penggunaan rutin',
                'jumlah_pengambilan' => 5,
                'jumlah_terpakai' => 5,
                'waktu_pemakaian' => now()->format('Y-m-d H:i'),
            ])
            ->assertRedirect(route('pemakaian_bahan.index'));

        $this->assertDatabaseHas('pemakaian_bahan', ['keperluan' => 'penggunaan rutin']);
    }

    public function test_dosen_cannot_access_pemakaian_bahan()
    {
        $this->actingAs($this->dosen)
            ->get(route('pemakaian_bahan.index'))
            ->assertForbidden();
    }
}
