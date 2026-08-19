<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class SecurityGuardTest extends TestCase
{
    public function test_kadep_cannot_promote_self_to_admin()
    {
        $kadep = User::factory()->create(['role' => 'kadep']);

        $this->actingAs($kadep)
            ->put(route('users.update', $kadep), [
                'nama' => $kadep->nama,
                'email' => $kadep->email,
                'role' => 'admin_jurusan',
                'status' => 'aktif',
            ])
            ->assertSessionHasErrors('role');

        $this->assertEquals('kadep', $kadep->fresh()->role);
    }

    public function test_kepala_labor_cannot_promote_self_to_admin()
    {
        $kalab = User::factory()->create(['role' => 'kepala_labor']);

        $this->actingAs($kalab)
            ->put(route('users.update', $kalab), [
                'nama' => $kalab->nama,
                'email' => $kalab->email,
                'role' => 'admin_jurusan',
                'status' => 'aktif',
            ])
            ->assertSessionHasErrors('role');

        $this->assertEquals('kepala_labor', $kalab->fresh()->role);
    }

    public function test_inactive_user_cannot_login_web()
    {
        $user = User::factory()->create(['status' => 'tidak_aktif']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}