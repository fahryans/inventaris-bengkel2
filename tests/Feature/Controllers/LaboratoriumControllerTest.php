<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Laboratorium;

class LaboratoriumControllerTest extends TestCase
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
        $this->get(route('laboratorium.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('laboratorium.index'))->assertOk();
    }

    public function test_update_modifies_laboratorium()
    {
        $lab = Laboratorium::factory()->create(['nama_labor' => 'Old Name']);

        $this->actingAs($this->admin)
            ->put(route('laboratorium.update', $lab), ['nama_labor' => 'New Name']);

        $this->assertDatabaseHas('laboratorium', ['id' => $lab->id, 'nama_labor' => 'New Name']);
    }

    public function test_destroy_deletes_laboratorium()
    {
        $lab = Laboratorium::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('laboratorium.destroy', $lab));

        $this->assertSoftDeleted('laboratorium', ['id' => $lab->id]);
    }

    public function test_dosen_cannot_access_laboratorium()
    {
        $this->actingAs($this->dosen)
            ->get(route('laboratorium.index'))
            ->assertForbidden();
    }
}
