<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Kategori;

class KategoriControllerTest extends TestCase
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
        $this->get(route('kategori.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('kategori.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('kategori.store'), [])
            ->assertSessionHasErrors(['nama_kategori', 'jenis']);
    }

    public function test_store_creates_kategori()
    {
        $this->actingAs($this->admin)
            ->post(route('kategori.store'), [
                'nama_kategori' => 'Alat Berat',
                'jenis' => 'alat',
            ]);

        $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Alat Berat']);
    }

public function test_update_modifies_kategori()
    {
        $kategori = Kategori::factory()->create(['nama_kategori' => 'Old Name', 'jenis' => 'alat']);

        $this->actingAs($this->admin)
            ->put(route('kategori.update', $kategori), ['nama_kategori' => 'New Name', 'jenis' => 'alat']);

        $this->assertDatabaseHas('kategori', ['id' => $kategori->id, 'nama_kategori' => 'New Name']);
    }

public function test_destroy_deletes_kategori()
    {
        $kategori = Kategori::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('kategori.destroy', $kategori));

        $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
    }

    public function test_dosen_cannot_access_kategori()
    {
        $this->actingAs($this->dosen)
            ->get(route('kategori.index'))
            ->assertForbidden();
    }
}
