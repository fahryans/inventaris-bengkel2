# Themed Auth Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Theme all auth pages (login and friends) with the bengkel background, glassmorphism card, orange buttons, and UNP logo.

**Architecture:** Style the shared guest layout and two Blade components. Because every auth view uses `layouts/guest.blade.php`, `primary-button`, and `input-label`, theming those three themes all auth pages at once. No auth logic or form content changes.

**Tech Stack:** Laravel Blade, Tailwind CSS v3 (project `primary` = `#FF6B35`).

## Global Constraints

- Touch only these files: `resources/views/layouts/guest.blade.php`, `resources/views/components/primary-button.blade.php`, `resources/views/components/input-label.blade.php`, plus the new test.
- No auth logic, controller, route, or form-content changes.
- Existing auth feature tests (`tests/Feature/Auth/*`) must keep passing.
- Logo/background come from `public/images/` via `asset()`.
- Button uses `bg-primary` (`#FF6B35`), hover/active `orange-600`/`orange-700`.

---

### Task 1: Theme the guest layout and shared components

**Files:**
- Modify: `resources/views/layouts/guest.blade.php`
- Modify: `resources/views/components/primary-button.blade.php`
- Modify: `resources/views/components/input-label.blade.php`
- Test: `tests/Feature/Auth/AuthPageThemingTest.php`

**Interfaces:**
- Produces: `layouts/guest` renders bengkel background, dark overlay, glass card, and UNP logo above the card.
- Produces: `primary-button` renders with `bg-primary` orange background.
- Produces: `input-label` renders with light text (`text-gray-100`).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Auth/AuthPageThemingTest.php`:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AuthPageThemingTest`
Expected: FAIL — `assertSee` finds neither `images/bgbengkel.png` nor `backdrop-blur-md` (current guest layout uses plain white card, no logo/background).

- [ ] **Step 3: Rewrite guest layout**

Replace the body of `resources/views/layouts/guest.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-white antialiased">
        <div class="min-h-screen flex items-center justify-center px-6 py-12 relative"
             style="background-image: url('{{ asset('images/bgbengkel.png') }}'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-black/60 z-0"></div>

            <div class="relative z-10 w-full flex flex-col items-center">
                <img src="{{ asset('images/unplogo.png') }}" alt="Logo UNP"
                     class="w-24 h-24 object-contain mb-6 drop-shadow-lg">

                <div class="w-full sm:max-w-md px-8 py-8 bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl shadow-2xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
```

- [ ] **Step 4: Restyle primary-button to orange**

Replace `resources/views/components/primary-button.blade.php`:

```blade
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#B45F06] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
```

- [ ] **Step 5: Restyle input-label for dark background**

Replace `resources/views/components/input-label.blade.php`:

```blade
@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-100']) }}>
    {{ $value ?? $slot }}
</label>
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=AuthPageThemingTest`
Expected: PASS (2 tests).

- [ ] **Step 7: Run full auth test suite**

Run: `php artisan test --filter=Auth`
Expected: PASS — all existing auth tests (Authentication, PasswordReset, EmailVerification, PasswordConfirmation, PasswordUpdate) plus the 2 new theming tests.

- [ ] **Step 8: Run full suite**

Run: `php artisan test`
Expected: PASS (177 + 2 = 179 tests).

- [ ] **Step 9: Commit**

```bash
git add resources/views/layouts/guest.blade.php resources/views/components/primary-button.blade.php resources/views/components/input-label.blade.php tests/Feature/Auth/AuthPageThemingTest.php
git commit -m "style: theme auth pages with bengkel landing theme"
```

---

## Manual Verification

1. Visit `/login` → bengkel background, dark overlay, UNP logo above a frosted glass card.
2. Form fields: labels light, inputs readable, focus ring orange `#B45F06`.
3. Submit button orange `#FF6B35`, hover darker.
4. Visit `/register`, `/forgot-password`, `/reset-password/xyz`, `/confirm-password` → same theme applied.
5. Log in → redirected to dashboard.