<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_welcome_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Selamat Datang di Sistem Inventaris Bengkel');
        $response->assertSee('images/bgbengkel.png');
        $response->assertSee(route('login'), false);
    }

    public function test_authenticated_user_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
