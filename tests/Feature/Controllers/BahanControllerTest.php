<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;

class BahanControllerTest extends TestCase
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
        $this->get(route('bahan.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('bahan.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('bahan.store'), [])
            ->assertSessionHasErrors(['nama_bahan', 'id_kategori', 'id_labor', 'stok_minimum', 'satuan']);
    }

    public function test_store_creates_bahan()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'bahan']);
        $lab = Laboratorium::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('bahan.store'), [
                'nama_bahan' => 'Minyak Mesin',
                'id_kategori' => $kategori->id,
                'id_labor' => $lab->id,
                'stok_minimum' => 10,
                'satuan' => 'liter',
            ]);

        $this->assertDatabaseHas('bahan', ['nama_bahan' => 'Minyak Mesin']);
    }

public function test_update_modifies_bahan()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'bahan']);
        $lab = Laboratorium::factory()->create();
        $bahan = Bahan::factory()->create([
            'nama_bahan' => 'Old Name',
            'id_kategori' => $kategori->id,
            'id_labor' => $lab->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('bahan.update', $bahan), [
                'nama_bahan' => 'New Name',
                'id_kategori' => $kategori->id,
                'id_labor' => $lab->id,
                'stok_minimum' => $bahan->stok_minimum,
                'satuan' => $bahan->satuan,
            ]);

        $this->assertDatabaseHas('bahan', ['id' => $bahan->id, 'nama_bahan' => 'New Name']);
    }

    public function test_destroy_deletes_bahan()
    {
        $bahan = Bahan::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('bahan.destroy', $bahan));

        $this->assertSoftDeleted('bahan', ['id' => $bahan->id]);
    }

    public function test_dosen_cannot_access_bahan()
    {
        $this->actingAs($this->dosen)
            ->get(route('bahan.index'))
            ->assertForbidden();
    }
}
