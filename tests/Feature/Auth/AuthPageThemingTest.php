<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPageThemingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_themed_background_and_logo(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('images/bgbengkel.png');
        $response->assertSee('images/unplogo.png');
        $response->assertSee('backdrop-blur-md');
    }

    public function test_login_page_shows_orange_submit_button(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('bg-primary');
    }
}