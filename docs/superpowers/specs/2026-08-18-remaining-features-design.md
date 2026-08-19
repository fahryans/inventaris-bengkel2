# Design Spec: Remaining Features — SIMA Bengkel

## Overview

Complete 6 remaining features to bring the project from ~85% to 100%. All features follow existing codebase patterns and conventions.

---

## Feature 1: Export PDF (Full Letterhead)

### Scope
- Generate PDF for all 7 report types + 2 personal reports
- Full letterhead layout: kop surat, nama institusi, judul laporan, tanggal cetak, tabel data, area tanda tangan

### Implementation

**Package:** `barryvdh/laravel-dompdf` (already installed)

**Files to create/modify:**
- `app/Http/Controllers/LaporanController.php` — implement `export()` method
- `resources/views/laporan/pdf/template.blade.php` — base PDF layout (letterhead)
- `resources/views/laporan/pdf/alat.blade.php` — alat report PDF
- `resources/views/laporan/pdf/bahan.blade.php` — bahan report PDF
- `resources/views/laporan/pdf/peminjaman.blade.php` — peminjaman report PDF
- `resources/views/laporan/pdf/pemeliharaan.blade.php` — pemeliharaan report PDF
- `resources/views/laporan/pdf/pengadaan_alat.blade.php` — pengadaan alat report PDF
- `resources/views/laporan/pdf/pengadaan_bahan.blade.php` — pengadaan bahan report PDF
- `resources/views/laporan/pdf/pemakaian_bahan.blade.php` — pemakaian bahan report PDF

**PDF Layout:**
```
┌─────────────────────────────────────┐
│  [Logo]  NAMA INSTITUSI             │
│  Alamat institusi                   │
│  Telp/HP                           │
├─────────────────────────────────────┤
│  JUDUL LAPORAN                      │
│  Periode: [tanggal]                 │
├─────────────────────────────────────┤
│  [Tabel data laporan]               │
│  | Kolom 1 | Kolom 2 | Kolom 3 |   │
├─────────────────────────────────────┤
│  Tanda tangan,                      │
│  [Nama]                             │
│  [Jabatan]                          │
│                                     │
│  Dicetak pada: [tanggal cetak]      │
└─────────────────────────────────────┘
```

**Controller logic:**
```php
public function export($tipe, Request $request)
{
    // Authorization check based on $tipe
    // Fetch data same as show() method
    // Generate PDF with DomPDF
    // Return download response
}
```

---

## Feature 2: Full Test Coverage

### Scope
- Controller tests: all 13 controllers (CRUD + authorization + validation)
- Service tests: StokService, PeminjamanService, FIFOService
- Model tests: all 11 models (relationships, casts, scopes)
- Target: ~100+ test cases
- Database: SQLite in-memory for speed

### Implementation

**Files to create:**
- `tests/Feature/Controllers/AlatControllerTest.php`
- `tests/Feature/Controllers/BahanControllerTest.php`
- `tests/Feature/Controllers/KategoriControllerTest.php`
- `tests/Feature/Controllers/LaboratoriumControllerTest.php`
- `tests/Feature/Controllers/UnitAlatControllerTest.php`
- `tests/Feature/Controllers/PeminjamanAlatControllerTest.php`
- `tests/Feature/Controllers/PengadaanAlatControllerTest.php`
- `tests/Feature/Controllers/PengadaanBahanControllerTest.php`
- `tests/Feature/Controllers/PemakaianBahanControllerTest.php`
- `tests/Feature/Controllers/PemeliharaanAlatControllerTest.php`
- `tests/Feature/Controllers/UserControllerTest.php`
- `tests/Feature/Controllers/DashboardControllerTest.php`
- `tests/Feature/Controllers/LaporanControllerTest.php`
- `tests/Unit/Services/StokServiceTest.php`
- `tests/Unit/Services/PeminjamanServiceTest.php`
- `tests/Unit/Services/FIFOServiceTest.php`
- `tests/Unit/Models/AlatTest.php`
- `tests/Unit/Models/BahanTest.php`
- `tests/Unit/Models/UnitAlatTest.php`
- `tests/Unit/Models/PeminjamanAlatTest.php`
- `tests/Unit/Models/PengadaanBahanTest.php`
- `tests/Unit/Models/PemakaianBahanTest.php`
- `tests/Unit/Models/UserTest.php`

