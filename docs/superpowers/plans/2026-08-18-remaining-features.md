# Remaining Features Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete 6 remaining features (Export PDF, Tests, REST API, Activity Log, Dashboard Charts, QR Code) to bring SIMA Bengkel to 100% completion.

**Architecture:** Each feature is independent and can be implemented in sequence. Features follow existing Laravel patterns. New packages: spatie/laravel-activitylog, laravel/sanctum, simplesoftwareio/simple-qrcode.

**Tech Stack:** Laravel 13.8, PHP 8.3, MySQL, DomPDF, Chart.js (CDN), Alpine.js, Bootstrap 5.3

## Global Constraints

- PHP 8.3+, Laravel 13.8+
- Follow existing code style (no comments unless asked)
- Use existing patterns: controllers use `$this->authorize()`, Form Requests for validation, Eloquent relationships
- Tests use SQLite in-memory database
- All new routes must be added to `routes/web.php` or `routes/api.php`
- All new views follow existing Blade layout structure (`layouts.admin`)
- Sidebar updates go in `resources/views/partials/sidebar.blade.php`

---

## Task 1: Install Required Packages

**Files:**
- Modify: `composer.json`
- Modify: `package.json` (no changes needed)

**Interfaces:**
- Produces: 3 new Composer packages available for use

- [ ] **Step 1: Install spatie/laravel-activitylog**

```bash
composer require spatie/laravel-activitylog
```

- [ ] **Step 2: Publish activitylog config and migration**

```bash
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
```

- [ ] **Step 3: Run activitylog migration**

```bash
php artisan migrate
```

- [ ] **Step 4: Install laravel/sanctum**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

- [ ] **Step 5: Install simplesoftwareio/simple-qrcode**

```bash
composer require simplesoftwareio/simple-qrcode
```

- [ ] **Step 6: Verify all packages installed**

```bash
composer show | grep -E "spatie|sanctum|qrcode"
```

- [ ] **Step 7: Commit**

```bash
git add composer.json composer.lock
git commit -m "feat: install activitylog, sanctum, qrcode packages"
```

---

## Task 2: Export PDF — Base Template

**Files:**
- Create: `resources/views/laporan/pdf/template.blade.php`

**Interfaces:**
- Consumes: $title, $content (yield), $tipe, $date
- Produces: Reusable PDF layout with letterhead

- [ ] **Step 1: Create base PDF template with letterhead**

Create `resources/views/laporan/pdf/template.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12px; margin: 40px; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 5px 0; text-transform: uppercase; }
        .header h2 { font-size: 14px; margin: 5px 0; font-weight: normal; }
        .header p { font-size: 11px; margin: 2px 0; }
        .title { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; margin: 20px 0; }
        .period { text-align: center; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .signature { margin-top: 40px; text-align: right; }
        .signature p { margin: 2px 0; }
        .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'SIMA Bengkel') }}</h1>
        <h2>Sistem Inventaris Alat Bengkel</h2>
        <p>Jl. Teknologi No. 123, Gedung A, Lantai 2</p>
        <p>Telp: (021) 1234-5678 | Email: info@sima-bengkel.test</p>
    </div>

    <div class="title">{{ $title }}</div>
    <div class="period">Periode: {{ $date }}</div>

    @yield('content')

    <div class="signature">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        <br><br><br>
        <p>_________________________</p>
        <p><strong>Kepala Laboratorium</strong></p>
    </div>

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh Sistem Inventaris Alat Bengkel</p>
    </div>
</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/laporan/pdf/template.blade.php
git commit -m "feat: add base PDF template with letterhead"
```

---

## Task 3: Export PDF — Report Templates

**Files:**
- Create: `resources/views/laporan/pdf/alat.blade.php`
- Create: `resources/views/laporan/pdf/bahan.blade.php`
- Create: `resources/views/laporan/pdf/peminjaman.blade.php`
- Create: `resources/views/laporan/pdf/pemeliharaan.blade.php`
- Create: `resources/views/laporan/pdf/pengadaan_alat.blade.php`
- Create: `resources/views/laporan/pdf/pengadaan_bahan.blade.php`
- Create: `resources/views/laporan/pdf/pemakaian_bahan.blade.php`

**Interfaces:**
- Consumes: $data (collection of records), $tipe
- Produces: PDF content for each report type

- [ ] **Step 1: Create alat PDF template**

Create `resources/views/laporan/pdf/alat.blade.php`:

