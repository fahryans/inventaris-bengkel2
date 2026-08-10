# Graph Report - .  (2026-08-09)

## Corpus Check
- Corpus is ~11,812 words - fits in a single context window. You may not need a graph.

## Summary
- 416 nodes · 572 edges · 83 communities (81 shown, 2 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 25 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10
- Community 11
- Community 12

## God Nodes (most connected - your core abstractions)
1. `User` - 33 edges
2. `Controller` - 22 edges
3. `TestCase` - 18 edges
4. `Alat` - 10 edges
5. `LoginRequest` - 9 edges
6. `Bahan` - 9 edges
7. `require-dev` - 9 edges
8. `scripts` - 9 edges
9. `PemakaianBahan` - 8 edges
10. `PeminjamanAlat` - 8 edges

## Surprising Connections (you probably didn't know these)
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/DashboardController.php → app/Http/Controllers/Controller.php
- `Pinjam_alat` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Pinjam_alat.php → app/Http/Controllers/Controller.php
- `AuthenticatedSessionController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Auth/AuthenticatedSessionController.php → app/Http/Controllers/Controller.php
- `ConfirmablePasswordController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Auth/ConfirmablePasswordController.php → app/Http/Controllers/Controller.php
- `EmailVerificationNotificationController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Auth/EmailVerificationNotificationController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (83 total, 2 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.06
Nodes (14): DashboardController, Alat, Bahan, Kategori, Laboratorium, PemakaianBahan, PemeliharaanAlat, PeminjamanAlat (+6 more)

### Community 1 - "Community 1"
Cohesion: 0.06
Nodes (15): User, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Notifications\Notifiable, AuthenticationTest, EmailVerificationTest (+7 more)

### Community 2 - "Community 2"
Cohesion: 0.09
Nodes (19): AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController, RegisteredUserController (+11 more)

### Community 3 - "Community 3"
Cohesion: 0.05
Nodes (43): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+35 more)

### Community 4 - "Community 4"
Cohesion: 0.08
Nodes (25): alpinejs, autoprefixer, concurrently, laravel-vite-plugin, devDependencies, alpinejs, autoprefixer, concurrently (+17 more)

### Community 5 - "Community 5"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 6 - "Community 6"
Cohesion: 0.27
Nodes (3): LoginRequest, ProfileUpdateRequest, Illuminate\Foundation\Http\FormRequest

### Community 7 - "Community 7"
Cohesion: 0.38
Nodes (3): DatabaseSeeder, UserSeeder, Illuminate\Database\Seeder

### Community 9 - "Community 9"
Cohesion: 0.47
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 10 - "Community 10"
Cohesion: 0.40
Nodes (4): partials.footer, partials.navbar, partials.scripts, partials.sidebar

### Community 12 - "Community 12"
Cohesion: 0.50
Nodes (3): profile.partials.delete-user-form, profile.partials.update-password-form, profile.partials.update-profile-information-form

## Knowledge Gaps
- **69 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+64 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **2 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Community 1` to `Community 0`, `Community 2`, `Community 7`?**
  _High betweenness centrality (0.062) - this node is a cross-community bridge._
- **Why does `Controller` connect `Community 2` to `Community 0`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Are the 21 inferred relationships involving `User` (e.g. with `.store()` and `.run()`) actually correct?**
  _`User` has 21 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _69 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.06400409626216078 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.06384180790960452 - nodes in this community are weakly interconnected._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.08754208754208755 - nodes in this community are weakly interconnected._