**Test structure per controller:**
- `test_index_requires_auth()` — unauthenticated redirected
- `test_index_returns_200()` — authenticated can view
- `test_create_requires_auth()`
- `test_store_validates_input()` — invalid data rejected
- `test_store_creates_record()` — valid data creates
- `test_show_displays_record()`
- `test_edit_requires_auth()`
- `test_update_validates_input()`
- `test_update_modifies_record()`
- `test_destroy_deletes_record()`
- `test_role_authorization()` — wrong role gets 403

**Config changes:**
- `phpunit.xml` — add SQLite in-memory database config
- `tests/TestCase.php` — add `RefreshDatabase` trait

---

## Feature 3: Full REST API

### Scope
- CRUD endpoints for all 11 resources
- Authentication via Laravel Sanctum
- Role-based middleware for API
- API Resources for all models

### Implementation

**Package to install:** `laravel/sanctum`

**Files to create:**
- `routes/api.php` — all API routes
- `app/Http/Controllers/Api/AuthController.php` — login/logout/register
- `app/Http/Controllers/Api/AlatController.php`
- `app/Http/Controllers/Api/BahanController.php`
- `app/Http/Controllers/Api/KategoriController.php`
- `app/Http/Controllers/Api/LaboratoriumController.php`
- `app/Http/Controllers/Api/UnitAlatController.php`
- `app/Http/Controllers/Api/PeminjamanAlatController.php`
- `app/Http/Controllers/Api/PengadaanAlatController.php`
- `app/Http/Controllers/Api/PengadaanBahanController.php`
- `app/Http/Controllers/Api/PemakaianBahanController.php`
- `app/Http/Controllers/Api/PemeliharaanAlatController.php`
- `app/Http/Controllers/Api/UserController.php`
- `app/Http/Controllers/Api/LaporanController.php`
- `app/Http/Resources/AlatResource.php`
- `app/Http/Resources/BahanResource.php`
- `app/Http/Resources/KategoriResource.php`
- `app/Http/Resources/LaboratoriumResource.php`
- `app/Http/Resources/UnitAlatResource.php`
- `app/Http/Resources/PeminjamanAlatResource.php`
- `app/Http/Resources/PengadaanAlatResource.php`
- `app/Http/Resources/PengadaanBahanResource.php`
- `app/Http/Resources/PemakaianBahanResource.php`
- `app/Http/Resources/PemeliharaanAlatResource.php`
- `app/Http/Resources/UserResource.php`
- `app/Http/Middleware/ApiRoleMiddleware.php`

**API Endpoints:**
```
POST   /api/login              — Login, get token
POST   /api/register           — Register (admin only)
POST   /api/logout             — Revoke token
GET    /api/user                — Current user info

GET    /api/alat                — List alat (with filter/search)
POST   /api/alat                — Create alat
GET    /api/alat/{id}           — Show alat
PUT    /api/alat/{id}           — Update alat
DELETE /api/alat/{id}           — Delete alat

[Same pattern for: bahan, kategori, laboratorium, unit-alat,
 peminjaman, pengadaan-alat, pengadaan-bahan, pemakaian-bahan,
 pemeliharaan, users]

GET    /api/laporan/{tipe}     — Get report data
GET    /api/dashboard           — Dashboard stats
```

**Config:**
- `config/sanctum.php` — publish config
- `bootstrap/app.php` — register API route
- `.env` — add `SANCTUM_STATEFUL_DOMAINS`

---

## Feature 4: Full Activity Log

### Scope
- Log all CRUD operations across all controllers
- Track: user, action, subject, old/new values, IP address, timestamp
- Admin view for activity log