```blade
@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Alat</th>
            <th>Merek</th>
            <th>Kategori</th>
            <th>Laboratorium</th>
            <th>Tipe Pelacakan</th>
            <th>Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama_alat }}</td>
            <td>{{ $item->merek ?? '-' }}</td>
            <td>{{ $item->kategori->nama_kategori }}</td>
            <td>{{ $item->laboratorium->nama_labor }}</td>
            <td>{{ ucfirst($item->tipe_pelacakan) }}</td>
            <td>{{ $item->jumlah_alat }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

- [ ] **Step 2: Create bahan PDF template**

Create `resources/views/laporan/pdf/bahan.blade.php`:

```blade
@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Bahan</th>
            <th>Kategori</th>
            <th>Stok Saat Ini</th>
            <th>Stok Minimum</th>
            <th>Satuan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->nama_bahan }}</td>
            <td>{{ $item->kategori->nama_kategori }}</td>
            <td>{{ $item->stok_saat_ini }}</td>
            <td>{{ $item->stok_minimum }}</td>
            <td>{{ $item->satuan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

- [ ] **Step 3: Create peminjaman PDF template**

Create `resources/views/laporan/pdf/peminjaman.blade.php`:

```blade
@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Alat</th>
            <th>Unit</th>
            <th>Peminjam</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Kembali</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->alat->nama_alat ?? '-' }}</td>
            <td>{{ $item->unitAlat->kode_inventaris ?? '-' }}</td>
            <td>{{ $item->userPeminjam->nama }}</td>
            <td>{{ $item->waktu_peminjaman->format('d/m/Y H:i') }}</td>
            <td>{{ $item->waktu_pengembalian->format('d/m/Y H:i') ?? '-' }}</td>
            <td>{{ $item->status === 'terpinjam' ? 'Dipinjam' : 'Sudah Dikembalikan' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

- [ ] **Step 4: Create pemeliharaan PDF template**

Create `resources/views/laporan/pdf/pemeliharaan.blade.php`:

```blade
@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Unit Alat</th>
            <th>Teknisi</th>
            <th>Tanggal Cek</th>
            <th>Kondisi</th>
            <th>Biaya</th>
            <th>Hasil</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->unitAlat->kode_inventaris }}</td>
            <td>{{ $item->teknisi->nama }}</td>
            <td>{{ $item->tanggal_cek->format('d/m/Y') }}</td>
            <td>{{ $item->kondisi ?? '-' }}</td>
            <td>Rp {{ number_format($item->biaya ?? 0, 0, ',', '.') }}</td>
            <td>{{ $item->hasil_pemeliharaan ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

- [ ] **Step 5: Create pengadaan_alat PDF template**

Create `resources/views/laporan/pdf/pengadaan_alat.blade.php`:

```blade
@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Alat</th>
            <th>Jumlah</th>
            <th>Harga Perolehan</th>
            <th>Tanggal Pengadaan</th>
            <th>Supplier</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->alat->nama_alat }}</td>
            <td>{{ $item->jumlah }}</td>
            <td>Rp {{ number_format($item->harga_perolehan, 0, ',', '.') }}</td>
            <td>{{ $item->tanggal_pengadaan->format('d/m/Y') }}</td>
            <td>{{ $item->supplier ?? '-' }}</td>
            <td>{{ $item->tanggal_masuk ? 'Sudah Diterima' : 'Menunggu' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

- [ ] **Step 6: Create pengadaan_bahan PDF template**

Create `resources/views/laporan/pdf/pengadaan_bahan.blade.php`:

```blade
@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Bahan</th>
            <th>Jumlah</th>
            <th>Stok Tersisa</th>
            <th>Harga Perolehan</th>
            <th>Tanggal Pengadaan</th>
            <th>Supplier</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->bahan->nama_bahan }}</td>
            <td>{{ $item->jumlah }}</td>
            <td>{{ $item->stok_tersisa_batch }}</td>
            <td>Rp {{ number_format($item->harga_perolehan, 0, ',', '.') }}</td>
            <td>{{ $item->tanggal_pengadaan->format('d/m/Y') }}</td>
            <td>{{ $item->supplier ?? '-' }}</td>
            <td>{{ $item->tanggal_masuk ? 'Sudah Diterima' : 'Menunggu' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

- [ ] **Step 7: Create pemakaian_bahan PDF template**

Create `resources/views/laporan/pdf/pemakaian_bahan.blade.php`:

```blade
@extends('laporan.pdf.template')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Bahan</th>
            <th>Jumlah Diambil</th>
            <th>Jumlah Terpakai</th>
            <th>Pemakai</th>
            <th>Verifikator</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->bahan->nama_bahan }}</td>
            <td>{{ $item->jumlah_pengambilan }}</td>
            <td>{{ $item->jumlah_terpakai ?? '-' }}</td>
            <td>{{ $item->userPemakai->nama }}</td>
            <td>{{ $item->userVerifikasi->nama ?? '-' }}</td>
            <td>{{ $item->waktu_pemakaian->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
```

- [ ] **Step 8: Commit**

```bash
git add resources/views/laporan/pdf/
git commit -m "feat: add PDF templates for all 7 report types"
```

---

## Task 4: Export PDF — Controller Implementation

**Files:**
- Modify: `app/Http/Controllers/LaporanController.php`

**Interfaces:**
- Consumes: PDF templates (Task 2-3), DomPDF package
- Produces: Functional export() method

- [ ] **Step 1: Read current LaporanController**

Read `app/Http/Controllers/LaporanController.php` to understand existing structure.

- [ ] **Step 2: Add DomPDF import and implement export()**

Modify `app/Http/Controllers/LaporanController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\PeminjamanAlat;
use App\Models\PemakaianBahan;
use App\Models\PemeliharaanAlat;
use App\Models\PengadaanAlat;
use App\Models\PengadaanBahan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    // ... existing index(), show(), myReport() methods stay the same ...

    public function export($tipe, Request $request)
    {
        $this->authorize('viewAny', PeminjamanAlat::class);

        $data = match($tipe) {
            'alat' => Alat::with(['kategori', 'laboratorium'])->latest()->get(),
            'bahan' => Bahan::with('kategori')->latest()->get(),
            'peminjaman' => PeminjamanAlat::with(['alat', 'unitAlat', 'userPeminjam'])->latest()->get(),
            'pemeliharaan' => PemeliharaanAlat::with(['unitAlat', 'teknisi'])->latest()->get(),
            'pengadaan_alat' => PengadaanAlat::with(['alat', 'userInput'])->latest()->get(),
            'pengadaan_bahan' => PengadaanBahan::with(['bahan', 'userInput'])->latest()->get(),
            'pemakaian_bahan' => PemakaianBahan::with(['bahan', 'userPemakai', 'userVerifikasi'])->latest()->get(),
            default => null,
        };

        if (!$data) {
            return redirect()->route('laporan.show', $tipe)
                ->with('error', 'Tipe laporan tidak valid');
        }

        $title = match($tipe) {
            'alat' => 'Laporan Data Alat',
            'bahan' => 'Laporan Data Bahan',
            'peminjaman' => 'Laporan Peminjaman Alat',
            'pemeliharaan' => 'Laporan Pemeliharaan Alat',
            'pengadaan_alat' => 'Laporan Pengadaan Alat',
            'pengadaan_bahan' => 'Laporan Pengadaan Bahan',
            'pemakaian_bahan' => 'Laporan Pemakaian Bahan',
        };

        $date = now()->format('d/m/Y');
        $pdf = Pdf::loadView("laporan.pdf.{$tipe}", compact('data', 'title', 'date'));

        return $pdf->download("laporan_{$tipe}_{$date}.pdf");
    }
}
```

- [ ] **Step 3: Test export in browser**

Visit `/laporan/alat` and click "Export PDF" button. Verify PDF downloads with correct letterhead.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/LaporanController.php
git commit -m "feat: implement PDF export for all report types"
```

---

## Task 5: Activity Log — Migration and Config

**Files:**
- Modify: `config/activitylog.php` (if published)

**Interfaces:**
- Consumes: spatie/laravel-activitylog package (Task 1)
- Produces: activity_log table in database

- [ ] **Step 1: Verify migration ran**

```bash
php artisan migrate:status
```

Check that `create_activity_log_table` migration has run.

- [ ] **Step 2: Configure activitylog to use soft deletes**

Read `config/activitylog.php` and verify configuration. The default config should work. If needed, publish and modify:

```bash
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
```

- [ ] **Step 3: Commit config if changed**

```bash
git add config/activitylog.php
git commit -m "feat: configure activitylog for soft deletes"
```

---

## Task 6: Activity Log — Add to Controllers

**Files:**
- Modify: `app/Http/Controllers/AlatController.php`
- Modify: `app/Http/Controllers/BahanController.php`
- Modify: `app/Http/Controllers/KategoriController.php`
- Modify: `app/Http/Controllers/LaboratoriumController.php`
- Modify: `app/Http/Controllers/UnitAlatController.php`
- Modify: `app/Http/Controllers/PeminjamanAlatController.php`
- Modify: `app/Http/Controllers/PengadaanAlatController.php`
- Modify: `app/Http/Controllers/PengadaanBahanController.php`
- Modify: `app/Http/Controllers/PemakaianBahanController.php`
- Modify: `app/Http/Controllers/PemeliharaanAlatController.php`
- Modify: `app/Http/Controllers/UserController.php`

**Interfaces:**
- Consumes: Spatie\Activitylog\Facades\Activity
- Produces: Activity log entries for all CRUD operations

- [ ] **Step 1: Add activity log to AlatController**

Read `app/Http/Controllers/AlatController.php`, then add `use Spatie\Activitylog\Facades\Activity;` import and add activity logging in store(), update(), destroy() methods.

Example for store():
```php
public function store(AlatRequest $request)
{
    $this->authorize('create', Alat::class);
    $alat = Alat::create($request->validated());

    activity()
        ->performedOn($alat)
        ->withProperties(['attributes' => $alat->toArray()])
        ->event('created')
        ->log('Alat baru ditambahkan');

    return redirect()->route('alat.index')
        ->with('success', 'Alat berhasil ditambahkan');
}
```

Example for update():
```php
public function update(AlatRequest $request, Alat $alat)
{
    $this->authorize('update', $alat);
    $oldData = $alat->toArray();
    $alat->update($request->validated());

    activity()
        ->performedOn($alat)
        ->withProperties(['old' => $oldData, 'attributes' => $alat->toArray()])
        ->event('updated')
        ->log('Alat diperbarui');

    return redirect()->route('alat.show', $alat)
        ->with('success', 'Alat berhasil diperbarui');
}
```

Example for destroy():
```php
public function destroy(Alat $alat)
{
    $this->authorize('delete', $alat);

    activity()
        ->performedOn($alat)
        ->withProperties(['attributes' => $alat->toArray()])
        ->event('deleted')
        ->log('Alat dihapus');

    $alat->delete();

    return redirect()->route('alat.index')
        ->with('success', 'Alat berhasil dihapus');
}
```

- [ ] **Step 2: Add activity log to BahanController**

Same pattern as AlatController. Read and modify `app/Http/Controllers/BahanController.php`.

- [ ] **Step 3: Add activity log to KategoriController**

Same pattern. Read and modify `app/Http/Controllers/KategoriController.php`.

- [ ] **Step 4: Add activity log to LaboratoriumController**

Same pattern. Read and modify `app/Http/Controllers/LaboratoriumController.php`.

- [ ] **Step 5: Add activity log to UnitAlatController**

Same pattern. Read and modify `app/Http/Controllers/UnitAlatController.php`.

- [ ] **Step 6: Add activity log to PeminjamanAlatController**

Same pattern, plus log status changes (borrow, return). Read and modify `app/Http/Controllers/PeminjamanAlatController.php`.

- [ ] **Step 7: Add activity log to PengadaanAlatController**

Same pattern, plus log mark-received action. Read and modify `app/Http/Controllers/PengadaanAlatController.php`.

- [ ] **Step 8: Add activity log to PengadaanBahanController**

Same pattern, plus log mark-received action. Read and modify `app/Http/Controllers/PengadaanBahanController.php`.

- [ ] **Step 9: Add activity log to PemakaianBahanController**

Same pattern, plus log verify action. Read and modify `app/Http/Controllers/PemakaianBahanController.php`.

- [ ] **Step 10: Add activity log to PemeliharaanAlatController**

Same pattern, plus log complete action. Read and modify `app/Http/Controllers/PemeliharaanAlatController.php`.

- [ ] **Step 11: Add activity log to UserController**

Same pattern. Read and modify `app/Http/Controllers/UserController.php`.

- [ ] **Step 12: Commit**

```bash
git add app/Http/Controllers/
git commit -m "feat: add activity logging to all controllers"
```

---

## Task 7: Activity Log — Admin View

**Files:**
- Create: `app/Http/Controllers/ActivityLogController.php`
- Create: `resources/views/activity-log/index.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/partials/sidebar.blade.php`

**Interfaces:**
- Consumes: Spatie\Activitylog\Models\Activity
- Produces: Admin view for activity log

- [ ] **Step 1: Create ActivityLogController**

Create `app/Http/Controllers/ActivityLogController.php`:

```php
<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Activity::class);

        $activities = Activity::with('causedBy')
            ->latest()
            ->paginate(20);

        return view('activity-log.index', compact('activities'));
    }
}
```

- [ ] **Step 2: Create activity log view**

Create `resources/views/activity-log/index.blade.php`:

```blade
@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-history me-2"></i>Aktivitas Sistem
    </h1>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Model</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $activity->causedBy->nama ?? 'System' }}</td>
                        <td>
                            @if($activity->event === 'created')
                                <span class="badge bg-success">Create</span>
                            @elseif($activity->event === 'updated')
                                <span class="badge bg-warning">Update</span>
                            @elseif($activity->event === 'deleted')
                                <span class="badge bg-danger">Delete</span>
                            @else
                                <span class="badge bg-info">{{ $activity->event }}</span>
                            @endif
                        </td>
                        <td>{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</td>
                        <td>
                            @if($activity->event === 'updated' && isset($activity->properties['old']))
                                <small class="text-muted">
                                    @foreach($activity->properties['attributes'] as $key => $value)
                                        @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $value)
                                            <strong>{{ $key }}:</strong> {{ $activity->properties['old'][$key] }} → {{ $value }}<br>
                                        @endif
                                    @endforeach
                                </small>
                            @else
                                <small class="text-muted">{{ json_encode($activity->properties['attributes'] ?? []) }}</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada aktivitas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $activities->links() }}
    </div>
