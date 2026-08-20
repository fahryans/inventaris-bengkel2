# Landing Page UI Redesign Design

Date: 2026-08-20

## Purpose

Rework the landing page layout from a centered stack to a two-column composition so the welcome text is prominent and legible against the bengkel background photo.

## Current Layout

`resources/views/welcome.blade.php` centers logo, title, description, and button vertically on a fullscreen background with a `bg-black/50` overlay. User feedback: text too small and blends into the background.

## New Design

Single file change: `resources/views/welcome.blade.php`. Routes, text content, and tests unchanged.

### Layout

- Fullscreen background `bgbengkel.png` with `bg-black/60` overlay so text does not blend into the photo.
- **Left column** (vertically centered): title **"Selamat Datang di Sistem Inventaris Bengkel"** at `text-5xl font-bold` with `text-shadow`, description paragraph at `text-xl`, left-aligned.
- **Right column** (vertically centered): UNP logo (`unplogo.png`) enlarged to `w-64 md:w-96`, `object-contain`.
- **Top-left corner**: orange **LOGIN** button — `bg-primary` (`#FF6B35`, the project's orange-engineering color from `tailwind.config.js`) with white bold text, rounded corners, hover to darker orange.
- **Responsive**: on small screens stack vertically — text block on top, logo below; LOGIN button stays pinned top-left.

### Legibility

- Overlay raised from `bg-black/50` to `bg-black/60`.
- Title uses `text-5xl`/`text-6xl` (responsive) and `font-bold`.
- Description uses `text-xl text-white/90`.
- Text shadow applied to title and description.

## Out of Scope

- No route, controller, or test changes.
- No content text changes.
- No other views touched.

## Testing

Existing `tests/Feature/WelcomePageTest.php` covers: guest sees title text, background image path, and login link. Redesign must keep those assertions passing. Manual: check legibility on the background photo, logo position, and button placement/hover.