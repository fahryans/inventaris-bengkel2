# Landing Page Design

Date: 2026-08-20

## Purpose

Add a landing page as the first screen for unauthenticated visitors. Currently the root route `/` redirects straight to the dashboard, which bounces guests to the login page. The landing page gives a welcome screen with the bengkel background image and UNP logo before entering the login flow.

## Current Behavior

- `routes/web.php:21` — `GET /` redirects to `route('dashboard')`.
- Dashboard is behind `auth` middleware, so guests land on the login page.

## Design

### 1. Assets

Move `bgbengkel.png` and `unplogo.png` from the project root into `public/images/` so the browser can serve them.

### 2. Route

Change `GET /` in `routes/web.php` to render a `welcome` view. Authenticated users should keep hitting the dashboard, so the landing page is only for guests:

- If authenticated → redirect to `route('dashboard')`.
- If guest → render the landing page.

### 3. View

New file `resources/views/welcome.blade.php`:

- Fullscreen background image `bgbengkel.png` with `background-size: cover`.
- Dark overlay to keep text readable.
- UNP logo (`unplogo.png`) centered at the top.
- Title: "Selamat Datang di Sistem Inventaris Bengkel".
- Short description of the bengkel inventory management system.
- "Masuk" button linking to `route('login')`.

Styling uses Tailwind, consistent with the existing views. The view is standalone (does not reuse `layouts/guest`, which is the white login card layout).

## Out of Scope

- No changes to the login page itself.
- No changes to dashboard routing or role-based dashboards.
- No registration/landing content beyond the welcome text and description.

## Testing

Manual: hit `/` as guest → see landing page; click "Masuk" → login page; log in → dashboard. Hit `/` while authenticated → dashboard.