</div>
@endsection
```

- [ ] **Step 3: Add route**

Modify `routes/web.php` — add inside the admin role group:

```php
Route::get('/activity-log', [\App\Http\Controllers\ActivityLogController::class, 'index'])
    ->name('activity-log.index')
    ->middleware('role:admin_jurusan');
```

- [ ] **Step 4: Add sidebar menu item**

Modify `resources/views/partials/sidebar.blade.php` — add after the "Laporan" menu item:

```html
<li class="nav-item">
    <a class="nav-link" href="{{ route('activity-log.index') }}">
        <i class="fas fa-history"></i>
        <span>Aktivitas Sistem</span>
    </a>
</li>
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/ActivityLogController.php resources/views/activity-log/ routes/web.php resources/views/partials/sidebar.blade.php
git commit -m "feat: add activity log admin view with sidebar menu"
```

---

## Task 8: Dashboard Charts — Admin Dashboard

**Files:**
- Modify: `resources/views/dashboard/admin.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`

**Interfaces:**
- Consumes: Chart.js CDN, Eloquent queries for chart data
- Produces: Bar chart (alat per lab), Line chart (pengadaan per bulan)

- [ ] **Step 1: Add Chart.js CDN to admin dashboard**

Read `resources/views/dashboard/admin.blade.php` and add Chart.js canvas elements and scripts.

Add before `@endsection`:

```html
<div class="row mb-4">
    <div class="col-xl-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-1"></i>Distribusi Alat per Laboratorium
                </h6>
            </div>
            <div class="card-body">
                <canvas id="alatPerLabChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line me-1"></i>Pengadaan per Bulan ({{ date('Y') }})
                </h6>
            </div>
            <div class="card-body">
                <canvas id="pengadaanChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Alat per Lab Bar Chart
