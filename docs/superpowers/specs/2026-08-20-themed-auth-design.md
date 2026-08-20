# Themed Auth Pages Design

Date: 2026-08-20

## Purpose

Replace the plain white Laravel default auth pages with a themed design matching the landing page: bengkel background photo, dark overlay, glassmorphism card, orange accents, and UNP logo. All auth pages (login, register, forgot-password, reset-password, confirm-password, verify-email) share the guest layout, so theming the layout themes everything.

## Current State

- `resources/views/layouts/guest.blade.php` — gray background, centered white card (`bg-white shadow-md`), default Laravel look.
- Components already carry orange accents: `text-input` focus ring is `#B45F06`, `primary-button` is `bg-gray-800` (default gray).

## Design

### 1. Guest layout (`resources/views/layouts/guest.blade.php`)

- Fullscreen background `bgbengkel.png` (`public/images/bgbengkel.png`) with `background-size: cover`.
- Dark overlay `bg-black/60` for legibility.
- Content vertically centered.
- **Glassmorphism card**: `bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl shadow-2xl px-8 py-8 w-full sm:max-w-md`.
- UNP logo (`unplogo.png`) shown above the card, centered.
- Login/back-to-login link styling adjusted for visibility on the dark background.

### 2. Primary button component (`resources/views/components/primary-button.blade.php`)

- Change `bg-gray-800 hover:bg-gray-700 active:bg-gray-900` → orange theme `bg-primary hover:bg-orange-600 active:bg-orange-700` (`primary` = `#FF6B35`, matching landing button). Keep white text, rounded-md, focus ring `#B45F06`.

### 3. Text input & label legibility

- `text-input`: keep current style but ensure inputs remain readable on glass card — white background with dark text is already legible. No forced change beyond existing orange focus ring.
- `input-label`: white/light text on the dark glass card. Change `text-gray-700` → `text-gray-100` for legibility.

### 4. Auth views

- No content changes to any auth form. Styling comes from the layout, button, label components.
- Keep "Ingat Saya", "Lupa Kata Sandi", and all existing links/flow.

## Files Changed

- `resources/views/layouts/guest.blade.php`
- `resources/views/components/primary-button.blade.php`
- `resources/views/components/input-label.blade.php`

## Out of Scope

- No auth logic/controller/route changes.
- No content changes to forms.
- Landing page (`welcome.blade.php`) unchanged.

## Testing

Existing auth feature tests (`tests/Feature/Auth/*`) must keep passing. Manual: visit `/login` (and other auth pages) → themed background, glass card, orange button, readable labels; submit works normally; redirect to dashboard after login.