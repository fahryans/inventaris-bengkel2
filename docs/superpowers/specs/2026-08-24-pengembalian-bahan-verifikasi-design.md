# Design: Sistem Pengembalian Bahan dengan Verifikasi

## Overview

Sistem pengembalian bahan (sisa) yang berbeda berdasarkan role:
- **Mahasiswa/Dosen**: Submit → pending → kalab/teknisi verifikasi → stok masuk
- **Kadep**: Submit → stok langsung masuk (tanpa verifikasi)

## Current State

- Tabel `pemakaian_bahan` memiliki kolom: `jumlah_pengembalian` (nullable), `jumlah_terpakai` (nullable)
- Return flow saat ini: user submit → `jumlah_pengembalian` diisi → stok langsung dikembalikan via FIFO reversal
- Tidak ada status untuk return (pending/verified/rejected)
- Verification hanya untuk pembuatan pemakaian bahan (bukan untuk pengembalian)

## New Flow

```
Mahasiswa/Dosen:
  Submit return → status='pending', waktu_pengembalian=set
  → Stok BELUM dikembalikan
  → Kalab/teknisi verifikasi → status='verified', stok dikembalikan via FIFO
  → Atau reject → status='rejected', user bisa submit ulang

Kadep:
  Submit return → status='verified', waktu_pengembalian=set
  → Stok LANGSUNG dikembalikan via FIFO
  → Tidak perlu verifikasi
```

## Database Changes

Tambah 2 kolom ke tabel `pemakaian_bahan`:

| Kolom | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| `status_pengembalian` | string(20) | Yes | null | Status return: null, 'pending', 'verified', 'rejected' |
| `waktu_pengembalian` | timestamp | Yes | null | Kapan return disubmit |

## Code Changes

### 1. Migration
- Create migration to add `status_pengembalian` and `waktu_pengembalian` columns

### 2. Model (`PemakaianBahan.php`)
- Add `status_pengembalian` and `waktu_pengembalian` to fillable
- Add cast: `status_pengembalian` → nullable string

### 3. Controller (`PemakaianBahanController.php`)

#### `returnBahan()` method:
- Validate `jumlah_terpakai` and `jumlah_pengembalian` (same as before)
- Check user role:
  - **If kadep**: set `status_pengembalian = 'verified'`, `waktu_pengembalian = now()`, immediately reverse stock
  - **If mahasiswa/dosen**: set `status_pengembalian = 'pending'`, `waktu_pengembalian = now()`, do NOT reverse stock yet
- For AJAX response, include the status

#### New `verifyReturn()` method:
- Check if `status_pengembalian === 'pending'`
- Set `status_pengembalian = 'verified'`, `waktu_verifikasi_pengembalian = now()`
- Reverse stock via `fifoService->reverseConsumeFromBatches()`
- Log activity

#### New `rejectReturn()` method:
- Check if `status_pengembalian === 'pending'`
- Set `status_pengembalian = 'rejected'`
- Log activity

### 4. Policy (`PemakaianBahanPolicy.php`)

#### `return` ability:
- Current: requires `id_user_verifikasi` not null, `jumlah_pengembalian` null, owner or staff
- New: additionally require `status_pengembalian` is null (not yet submitted)

#### New `verifyReturn` ability:
- Only `admin_jurusan` or `kepala_labor`
- Requires `status_pengembalian === 'pending'`

#### New `rejectReturn` ability:
- Only `admin_jurusan` or `kepala_labor`
- Requires `status_pengembalian === 'pending'`

### 5. Routes
Add new routes:
```php
Route::post('pemakaian-bahan/{pemakaian}/verify-return', [PemakaianBahanController::class, 'verifyReturn'])
    ->name('pemakaian_bahan.verify_return');
Route::post('pemakaian-bahan/{pemakaian}/reject-return', [PemakaianBahanController::class, 'rejectReturn'])
    ->name('pemakaian_bahan.reject_return');
```

### 6. Views

#### `pemakaian_bahan/show.blade.php`:
- Show return status badge (pending/verified/rejected)
- Show "Verifikasi Pengembalian" button for staff when status='pending'
- Show "Tolak Pengembalian" button for staff when status='pending'
- Disable "Kembalikan Sisa" button when status is not null

#### `dashboard/user.blade.php`:
- For mahasiswa/dosen: show pending returns with status
- For staff: show pending returns that need verification

#### `dashboard/teknisi.blade.php`:
- Show pending return verifications

## Testing
- Test return flow for mahasiswa/dosen (pending → verified)
- Test return flow for kadep (direct verified)
- Test reject flow
- Test stock restoration timing (only after verify for mahasiswa/dosen)
- Test that pending returns cannot be edited
- Test that duplicate returns are prevented