new Chart(document.getElementById('alatPerLabChart'), {
    type: 'bar',
    data: {
        labels: @json($labNames ?? []),
        datasets: [{
            label: 'Jumlah Alat',
            data: @json($alatCounts ?? []),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});

// Pengadaan per Bulan Line Chart
new Chart(document.getElementById('pengadaanChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Pengadaan',
            data: @json($pengadaanPerBulan ?? []),
            borderColor: '#4e73df',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>
```

- [ ] **Step 2: Add chart data to DashboardController**

Read `app/Http/Controllers/DashboardController.php` and add chart data for admin view.

In the admin dashboard section, add:

```php
$labNames = \App\Models\Laboratorium::pluck('nama_labor');
$alatCounts = \App\Models\Laboratorium::withCount('alat')->pluck('alat_count');
$pengadaanPerBulan = \App\Models\PengadaanAlat::whereYear('created_at', now()->year)
    ->selectRaw('MONTH(created_at) as month, count(*) as total')
    ->groupBy('month')
    ->pluck('total', 'month')
    ->toArray();
// Fill missing months with 0
$pengadaanPerBulan = collect(range(1, 12))->map(fn($m) => $pengadaanPerBulan[$m] ?? 0)->toArray();
```

Pass `$labNames`, `$alatCounts`, `$pengadaanPerBulan` to the admin view.

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard/admin.blade.php app/Http/Controllers/DashboardController.php
git commit -m "feat: add charts to admin dashboard"
```

---

## Task 9: Dashboard Charts — Kepala Lab Dashboard

**Files:**
- Modify: `resources/views/dashboard/kepala-labor.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`

**Interfaces:**
- Consumes: Chart.js CDN, lab-specific data
- Produces: Line chart (peminjaman lab), Doughnut chart (stok bahan)

- [ ] **Step 1: Add charts to kepala lab dashboard**

Read `resources/views/dashboard/kepala-labor.blade.php` and add chart elements.

Add before `@endsection`:

```html
<div class="row mb-4">
    <div class="col-xl-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line me-1"></i>Peminjaman per Bulan
                </h6>
            </div>
            <div class="card-body">
                <canvas id="peminjamanLabChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-doughnut me-1"></i>Stok Bahan
                </h6>
            </div>
            <div class="card-body">
                <canvas id="stokBahanChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Peminjaman per Bulan
new Chart(document.getElementById('peminjamanLabChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Peminjaman',
            data: @json($peminjamanPerBulan ?? []),
            borderColor: '#1cc88a',
            tension: 0.3
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// Stok Bahan Doughnut
new Chart(document.getElementById('stokBahanChart'), {
    type: 'doughnut',
    data: {
        labels: @json($bahanNames ?? []),
        datasets: [{
            data: @json($stokBahan ?? []),
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
        }]
    },
    options: { responsive: true }
});
</script>
```

- [ ] **Step 2: Add chart data to DashboardController**

In the kepala_labor section, add:

```php
$peminjamanPerBulan = \App\Models\PeminjamanAlat::whereHas('alat', fn($q) => $q->where('id_labor', $user->kepalaLabor->id))
    ->whereYear('created_at', now()->year)
    ->selectRaw('MONTH(created_at) as month, count(*) as total')
    ->groupBy('month')
    ->pluck('total', 'month')
    ->toArray();
$peminjamanPerBulan = collect(range(1, 12))->map(fn($m) => $peminjamanPerBulan[$m] ?? 0)->toArray();

$bahanNames = \App\Models\Bahan::where('id_labor', $user->kepalaLabor->id)->pluck('nama_bahan');
$stokBahan = \App\Models\Bahan::where('id_labor', $user->kepalaLabor->id)->pluck('stok_saat_ini');
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard/kepala-labor.blade.php app/Http/Controllers/DashboardController.php
git commit -m "feat: add charts to kepala lab dashboard"
```

---

## Task 10: Dashboard Charts — Teknisi Dashboard

**Files:**
- Modify: `resources/views/dashboard/teknisi.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`

**Interfaces:**
- Consumes: Chart.js CDN, maintenance data
- Produces: Bar chart (pemeliharaan per bulan), Pie chart (status unit)

- [ ] **Step 1: Add charts to teknisi dashboard**

Read `resources/views/dashboard/teknisi.blade.php` and add chart elements.

Add before `@endsection`:

```html
<div class="row mb-4">
    <div class="col-xl-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-1"></i>Pemeliharaan per Bulan
                </h6>
            </div>
            <div class="card-body">
                <canvas id="pemeliharaanChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-pie me-1"></i>Status Unit Alat
                </h6>
            </div>
            <div class="card-body">
                <canvas id="statusUnitChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Pemeliharaan per Bulan
new Chart(document.getElementById('pemeliharaanChart'), {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Pemeliharaan',
            data: @json($pemeliharaanPerBulan ?? []),
            backgroundColor: '#36b9cc'
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// Status Unit Pie
new Chart(document.getElementById('statusUnitChart'), {
    type: 'pie',
    data: {
        labels: @json($statusLabels ?? []),
        datasets: [{
            data: @json($statusCounts ?? []),
            backgroundColor: ['#1cc88a', '#4e73df', '#e74a3b', '#f6c23e']
        }]
    },
    options: { responsive: true }
});
</script>
```

- [ ] **Step 2: Add chart data to DashboardController**

In the teknisi section, add:

```php
$pemeliharaanPerBulan = \App\Models\PemeliharaanAlat::whereYear('created_at', now()->year)
    ->selectRaw('MONTH(created_at) as month, count(*) as total')
    ->groupBy('month')
    ->pluck('total', 'month')
    ->toArray();
$pemeliharaanPerBulan = collect(range(1, 12))->map(fn($m) => $pemeliharaanPerBulan[$m] ?? 0)->toArray();

$statusLabels = ['Tersedia', 'Dipinjam', 'Rusak', 'Maintenance'];
$statusCounts = [
    \App\Models\UnitAlat::where('status', 'tersedia')->count(),
    \App\Models\UnitAlat::where('status', 'dipinjam')->count(),
    \App\Models\UnitAlat::where('status', 'rusak')->count(),
    \App\Models\UnitAlat::where('status', 'maintenance')->count(),
];
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard/teknisi.blade.php app/Http/Controllers/DashboardController.php
git commit -m "feat: add charts to teknisi dashboard"
```

---

## Task 11: Dashboard Charts — User Dashboard

**Files:**
- Modify: `resources/views/dashboard/user.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`

**Interfaces:**
- Consumes: Chart.js CDN, user's borrowing history
- Produces: Line chart (riwayat peminjaman pribadi)

- [ ] **Step 1: Add chart to user dashboard**

Read `resources/views/dashboard/user.blade.php` and add chart element.

Add before `@endsection`:

```html
<div class="row mb-4">
    <div class="col-xl-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-line me-1"></i>Riwayat Peminjaman Saya ({{ date('Y') }})
                </h6>
            </div>
            <div class="card-body">
                <canvas id="riwayatPeminjamanChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('riwayatPeminjamanChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [{
            label: 'Peminjaman',
            data: @json($riwayatPeminjaman ?? []),
            borderColor: '#4e73df',
            tension: 0.3,
            fill: true,
            backgroundColor: 'rgba(78, 115, 223, 0.1)'
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>
```

- [ ] **Step 2: Add chart data to DashboardController**

In the user (mahasiswa/dosen) section, add:

```php
$riwayatPeminjaman = \App\Models\PeminjamanAlat::where('id_user_peminjam', $user->id)
    ->whereYear('created_at', now()->year)
    ->selectRaw('MONTH(created_at) as month, count(*) as total')
    ->groupBy('month')
    ->pluck('total', 'month')
    ->toArray();
$riwayatPeminjaman = collect(range(1, 12))->map(fn($m) => $riwayatPeminjaman[$m] ?? 0)->toArray();
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard/user.blade.php app/Http/Controllers/DashboardController.php
git commit -m "feat: add chart to user dashboard"
```

---

## Task 12: Dashboard Charts — Fix Default Index

**Files:**
- Modify: `resources/views/dashboard/index.blade.php`
- Modify: `app/Http/Controllers/DashboardController.php`

**Interfaces:**
- Consumes: None
- Produces: Removed placeholder zeros, proper role redirect

- [ ] **Step 1: Update DashboardController to redirect**

Read `app/Http/Controllers/DashboardController.php` and ensure the index method redirects to role-specific dashboard. The current code already does this via `view("dashboard.{$role}")`. The `dashboard/index.blade.php` is a fallback that should never be reached.

- [ ] **Step 2: Clean up index.blade.php placeholder**

Read `resources/views/dashboard/index.blade.php` and replace the hardcoded zeros with a redirect or simple message:

```blade
@extends('layouts.admin')

@section('content')
<div class="text-center">
    <h4>Anda akan dialihkan ke dashboard yang sesuai...</h4>
    <script>window.location.href = "{{ route('dashboard') }}";</script>
</div>
@endsection
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard/index.blade.php
git commit -m "fix: clean up default dashboard placeholder"
```

---

## Task 13: QR Code — Controller and Routes

**Files:**
- Modify: `app/Http/Controllers/UnitAlatController.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: simplesoftwareio/qrcode package
- Produces: QR code generation endpoint

- [ ] **Step 1: Add QR code method to UnitAlatController**

Read `app/Http/Controllers/UnitAlatController.php` and add:

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

public function qr(UnitAlat $unitAlat)
{
    $this->authorize('view', $unitAlat);

    return view('unit_alat.qr', compact('unitAlat'));
}
```

- [ ] **Step 2: Add QR route**

Modify `routes/web.php` — add inside the unit-alat resource:

```php
Route::get('unit-alat/{unitAlat}/qr', [\App\Http\Controllers\UnitAlatController::class, 'qr'])
    ->name('unit-alat.qr');
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/UnitAlatController.php routes/web.php
git commit -m "feat: add QR code route and controller method"
```

---

## Task 14: QR Code — Print View

**Files:**
- Create: `resources/views/unit_alat/qr.blade.php`

**Interfaces:**
- Consumes: $unitAlat, QrCode facade
- Produces: Printable QR code page

- [ ] **Step 1: Create QR code print view**

Create `resources/views/unit_alat/qr.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code - {{ $unitAlat->kode_inventaris }}</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f5f5f5; }
        .qr-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; max-width: 300px; }
        .qr-code { margin: 20px 0; }
        .info { margin-top: 15px; }
        .info h2 { font-size: 18px; margin: 5px 0; color: #333; }
        .info p { font-size: 14px; color: #666; margin: 3px 0; }
        .print-btn { margin-top: 20px; padding: 10px 20px; background: #4e73df; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .print-btn:hover { background: #2e59d9; }
        @media print { .print-btn { display: none; } body { background: white; } }
    </style>
</head>
<body>
    <div class="qr-card">
        <div class="qr-code">
            {!! QrCode::size(200)->generate(route('unit-alat.show', $unitAlat)) !!}
        </div>
        <div class="info">
            <h2>{{ $unitAlat->kode_inventaris }}</h2>
            <p>{{ $unitAlat->alat->nama_alat }}</p>
            <p>Lab: {{ $unitAlat->alat->laboratorium->nama_labor }}</p>
            <p>Status: {{ ucfirst($unitAlat->status) }}</p>
        </div>
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak QR Code
        </button>
    </div>
</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/unit_alat/qr.blade.php
git commit -m "feat: add QR code print view for unit alat"
```

---

## Task 15: QR Code — Add Buttons to Views

**Files:**
- Modify: `resources/views/unit_alat/show.blade.php`
- Modify: `resources/views/unit_alat/index.blade.php`

**Interfaces:**
- Consumes: QR route (Task 13)
- Produces: "Cetak QR" buttons in unit alat views

- [ ] **Step 1: Add QR button to show view**

Read `resources/views/unit_alat/show.blade.php` and add a "Cetak QR" button next to the Edit button:

```html
<a href="{{ route('unit-alat.qr', $unitAlat) }}" class="btn btn-info" target="_blank">
    <i class="fas fa-qrcode me-1"></i>Cetak QR
</a>
```

- [ ] **Step 2: Add QR button to index view**

Read `resources/views/unit_alat/index.blade.php` and add a QR icon button in the actions column:

```html
<a href="{{ route('unit-alat.qr', $unitAlat) }}" class="btn btn-sm btn-info" target="_blank" title="Cetak QR">
    <i class="fas fa-qrcode"></i>
</a>
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/unit_alat/show.blade.php resources/views/unit_alat/index.blade.php
git commit -m "feat: add QR code buttons to unit alat views"
```

---

## Task 16: REST API — Sanctum Setup

**Files:**
- Modify: `bootstrap/app.php`
- Modify: `config/sanctum.php` (publish)

**Interfaces:**
- Consumes: laravel/sanctum package (Task 1)
- Produces: API authentication system

- [ ] **Step 1: Publish Sanctum config**

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

- [ ] **Step 2: Configure sanctum.php**

Read `config/sanctum.php` and ensure `stateful_domains` is configured for local development:

```php
'stateful_domains' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

- [ ] **Step 3: Create personal access client**

```bash
php artisan san:make "App Personal Access Client" --provider="Laravel\Sanctum\PersonalAccessClient"
php artisan migrate
```

- [ ] **Step 4: Commit**

```bash
git add config/sanctum.php
git commit -m "feat: configure Sanctum for API authentication"
```

---

## Task 17: REST API — Auth Controller

**Files:**
- Create: `app/Http/Controllers/Api/AuthController.php`

**Interfaces:**
- Consumes: Sanctum, User model
- Produces: Login, register, logout, user info endpoints

- [ ] **Step 1: Create API AuthController**

Create `app/Http/Controllers/Api/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|in:admin_jurusan,kadep,kepala_labor,teknisi,dosen,mahasiswa',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Api/AuthController.php
git commit -m "feat: add API auth controller with login, register, logout"
```

---

## Task 18: REST API — API Role Middleware

**Files:**
- Create: `app/Http/Middleware/ApiRoleMiddleware.php`

**Interfaces:**
- Consumes: User model roles
- Produces: Role-based API access control

- [ ] **Step 1: Create API role middleware**

Create `app/Http/Middleware/ApiRoleMiddleware.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Register middleware**

Modify `bootstrap/app.php` and add the alias:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'api.role' => \App\Http\Middleware\ApiRoleMiddleware::class,
    ]);
})
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Middleware/ApiRoleMiddleware.php bootstrap/app.php
git commit -m "feat: add API role middleware for access control"
```

---

## Task 19: REST API — API Resources

**Files:**
- Create: `app/Http/Resources/AlatResource.php`
- Create: `app/Http/Resources/BahanResource.php`
- Create: `app/Http/Resources/KategoriResource.php`
- Create: `app/Http/Resources/LaboratoriumResource.php`
- Create: `app/Http/Resources/UnitAlatResource.php`
- Create: `app/Http/Resources/PeminjamanAlatResource.php`
- Create: `app/Http/Resources/PengadaanAlatResource.php`
- Create: `app/Http/Resources/PengadaanBahanResource.php`
- Create: `app/Http/Resources/PemakaianBahanResource.php`
- Create: `app/Http/Resources/PemeliharaanAlatResource.php`
- Create: `app/Http/Resources/UserResource.php`

**Interfaces:**
- Consumes: Eloquent models
- Produces: Consistent JSON response format

- [ ] **Step 1: Create AlatResource**

Create `app/Http/Resources/AlatResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AlatResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama_alat' => $this->nama_alat,
            'merek' => $this->merek,
            'spesifikasi' => $this->spesifikasi,
            'tipe_pelacakan' => $this->tipe_pelacakan,
            'jumlah_alat' => $this->jumlah_alat,
            'foto' => $this->foto,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'laboratorium' => new LaboratoriumResource($this->whenLoaded('laboratorium')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

- [ ] **Step 2: Create remaining resources**

Create similar resources for all other models. Each follows the same pattern with their specific fields.

BahanResource:
```php
return [
    'id' => $this->id,
    'nama_bahan' => $this->nama_bahan,
    'stok_saat_ini' => $this->stok_saat_ini,
    'stok_minimum' => $this->stok_minimum,
    'satuan' => $this->satuan,
    'merek' => $this->merek,
    'kategori' => new KategoriResource($this->whenLoaded('kategori')),
    // ...
];
```

[Repeat for all 11 resources]

- [ ] **Step 3: Commit**

```bash
git add app/Http/Resources/
git commit -m "feat: add API resources for all models"
```

---

## Task 20: REST API — API Controllers

**Files:**
- Create: `app/Http/Controllers/Api/AlatController.php`
- Create: `app/Http/Controllers/Api/BahanController.php`
- Create: `app/Http/Controllers/Api/KategoriController.php`
- Create: `app/Http/Controllers/Api/LaboratoriumController.php`
- Create: `app/Http/Controllers/Api/UnitAlatController.php`
- Create: `app/Http/Controllers/Api/PeminjamanAlatController.php`
- Create: `app/Http/Controllers/Api/PengadaanAlatController.php`
- Create: `app/Http/Controllers/Api/PengadaanBahanController.php`
- Create: `app/Http/Controllers/Api/PemakaianBahanController.php`
- Create: `app/Http/Controllers/Api/PemeliharaanAlatController.php`
- Create: `app/Http/Controllers/Api/UserController.php`
- Create: `app/Http/Controllers/Api/LaporanController.php`

**Interfaces:**
- Consumes: API Resources, Form Requests, Models
- Produces: CRUD API endpoints

- [ ] **Step 1: Create Api/AlatController**

Create `app/Http/Controllers/Api/AlatController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlatRequest;
use App\Http\Resources\AlatResource;
use App\Models\Alat;
use Illuminate\Http\Request;

class AlatController extends Controller
{
    public function index(Request $request)
    {
        $query = Alat::with(['kategori', 'laboratorium']);

        if ($request->has('search')) {
            $query->where('nama_alat', 'like', "%{$request->search}%");
        }
        if ($request->has('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }
        if ($request->has('id_labor')) {
            $query->where('id_labor', $request->id_labor);
        }

        return AlatResource::collection($query->latest()->paginate(15));
    }

    public function store(AlatRequest $request)
    {
        $this->authorize('create', Alat::class);
        $alat = Alat::create($request->validated());
        return new AlatResource($alat->load(['kategori', 'laboratorium']));
    }

    public function show(Alat $alat)
    {
        $this->authorize('view', $alat);
        return new AlatResource($alat->load(['kategori', 'laboratorium', 'unitAlat']));
    }

    public function update(AlatRequest $request, Alat $alat)
    {
        $this->authorize('update', $alat);
        $alat->update($request->validated());
        return new AlatResource($alat->load(['kategori', 'laboratorium']));
    }

    public function destroy(Alat $alat)
    {
        $this->authorize('delete', $alat);
        $alat->delete();
        return response()->json(['message' => 'Alat berhasil dihapus']);
    }
}
```

- [ ] **Step 2: Create remaining API controllers**

Create similar API controllers for all other resources. Each follows the same pattern.

[Repeat for all 11 API controllers]

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Api/
git commit -m "feat: add API controllers for all resources"
```

---

## Task 21: REST API — Routes

**Files:**
- Create: `routes/api.php`
- Modify: `bootstrap/app.php`

**Interfaces:**
- Consumes: API Controllers, Sanctum, API Role Middleware
- Produces: Complete API route definitions

- [ ] **Step 1: Create api.php routes file**

Create `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AlatController;
use App\Http\Controllers\Api\BahanController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\LaboratoriumController;
use App\Http\Controllers\Api\UnitAlatController;
use App\Http\Controllers\Api\PeminjamanAlatController;
use App\Http\Controllers\Api\PengadaanAlatController;
use App\Http\Controllers\Api\PengadaanBahanController;
use App\Http\Controllers\Api\PemakaianBahanController;
use App\Http\Controllers\Api\PemeliharaanAlatController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LaporanController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('api.role:admin_jurusan');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Admin only
    Route::middleware('api.role:admin_jurusan,kadep,kepala_labor')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Lab management
    Route::apiResource('laboratorium', LaboratoriumController::class)
        ->only(['index', 'show', 'update']);

    // Equipment management
    Route::middleware('api.role:admin_jurusan,kepala_labor,teknisi,kadep')->group(function () {
        Route::apiResource('alat', AlatController::class);
        Route::apiResource('unit-alat', UnitAlatController::class);
        Route::apiResource('bahan', BahanController::class);
        Route::apiResource('kategori', KategoriController::class);
        Route::apiResource('pengadaan-alat', PengadaanAlatController::class);
        Route::apiResource('pengadaan-bahan', PengadaanBahanController::class);
        Route::apiResource('pemakaian-bahan', PemakaianBahanController::class);
        Route::apiResource('pemeliharaan', PemeliharaanAlatController::class);
    });

    // Borrowing (all roles)
    Route::apiResource('peminjaman', PeminjamanAlatController::class);

    // Reports
    Route::get('/laporan/{tipe}', [LaporanController::class, 'show']);
    Route::get('/dashboard', [LaporanController::class, 'dashboard']);
});
```

- [ ] **Step 2: Register API routes in bootstrap/app.php**

Modify `bootstrap/app.php`:

```php
->withRouting(
    api: __DIR__.'/../routes/api.php',
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

- [ ] **Step 3: Test API endpoints**

```bash
php artisan route:list --path=api
```

- [ ] **Step 4: Commit**

```bash
git add routes/api.php bootstrap/app.php
git commit -m "feat: add complete API routes with Sanctum auth"
```

---

## Task 22: Full Tests — Setup and Config

**Files:**
- Modify: `phpunit.xml`
- Modify: `tests/TestCase.php`

**Interfaces:**
- Consumes: PHPUnit, Laravel testing utilities
- Produces: Test environment configured for SQLite in-memory

- [ ] **Step 1: Update phpunit.xml for SQLite**

Read `phpunit.xml` and update the `<env>` section:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

- [ ] **Step 2: Add RefreshDatabase to TestCase**

Read `tests/TestCase.php` and add:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
}
```

- [ ] **Step 3: Commit**

```bash
git add phpunit.xml tests/TestCase.php
git commit -m "feat: configure test environment with SQLite in-memory"
```

---

## Task 23: Full Tests — Model Tests

**Files:**
- Create: `tests/Unit/Models/AlatTest.php`
- Create: `tests/Unit/Models/BahanTest.php`
- Create: `tests/Unit/Models/UnitAlatTest.php`
- Create: `tests/Unit/Models/PeminjamanAlatTest.php`
- Create: `tests/Unit/Models/PengadaanBahanTest.php`
- Create: `tests/Unit/Models/PemakaianBahanTest.php`
- Create: `tests/Unit/Models/UserTest.php`

**Interfaces:**
- Consumes: Eloquent models, factories
- Produces: Model relationship and attribute tests

- [ ] **Step 1: Create AlatTest**

Create `tests/Unit/Models/AlatTest.php`:

```php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\UnitAlat;

