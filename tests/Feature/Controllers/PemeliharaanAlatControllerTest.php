<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\UnitAlat;
use App\Models\PemeliharaanAlat;

class PemeliharaanAlatControllerTest extends TestCase
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
        $this->get(route('pemeliharaan.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('pemeliharaan.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('pemeliharaan.store'), [])
            ->assertSessionHasErrors(['id_unit_alat', 'id_teknisi', 'tanggal_cek', 'tanggal_cek_berikutnya', 'kondisi']);
    }

    public function test_store_creates_pemeliharaan_alat()
    {
        $unitAlat = UnitAlat::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('pemeliharaan.store'), [
                'id_unit_alat' => $unitAlat->id,
                'id_teknisi' => $this->teknisi->id,
                'tanggal_cek' => now()->format('Y-m-d'),
                'tanggal_cek_berikutnya' => now()->addDays(30)->format('Y-m-d'),
                'kondisi' => 'baik',
            ]);

        $this->assertDatabaseHas('pemeliharaan_alat', ['id_unit_alat' => $unitAlat->id]);
    }

    public function test_dosen_cannot_access_pemeliharaan_alat()
    {
        $this->actingAs($this->dosen)
            ->get(route('pemeliharaan.index'))
            ->assertForbidden();
    }
}
