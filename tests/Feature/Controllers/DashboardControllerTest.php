<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;

class DashboardControllerTest extends TestCase
{
    private $admin;
    private $teknisi;
    private $dosen;
    private $kadep;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin_jurusan']);
        $this->teknisi = User::factory()->create(['role' => 'teknisi']);
        $this->dosen = User::factory()->create(['role' => 'dosen']);
        $this->kadep = User::factory()->create(['role' => 'kadep']);
    }

    public function test_admin_can_access_dashboard()
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
    }

    public function test_teknisi_can_access_dashboard()
    {
        $this->actingAs($this->teknisi)->get(route('dashboard'))->assertOk();
    }

    public function test_kadep_can_access_dashboard()
    {
        $this->actingAs($this->kadep)->get(route('dashboard'))->assertOk();
    }

    public function test_dosen_cannot_access_dashboard()
    {
        $this->actingAs($this->dosen)->get(route('dashboard'))->assertForbidden();
    }

    public function test_mahasiswa_cannot_access_dashboard()
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($mahasiswa)->get(route('dashboard'))->assertForbidden();
    }
}