class AlatTest extends TestCase
{
    public function test_alat_belongs_to_kategori()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $alat = Alat::factory()->create(['id_kategori' => $kategori->id]);

        $this->assertInstanceOf(Kategori::class, $alat->kategori);
        $this->assertEquals($kategori->id, $alat->kategori->id);
    }

    public function test_alat_belongs_to_laboratorium()
    {
        $lab = Laboratorium::factory()->create();
        $alat = Alat::factory()->create(['id_labor' => $lab->id]);

        $this->assertInstanceOf(Laboratorium::class, $alat->laboratorium);
    }

    public function test_alat_has_many_unit_alat()
    {
        $alat = Alat::factory()->create();
        UnitAlat::factory()->count(3)->create(['id_alat' => $alat->id]);

        $this->assertCount(3, $alat->unitAlat);
    }

    public function test_alat_tipe_pelacakan_is_cast()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'unit']);

        $this->assertIsString($alat->tipe_pelacakan);
    }
}
```

- [ ] **Step 2: Create remaining model tests**

Create similar tests for all other models, testing their relationships and attributes.

[Repeat for all 7 model test files]

- [ ] **Step 3: Run model tests**

```bash
php artisan test --filter=Unit/Models
```

- [ ] **Step 4: Commit**

```bash
git add tests/Unit/Models/
git commit -m "feat: add model unit tests for all models"
```

---

## Task 24: Full Tests — Service Tests

**Files:**
- Create: `tests/Unit/Services/StokServiceTest.php`
- Create: `tests/Unit/Services/PeminjamanServiceTest.php`
- Create: `tests/Unit/Services/FIFOServiceTest.php`

**Interfaces:**
- Consumes: Service classes, Models, factories
- Produces: Business logic tests

- [ ] **Step 1: Create StokServiceTest**

Create `tests/Unit/Services/StokServiceTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StokService;
use App\Models\Bahan;
use App\Models\UnitAlat;

