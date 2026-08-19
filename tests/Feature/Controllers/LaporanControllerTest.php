<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;

class LaporanControllerTest extends TestCase
{
    private $admin;
    private $teknisi;
    private $dosen;
    private $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin_jurusan']);
        $this->teknisi = User::factory()->create(['role' => 'teknisi']);
        $this->dosen = User::factory()->create(['role' => 'dosen']);
        $this->mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
    }

    public function test_index_requires_auth()
    {
        $this->get(route('laporan.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('laporan.index'))->assertOk();
    }

    public function test_show_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('laporan.show', 'alat'))->assertOk();
    }

    public function test_show_returns_200_for_teknisi()
    {
        $this->actingAs($this->teknisi)->get(route('laporan.show', 'alat'))->assertOk();
    }

    public function test_dosen_can_access_laporan_index()
    {
        $this->actingAs($this->dosen)->get(route('laporan.index'))->assertOk();
    }

    public function test_mahasiswa_can_access_laporan_show()
    {
        $this->actingAs($this->mahasiswa)->get(route('laporan.show', 'alat'))->assertOk();
    }

    public function test_export_requires_auth()
    {
        $this->post(route('laporan.export', 'alat'))->assertRedirect(route('login'));
    }

    public function test_admin_can_export_laporan()
    {
        $this->actingAs($this->admin)->post(route('laporan.export', 'alat'))->assertOk();
    }
}
