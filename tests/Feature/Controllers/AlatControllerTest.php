<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Laboratorium;

class AlatControllerTest extends TestCase
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
        $response = $this->get(route('alat.index'));
        if ($response->status() === 302 || $response->status() === 401) {
            return;
        }
        $response->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('alat.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('alat.store'), []);
        $response->assertSessionHasErrors(['nama_alat', 'id_kategori', 'id_labor', 'tipe_pelacakan']);
    }

    public function test_store_creates_alat()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $lab = Laboratorium::factory()->create(['id_user_kalab' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->post(route('alat.store'), [
                'nama_alat' => 'Multimeter Digital',
                'id_kategori' => $kategori->id,
                'id_labor' => $lab->id,
                'tipe_pelacakan' => 'agregat',
                'jumlah_alat' => 5,
            ]);

        $this->assertDatabaseHas('alat', ['nama_alat' => 'Multimeter Digital']);
    }

    public function test_update_modifies_alat()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $alat = Alat::factory()->create(['id_kategori' => $kategori->id, 'nama_alat' => 'Old Name']);

        $this->actingAs($this->admin)
            ->put(route('alat.update', $alat), ['nama_alat' => 'New Name']);

        $this->assertDatabaseHas('alat', ['id' => $alat->id, 'nama_alat' => 'New Name']);
    }

    public function test_destroy_deletes_alat()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $alat = Alat::factory()->create(['id_kategori' => $kategori->id]);

        $this->actingAs($this->admin)
            ->delete(route('alat.destroy', $alat));

        $this->assertSoftDeleted('alat', ['id' => $alat->id]);
    }

    public function test_dosen_cannot_access_alat()
    {
        $this->actingAs($this->dosen)
            ->get(route('alat.index'))
            ->assertForbidden();
    }
}