class StokServiceTest extends TestCase
{
    public function test_add_stok_increases_bahan_stock()
    {
        $bahan = Bahan::factory()->create(['stok_saat_ini' => 50]);
        $service = new StokService();

        $service->addStok($bahan, 20);

        $this->assertEquals(70, $bahan->fresh()->stok_saat_ini);
    }

    public function test_subtract_stok_decreases_bahan_stock()
    {
        $bahan = Bahan::factory()->create(['stok_saat_ini' => 50]);
        $service = new StokService();

        $service->subtractStok($bahan, 20);

        $this->assertEquals(30, $bahan->fresh()->stok_saat_ini);
    }

    public function test_subtract_stok_throws_on_insufficient()
    {
        $bahan = Bahan::factory()->create(['stok_saat_ini' => 10]);
        $service = new StokService();

        $this->expectException(\Exception::class);
        $service->subtractStok($bahan, 20);
    }

    public function test_update_unit_status()
    {
        $unit = UnitAlat::factory()->create(['status' => 'tersedia']);
        $service = new StokService();

        $service->updateUnitStatus($unit, 'dipinjam');

        $this->assertEquals('dipinjam', $unit->fresh()->status);
    }
}
```

- [ ] **Step 2: Create PeminjamanServiceTest**

Create `tests/Unit/Services/PeminjamanServiceTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PeminjamanService;
use App\Models\PeminjamanAlat;
use App\Models\Alat;
use App\Models\UnitAlat;
use App\Models\User;

