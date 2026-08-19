<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
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
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('users.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [])
            ->assertSessionHasErrors(['role', 'nama', 'email', 'password']);
    }

    public function test_store_creates_user()
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'role' => 'teknisi',
                'nama' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'status' => 'aktif',
                'no_hp' => '08123456789',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_dosen_cannot_access_users()
    {
        $this->actingAs($this->dosen)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_mahasiswa_cannot_create_user()
    {
        $this->actingAs($this->mahasiswa)
            ->post(route('users.store'), [
                'role' => 'teknisi',
                'nama' => 'Test',
                'email' => 'test2@example.com',
                'password' => 'password123',
            ])
            ->assertForbidden();
    }
}
