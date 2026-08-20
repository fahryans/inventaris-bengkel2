# Landing Page UI Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Re-layout the landing page into a two-column composition (text left, enlarged logo right, orange LOGIN button pinned top-left) with larger, bolder, legible typography over a darker overlay.

**Architecture:** Single-file change to `resources/views/welcome.blade.php`. Tailwind layout only; routes, text content, and the existing feature test remain untouched.

**Tech Stack:** Laravel Blade, Tailwind CSS v3 (project `primary` color = `#FF6B35`).

## Global Constraints

- Touch only `resources/views/welcome.blade.php` — no route, controller, or test changes.
- Title text stays exactly: `Selamat Datang di Sistem Inventaris Bengkel`.
- Description text stays exactly as currently written.
- Use Tailwind classes only; no new dependencies.
- Existing `tests/Feature/WelcomePageTest.php` must keep passing (asserts title, `images/bgbengkel.png`, login route).

---

### Task 1: Re-layout the welcome view

**Files:**
- Modify: `resources/views/welcome.blade.php`
- Test: `tests/Feature/WelcomePageTest.php` (unchanged, run as gate)

**Consumes:** Nothing new — assets already at `public/images/`.
**Produces:** Restyled landing page view.

- [ ] **Step 1: Assert the existing test passes before changes**

Run: `php artisan test --filter=WelcomePageTest`
Expected: PASS (2 tests).

- [ ] **Step 2: Replace the body content**

Replace the entire `<body>...</body>` in `resources/views/welcome.blade.php` with:

```blade
<body class="font-sans text-white antialiased">
    <div class="min-h-screen relative overflow-hidden"
         style="background-image: url('{{ asset('images/bgbengkel.png') }}'); background-size: cover; background-position: center;">

        <div class="absolute inset-0 bg-black/60"></div>

        <a href="{{ route('login') }}"
           class="absolute top-6 left-6 z-10 bg-primary hover:bg-orange-600 text-white font-bold px-8 py-3 rounded-lg shadow-lg transition">
            LOGIN
        </a>

        <div class="relative z-10 min-h-screen flex flex-col md:flex-row items-center md:justify-between px-8 md:px-16 lg:px-24 py-24 md:py-0">
            <div class="max-w-2xl text-center md:text-left">
                <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight"
                    style="text-shadow: 2px 2px 8px rgba(0,0,0,0.8);">
                    Selamat Datang di Sistem Inventaris Bengkel
                </h1>

                <p class="text-xl text-white/90" style="text-shadow: 1px 1px 6px rgba(0,0,0,0.8);">
                    Sistem pengelolaan inventaris alat dan bahan bengkel yang memudahkan
                    pengawasan, pemeliharaan, peminjaman, dan pelaporan aset bengkel secara terpusat.
                </p>
            </div>

            <img src="{{ asset('images/unplogo.png') }}" alt="Logo UNP"
                 class="w-64 md:w-96 object-contain mt-12 md:mt-0 drop-shadow-2xl">
        </div>
    </div>
</body>
```

- [ ] **Step 3: Run existing test to verify the redesign passes**

Run: `php artisan test --filter=WelcomePageTest`
Expected: PASS (2 tests).

- [ ] **Step 4: Run full suite to verify nothing regressed**

Run: `php artisan test`
Expected: PASS (177 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/views/welcome.blade.php
git commit -m "style: redesign landing page layout"
```

---

## Manual Verification

1. Visit `/` logged out.
2. Expected: dark overlay on bengkel photo; **LOGIN** button orange `#FF6B35` top-left; large bold white title left-center with drop shadow; description beneath it; enlarged UNP logo right-center; on mobile, text above logo and button still pinned top-left.