class PeminjamanServiceTest extends TestCase
{
    public function test_create_peminjaman_decrements_stock()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'agregat', 'jumlah_alat' => 10]);
        $user = User::factory()->create();
        $service = new PeminjamanService();

        $peminjaman = $service->create([
            'id_alat' => $alat->id,
            'id_user_peminjam' => $user->id,
            'jumlah' => 3,
            'keperluan' => 'Praktikum',
            'waktu_peminjaman' => now(),
            'waktu_pengembalian' => now()->addDays(7),
        ]);

        $this->assertEquals(7, $alat->fresh()->jumlah_alat);
        $this->assertEquals('terpinjam', $peminjaman->status);
    }

    public function test_return_peminjaman_restores_stock()
    {
        $alat = Alat::factory()->create(['tipe_pelacakan' => 'agregat', 'jumlah_alat' => 7]);
        $peminjaman = PeminjamanAlat::factory()->create([
            'id_alat' => $alat->id,
            'jumlah' => 3,
            'status' => 'terpinjam',
        ]);
        $service = new PeminjamanService();

        $service->returnPeminjaman($peminjaman, ['kondisi_saat_pengembalian' => 'Baik']);

        $this->assertEquals(10, $alat->fresh()->jumlah_alat);
        $this->assertEquals('sudah_dikembalikan', $peminjaman->fresh()->status);
    }
}
```

- [ ] **Step 3: Create FIFOServiceTest**

Create `tests/Unit/Services/FIFOServiceTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FIFOService;
use App\Models\Bahan;
use App\Models\PengadaanBahan;

