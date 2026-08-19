<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Alat;
use App\Models\UnitAlat;

class UnitAlatControllerTest extends TestCase
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
        $this->get(route('unit-alat.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('unit-alat.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('unit-alat.store'), [])
            ->assertSessionHasErrors(['id_alat', 'kode_inventaris', 'kondisi_saat_ini', 'status']);
    }

    public function test_store_creates_unit_alat()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'unit']);

        $this->actingAs($this->admin)
            ->post(route('unit-alat.store'), [
                'id_alat' => $alat->id,
                'kode_inventaris' => 'INV-001',
                'kondisi_saat_ini' => 'baik',
                'status' => 'tersedia',
            ]);

        $this->assertDatabaseHas('unit_alat', ['kode_inventaris' => 'INV-001']);
    }

public function test_update_modifies_unit_alat()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'unit']);
        $unitAlat = UnitAlat::factory()->create(['kode_inventaris' => 'INV-002', 'id_alat' => $alat->id]);

        $this->actingAs($this->admin)
            ->put(route('unit-alat.update', $unitAlat), [
                'id_alat' => $alat->id,
                'kode_inventaris' => 'INV-003',
                'kondisi_saat_ini' => $unitAlat->kondisi_saat_ini,
                'status' => $unitAlat->status,
            ]);

        $this->assertDatabaseHas('unit_alat', ['id' => $unitAlat->id, 'kode_inventaris' => 'INV-003']);
    }

    public function test_destroy_deletes_unit_alat()
    {
        $unitAlat = UnitAlat::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('unit-alat.destroy', $unitAlat));

        $this->assertSoftDeleted('unit_alat', ['id' => $unitAlat->id]);
    }

    public function test_dosen_cannot_access_unit_alat()
    {
        $this->actingAs($this->dosen)
            ->get(route('unit-alat.index'))
            ->assertForbidden();
    }
}