### Implementation

**Package to install:** `spatie/laravel-activitylog`

**Files to create/modify:**
- `database/migrations/xxxx_create_activity_log_table.php` — migration (package provides default)
- All 13 controllers — add `activity()` calls in store/update/destroy methods
- `app/Http/Controllers/ActivityLogController.php` — index view for admin
- `resources/views/activity-log/index.blade.php` — activity log view
- `routes/web.php` — add activity log route
- `resources/views/partials/sidebar.blade.php` — add activity log menu item

**Activity log structure:**
```php
activity()
    ->performedOn($alat)
    ->withProperties(['attributes' => $alat->getChanges()])
    ->causedBy($user)
    ->event('created') // or 'updated', 'deleted'
    ->log('Alat created');
```

**View columns:**
| Kolom | Deskripsi |
|-------|-----------|
| Waktu | Timestamp kejadian |
| User | Siapa yang melakukan |
| Aksi | create / update / delete |
| Model | Alat / Bahan / Peminjaman / dll |
| Detail | Data yang berubah |
| IP Address | Alamat IP |

---

## Feature 5: Dashboard Charts (All Roles)

### Scope
- Add Chart.js charts to all 6 dashboard views
- Charts: peminjaman per bulan, distribusi alat, stok bahan, pemeliharaan

### Implementation

**Package:** Chart.js via CDN (already used in kadep dashboard)

**Files to modify:**
- `resources/views/dashboard/admin.blade.php` — add: alat per lab (bar), pengadaan per bulan (line)
- `resources/views/dashboard/kepala-labor.blade.php` — add: peminjaman lab (line), stok bahan (doughnut)
- `resources/views/dashboard/teknisi.blade.php` — add: pemeliharaan per bulan (bar), status unit (pie)
- `resources/views/dashboard/user.blade.php` — add: riwayat peminjaman (line)
- `resources/views/dashboard/index.blade.php` — remove placeholder zeros, redirect to role dashboard
- `app/Http/Controllers/DashboardController.php` — pass chart data to views

**Charts per role:**

| Role | Chart Type | Data |
|------|-----------|------|
| Admin | Bar + Line | Alat per lab, Pengadaan per bulan |
| Kepala Lab | Line + Doughnut | Peminjaman lab per bulan, Stok bahan |
| Teknisi | Bar + Pie | Pemeliharaan per bulan, Status unit |
| Kadep | Line (existing) | Peminjaman per bulan |
| User | Line | Riwayat peminjaman pribadi |

---

## Feature 6: QR Code per Unit Alat

### Scope
- Generate QR code for each unit alat
- QR contains URL to unit alat detail page
- Print button on unit alat pages

### Implementation

**Package to install:** `simplesoftwareio/simple-qrcode`

**Files to create/modify:**
- `app/Http/Controllers/UnitAlatController.php` — add `qr()` method
- `resources/views/unit_alat/qr.blade.php` — QR code print view
- `resources/views/unit_alat/show.blade.php` — add "Cetak QR" button
- `resources/views/unit_alat/index.blade.php` — add QR column/button
- `routes/web.php` — add QR route

**QR content:**
```
https://domain.com/unit-alat/{id}
```

**Print layout:**
```
┌──────────────┐
│   [QR Code]  │
│  OS-001      │
│  Oscilloscope│
│  Lab: Elektronika │
└──────────────┘
```

---

## Package Installation Summary

```bash
composer require spatie/laravel-activitylog
composer require laravel/sanctum
composer require simplesoftwareio/simple-qrcode
```

## Execution Order

1. **Feature 1: Export PDF** — no dependencies, quick win
2. **Feature 4: Activity Log** — install spatie/activitylog, add to controllers
3. **Feature 5: Dashboard Charts** — pure frontend, no backend changes
4. **Feature 6: QR Code** — install package, add to unit alat
5. **Feature 3: REST API** — install Sanctum, create controllers + routes
6. **Feature 2: Full Tests** — write after all features are implemented (tests should test final state)