class FIFOServiceTest extends TestCase
{
    public function test_consume_fifo_uses_oldest_batch_first()
    {
        $bahan = Bahan::factory()->create(['stok_saat_ini' => 0]);
        $batch1 = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 50,
            'stok_tersisa_batch' => 50,
            'masa_expire_bahan' => now()->addYear(),
        ]);
        $batch2 = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 50,
            'stok_tersisa_batch' => 50,
            'masa_expire_bahan' => now()->addYears(2),
        ]);
        $service = new FIFOService();

        $service->consume($bahan, 30);

        $this->assertEquals(20, $batch1->fresh()->stok_tersisa_batch);
        $this->assertEquals(50, $batch2->fresh()->stok_tersisa_batch);
    }

    public function test_consume_fifo_handles_partial_batch()
    {
        $bahan = Bahan::factory()->create(['stok_saat_ini' => 0]);
        $batch1 = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 20,
            'stok_tersisa_batch' => 20,
        ]);
        $batch2 = PengadaanBahan::factory()->create([
            'id_bahan' => $bahan->id,
            'jumlah' => 50,
            'stok_tersisa_batch' => 50,
        ]);
        $service = new FIFOService();

        $service->consume($bahan, 30);

        $this->assertEquals(0, $batch1->fresh()->stok_tersisa_batch);
        $this->assertEquals(40, $batch2->fresh()->stok_tersisa_batch);
    }
}
```

- [ ] **Step 4: Run service tests**

```bash
php artisan test --filter=Unit/Services
```

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/Services/
git commit -m "feat: add service unit tests for StokService, PeminjamanService, FIFOService"
```

---

## Task 25: Full Tests — Controller Tests

**Files:**
- Create: `tests/Feature/Controllers/AlatControllerTest.php`
- Create: `tests/Feature/Controllers/BahanControllerTest.php`
- Create: `tests/Feature/Controllers/KategoriControllerTest.php`
- Create: `tests/Feature/Controllers/LaboratoriumControllerTest.php`
- Create: `tests/Feature/Controllers/UnitAlatControllerTest.php`
- Create: `tests/Feature/Controllers/PeminjamanAlatControllerTest.php`
- Create: `tests/Feature/Controllers/PengadaanAlatControllerTest.php`
- Create: `tests/Feature/Controllers/PengadaanBahanControllerTest.php`
- Create: `tests/Feature/Controllers/PemakaianBahanControllerTest.php`
- Create: `tests/Feature/Controllers/PemeliharaanAlatControllerTest.php`
- Create: `tests/Feature/Controllers/UserControllerTest.php`
- Create: `tests/Feature/Controllers/DashboardControllerTest.php`
- Create: `tests/Feature/Controllers/LaporanControllerTest.php`

**Interfaces:**
- Consumes: Controllers, Models, Factories, Form Requests
- Produces: Feature tests for all controllers

- [ ] **Step 1: Create AlatControllerTest**

Create `tests/Feature/Controllers/AlatControllerTest.php`:

```php
<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Laboratorium;

class AlatControllerTest extends TestCase
{
    private $admin;
    private $teknisi;
    private $dosen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin_jurusan']);
        $this->teknisi = User::factory()->create(['role' => 'teknisi']);
        $this->dosen = User::factory()->create(['role' => 'dosen']);
    }

    public function test_index_requires_auth()
    {
        $this->get(route('alat.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_200_for_admin()
    {
        $this->actingAs($this->admin)->get(route('alat.index'))->assertOk();
    }

    public function test_store_validates_required_fields()
    {
        $this->actingAs($this->admin)
            ->post(route('alat.store'), [])
            ->assertSessionHasErrors(['nama_alat', 'id_kategori', 'id_labor', 'tipe_pelacakan']);
    }

    public function test_store_creates_alat()
    {
        $kategori = Kategori::factory()->create(['jenis' => 'alat']);
        $lab = Laboratorium::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('alat.store'), [
                'nama_alat' => 'Multimeter Digital',
                'id_kategori' => $kategori->id,
                'id_labor' => $lab->id,
                'tipe_pelacakan' => 'agregat',
                'jumlah_alat' => 5,
            ]);

        $this->assertDatabaseHas('alat', ['nama_alat' => 'Multimeter Digital']);
    }

    public function test_update_modifies_alat()
    {
        $alat = Alat::factory()->create(['nama_alat' => 'Old Name']);

        $this->actingAs($this->admin)
            ->put(route('alat.update', $alat), ['nama_alat' => 'New Name']);

        $this->assertDatabaseHas('alat', ['id' => $alat->id, 'nama_alat' => 'New Name']);
    }

    public function test_destroy_deletes_alat()
    {
        $alat = Alat::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('alat.destroy', $alat));

        $this->assertSoftDeleted('alat', ['id' => $alat->id]);
    }

    public function test_dosen_cannot_access_alat()
    {
        $this->actingAs($this->dosen)
            ->get(route('alat.index'))
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Create remaining controller tests**

Create similar tests for all other controllers, testing:
- Auth requirements
- CRUD operations
- Validation
- Role authorization (admin can, dosen cannot, etc.)

[Repeat for all 13 controller test files]

- [ ] **Step 3: Run all controller tests**

```bash
php artisan test --filter=Feature/Controllers
```

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Controllers/
git commit -m "feat: add feature tests for all 13 controllers"
```

---

## Task 26: Full Tests — Run All Tests

**Files:**
- No new files

**Interfaces:**
- Consumes: All test files (Tasks 23-25)
- Produces: Passing test suite

- [ ] **Step 1: Run full test suite**

```bash
php artisan test
```

- [ ] **Step 2: Fix any failing tests**

Review and fix any test failures.

- [ ] **Step 3: Run tests again to confirm all pass**

```bash
php artisan test
```

- [ ] **Step 4: Commit final test results**

```bash
git add -A
git commit -m "feat: complete test suite with 100+ tests passing"
```

---

## Final Verification

- [ ] **Step 1: Verify all packages installed**

```bash
composer show | grep -E "spatie|sanctum|qrcode|dompdf"
```

- [ ] **Step 2: Verify all routes exist**

```bash
php artisan route:list
```

- [ ] **Step 3: Verify all tests pass**

```bash
php artisan test
```

- [ ] **Step 4: Verify PDF export works**

Visit `/laporan/alat` and click "Export PDF". Verify download.

- [ ] **Step 5: Verify activity log works**

Perform a CRUD action and check `/activity-log` for entry.

- [ ] **Step 6: Verify charts render on dashboards**

Login as each role and verify charts appear.

- [ ] **Step 7: Verify QR code works**

Visit `/unit-alat/{id}/qr` and verify QR code displays.

- [ ] **Step 8: Verify API endpoints work**

```bash
curl -X POST http://localhost:8000/api/login -H "Content-Type: application/json" -d '{"email":"admin@inventaris.test","password":"password"}'
```

- [ ] **Step 9: Final commit**

```bash
git add -A
git commit -m "feat: complete all remaining features - 100% project completion"
```
