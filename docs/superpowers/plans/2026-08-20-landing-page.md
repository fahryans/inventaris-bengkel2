# Landing Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Show a guest-visible landing/welcome page at `/` using `bgbengkel.png` and `unplogo.png`, with a "Masuk" button to the login page.

**Architecture:** Change the root route in `routes/web.php` to render a new standalone Blade view `welcome` for guests, redirecting authenticated users to the dashboard. Static images move from the project root into `public/images/`.

**Tech Stack:** Laravel 10/11 (Breeze auth), Blade, Tailwind CSS, PHPUnit.

## Global Constraints

- Do not touch the existing login page or any dashboard/view except `routes/web.php`, `resources/views/welcome.blade.php`, and moving the two PNG files.
- Copy rule: title text = `Selamat Datang di Sistem Inventaris Bengkel`; button label = `Masuk`.
- Images must be served from `public/images/` — never reference the project root path.
- No new composer/npm dependencies.

---

### Task 1: Move images into public/

**Files:**
- Move: `bgbengkel.png` → `public/images/bgbengkel.png`
- Move: `unplogo.png` → `public/images/unplogo.png`

**Interfaces:**
- Produces: two files at `public/images/` referenced later in the Blade view as `asset('images/bgbengkel.png')` and `asset('images/unplogo.png')`.

- [ ] **Step 1: Create public/images/ and move both PNGs**

```bash
New-Item -ItemType Directory -Force -Path public\images
Move-Item -Force bgbengkel.png public\images\bgbengkel.png
Move-Item -Force unplogo.png public\images\unplogo.png
```

- [ ] **Step 2: Verify files exist in public/images/**

Run: `Get-ChildItem public\images`
Expected: `bgbengkel.png` and `unplogo.png` listed; root files gone.

- [ ] **Step 3: Commit**

```bash
git add -A public/images
git commit -m "move landing images into public/images"
```

---

### Task 2: Landing route and welcome view

**Files:**
- Modify: `routes/web.php:21-23`
- Create: `resources/views/welcome.blade.php`
- Test: `tests/Feature/WelcomePageTest.php`

**Interfaces:**
- Produces: `GET /` → welcome view for guests, `redirect()->route('dashboard')` for authenticated users.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/WelcomePageTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=WelcomePageTest`
Expected: FAIL — `AssertionError`: response is a redirect to `/dashboard`, not 200, and `welcome` view undefined.

- [ ] **Step 3: Implement the route**

Replace the root closure in `routes/web.php:21-23`:

```php
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});
```

- [ ] **Step 4: Create the welcome view**

Create `resources/views/welcome.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Selamat Datang</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-white antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center text-center px-6"
         style="background-image: url('{{ asset('images/bgbengkel.png') }}'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-black/50"></div>

        <div class="relative">
            <img src="{{ asset('images/unplogo.png') }}" alt="Logo UNP" class="w-28 h-28 object-contain mx-auto mb-6">

            <h1 class="text-4xl font-bold mb-4">Selamat Datang di Sistem Inventaris Bengkel</h1>

            <p class="max-w-2xl mx-auto text-lg mb-8">
                Sistem pengelolaan inventaris alat dan bahan bengkel yang memudahkan
                pengawasan, pemeliharaan, peminjaman, dan pelaporan aset bengkel secara terpusat.
            </p>

            <a href="{{ route('login') }}"
               class="inline-block bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-8 py-3 rounded-lg shadow-lg transition">
                Masuk
            </a>
        </div>
    </div>
</body>
</html>
```

Project uses Tailwind v3.1+ (JIT), so `bg-black/50` opacity syntax is supported. No build verification needed beyond the test.

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=WelcomePageTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add routes/web.php resources/views/welcome.blade.php tests/Feature/WelcomePageTest.php
git commit -m "feat: add landing welcome page at root"
```

---

## Manual Verification

1. Clear cached views: `php artisan view:clear`.
2. Visit `/` logged out → landing page shows background, UNP logo, welcome text, "Masuk" button.
3. Click "Masuk" → login page.
4. Log in → dashboard. Visit `/` again → redirects to dashboard.