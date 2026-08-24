# Pengembalian Bahan dengan Verifikasi - Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement return verification system for pemakaian bahan where mahasiswa/dosen returns go through pending→verified flow, while kadep returns are instant.

**Architecture:** Add `status_pengembalian` and `waktu_pengembalian` columns to `pemakaian_bahan` table. Update controller to handle role-based return flow. Add verify/reject endpoints for staff.

**Tech Stack:** Laravel 13, PHP 8.5, MySQL, Bootstrap 5, Blade views

## Global Constraints

- Laravel 13.23.0, PHP 8.5.9, MySQL
- Bootstrap 5.3.7, Blade views
- Roles: admin_jurusan, kepala_labor, teknisi, dosen, mahasiswa, kadep
- FIFO stock management via FIFOService
- Activity logging via SpatieActivitylog

---

## File Structure

| File | Purpose |
|------|---------|
| `database/migrations/xxxx_add_status_pengembalian_to_pemakaian_bahan_table.php` | Add status_pengembalian + waktu_pengembalian columns |
| `app/Models/PemakaianBahan.php` | Add fillable, cast |
| `app/Http/Controllers/PemakaianBahanController.php` | Update returnBahan(), add verifyReturn(), rejectReturn() |
| `app/Policies/PemakaianBahanPolicy.php` | Update return, add verifyReturn, rejectReturn abilities |
| `routes/web.php` | Add verify-return, reject-return routes |
| `resources/views/pemakaian_bahan/show.blade.php` | Show status, verify/reject buttons |
| `resources/views/dashboard/user.blade.php` | Show pending returns for staff |

---

### Task 1: Database Migration

**Files:**
- Create: `database/migrations/2026_08_24_000001_add_status_pengembalian_to_pemakaian_bahan_table.php`

**Interfaces:**
- Produces: `status_pengembalian` (nullable enum string), `waktu_pengembalian` (nullable timestamp) columns on `pemakaian_bahan` table

- [ ] **Step 1: Create migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->string('status_pengembalian', 20)->nullable()->after('jumlah_pengembalian');
            $table->timestamp('waktu_pengembalian')->nullable()->after('status_pengembalian');
        });
    }

    public function down(): void
    {
        Schema::table('pemakaian_bahan', function (Blueprint $table) {
            $table->dropColumn(['status_pengembalian', 'waktu_pengembalian']);
        });
    }
};
```

- [ ] **Step 2: Run migration**

Run: `php artisan migrate`
Expected: Migration runs successfully

---

### Task 2: Update Model

**Files:**
- Modify: `app/Models/PemakaianBahan.php`

**Interfaces:**
- Produces: `status_pengembalian` and `waktu_pengembalian` in fillable array

- [ ] **Step 1: Add to fillable**

Add `'status_pengembalian'` and `'waktu_pengembalian'` to the `$fillable` array.

```php
protected $fillable = [
    // ... existing fields
    'status_pengembalian',
    'waktu_pengembalian',
];
```

- [ ] **Step 2: Add to casts (if needed)**

The `waktu_pengembalian` should be cast to `datetime`. Check if existing timestamps are auto-cast. If not, add:

```php
protected $casts = [
    // ... existing casts
    'waktu_pengembalian' => 'datetime',
];
```

---

### Task 3: Update Policy

**Files:**
- Modify: `app/Policies/PemakaianBahanPolicy.php`

**Interfaces:**
- Produces: Updated `return()` ability, new `verifyReturn()` and `rejectReturn()` abilities

- [ ] **Step 1: Update `return()` ability**

Current logic requires `id_user_verifikasi` not null, `jumlah_pengembalian` null, owner or staff.

New logic: additionally require `status_pengembalian` is null.

```php
public function return(User $user, PemakaianBahan $pemakaian)
{
    $isOwner = $pemakaian->id_user_pemakai === $user->id;
    $isStaff = in_array($user->role, ['admin_jurusan', 'kepala_labor', 'teknisi']);

    return $pemakaian->id_user_verifikasi !== null
        && $pemakaian->jumlah_pengembalian === null
        && $pemakaian->status_pengembalian === null
        && ($isOwner || $isStaff);
}
```

- [ ] **Step 2: Add `verifyReturn()` ability**

```php
public function verifyReturn(User $user, PemakaianBahan $pemakaian)
{
    return in_array($user->role, ['admin_jurusan', 'kepala_labor'])
        && $pemakaian->status_pengembalian === 'pending';
}
```

- [ ] **Step 3: Add `rejectReturn()` ability**

```php
public function rejectReturn(User $user, PemakaianBahan $pemakaian)
{
    return in_array($user->role, ['admin_jurusan', 'kepala_labor'])
        && $pemakaian->status_pengembalian === 'pending';
}
```

---

### Task 4: Update Controller - Return Flow

**Files:**
- Modify: `app/Http/Controllers/PemakaianBahanController.php`

**Interfaces:**
- Consumes: `FIFOService::reverseConsumeFromBatches()`
- Produces: Updated `returnBahan()`, new `verifyReturn()`, `rejectReturn()` methods

- [ ] **Step 1: Update `returnBahan()` method**

Replace the current return logic with role-based flow:

```php
public function returnBahan(Request $request, $id)
{
    $pemakaian = PemakaianBahan::findOrFail($id);
    $this->authorize('return', $pemakaian);

    $validated = $request->validate([
        'jumlah_terpakai' => ['required', 'integer', 'min:1', 'lte:' . $pemakaian->jumlah_pengambilan],
        'jumlah_pengembalian' => ['required', 'integer', 'min:0'],
    ]);

    $sisa = $pemakaian->jumlah_pengambilan - $validated['jumlah_terpakai'];

    if ($validated['jumlah_pengembalian'] > $sisa) {
        return back()->withErrors(['jumlah_pengembalian' => 'Jumlah pengembalian melebihi sisa (maksimal ' . $sisa . ')']);
    }

    if ($validated['jumlah_pengembalian'] < 0) {
        return back()->withErrors(['jumlah_pengembalian' => 'Jumlah pengembalian tidak boleh negatif']);
    }

    $oldData = $pemakaian->toArray();
    $user = Auth::user();

    DB::transaction(function () use ($pemakaian, $validated, $user) {
        $pemakaian->update([
            'jumlah_terpakai' => $validated['jumlah_terpakai'],
            'jumlah_pengembalian' => $validated['jumlah_pengembalian'],
            'waktu_pengembalian' => now(),
        ]);

        if ($user->role === 'kadep') {
            // Kadep: langsung verified, stok langsung dikembalikan
            $pemakaian->update(['status_pengembalian' => 'verified']);

            if ($validated['jumlah_pengembalian'] > 0) {
                $this->fifoService->reverseConsumeFromBatches(
                    $pemakaian->id_bahan,
                    $validated['jumlah_pengembalian']
                );
            }
        } else {
            // Mahasiswa/Dosen: status pending, stok belum dikembalikan
            $pemakaian->update(['status_pengembalian' => 'pending']);
        }
    });

    $pemakaian->refresh();

    activity()
        ->performedOn($pemakaian)
        ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
        ->event('returned')
        ->log('Pengembalian sisa bahan dicatat');

    $statusMessage = $user->role === 'kadep'
        ? 'Pengembalian sisa bahan berhasil dicatat'
        : 'Pengembalian sisa bahan berhasil disubmit, menunggu verifikasi';

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json(['message' => $statusMessage]);
    }

    return redirect()->route('pemakaian_bahan.show', $pemakaian)
        ->with('success', $statusMessage);
}
```

- [ ] **Step 2: Add `verifyReturn()` method**

```php
public function verifyReturn(Request $request, $id)
{
    $pemakaian = PemakaianBahan::findOrFail($id);
    $this->authorize('verifyReturn', $pemakaian);

    $oldData = $pemakaian->toArray();

    DB::transaction(function () use ($pemakaian) {
        $pemakaian->update([
            'status_pengembalian' => 'verified',
        ]);

        // Sekarang kembalikan stok
        if ($pemakaian->jumlah_pengembalian > 0) {
            $this->fifoService->reverseConsumeFromBatches(
                $pemakaian->id_bahan,
                $pemakaian->jumlah_pengembalian
            );
        }
    });

    $pemakaian->refresh();

    activity()
        ->performedOn($pemakaian)
        ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
        ->event('verified')
        ->log('Pengembalian bahan berhasil diverifikasi');

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json(['message' => 'Pengembalian bahan berhasil diverifikasi']);
    }

    return redirect()->route('pemakaian_bahan.show', $pemakaian)
        ->with('success', 'Pengembalian bahan berhasil diverifikasi');
}
```

- [ ] **Step 3: Add `rejectReturn()` method**

```php
public function rejectReturn(Request $request, $id)
{
    $pemakaian = PemakaianBahan::findOrFail($id);
    $this->authorize('rejectReturn', $pemakaian);

    $oldData = $pemakaian->toArray();

    $pemakaian->update([
        'status_pengembalian' => 'rejected',
    ]);

    $pemakaian->refresh();

    activity()
        ->performedOn($pemakaian)
        ->withProperties(['old' => $oldData, 'attributes' => $pemakaian->toArray()])
        ->event('rejected')
        ->log('Pengembalian bahan ditolak');

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json(['message' => 'Pengembalian bahan ditolak']);
    }

    return redirect()->route('pemakaian_bahan.show', $pemakaian)
        ->with('success', 'Pengembalian bahan ditolak');
}
```

---

### Task 5: Add Routes

**Files:**
- Modify: `routes/web.php`

**Interfaces:**
- Produces: `pemakaian_bahan.verify_return` and `pemakaian_bahan.reject_return` routes

- [ ] **Step 1: Add routes**

Find the existing pemakaian_bahan route group and add after the `return` route:

```php
Route::post('pemakaian-bahan/{pemakaian}/verify-return', [PemakaianBahanController::class, 'verifyReturn'])
    ->name('pemakaian_bahan.verify_return');
Route::post('pemakaian-bahan/{pemakaian}/reject-return', [PemakaianBahanController::class, 'rejectReturn'])
    ->name('pemakaian_bahan.reject_return');
```

- [ ] **Step 2: Verify route is registered**

Run: `php artisan route:list --name=pemakaian_bahan`
Expected: Both new routes appear

---

### Task 6: Update Show View - Return Status & Verify/Reject Buttons

**Files:**
- Modify: `resources/views/pemakaian_bahan/show.blade.php`

**Interfaces:**
- Consumes: `$pemakaian->status_pengembalian`, `@can('verifyReturn', $pemakaian)`, `@can('rejectReturn', $pemakaian)`

- [ ] **Step 1: Add status badge for return**

After the existing verification status display, add return status:

```blade
{{-- Status Pengembalian --}}
@if($pemakaian->status_pengembalian)
    <div class="mb-3">
        <strong>Status Pengembalian:</strong>
        @if($pemakaian->status_pengembalian === 'pending')
            <span class="badge bg-warning">Menunggu Verifikasi</span>
        @elseif($pemakaian->status_pengembalian === 'verified')
            <span class="badge bg-success">Diverifikasi</span>
        @elseif($pemakaian->status_pengembalian === 'rejected')
            <span class="badge bg-danger">Ditolak</span>
        @endif
        @if($pemakaian->waktu_pengembalian)
            <small class="text-muted d-block">Disubmit: {{ $pemakaian->waktu_pengembalian->format('d-m-Y H:i') }}</small>
        @endif
    </div>
@endif
```

- [ ] **Step 2: Add verify/reject buttons for pending returns**

After the return status display, add buttons for staff:

```blade
{{-- Verifikasi Pengembalian --}}
@can('verifyReturn', $pemakaian)
<div class="mb-3">
    <form action="{{ route('pemakaian_bahan.verify_return', $pemakaian) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Verifikasi pengembalian bahan ini? Stok akan dikembalikan ke inventory.')">
        @csrf
        <button type="submit" class="btn btn-success btn-sm">
            <i class="fas fa-check"></i> Verifikasi Pengembalian
        </button>
    </form>
    <form action="{{ route('pemakaian_bahan.reject_return', $pemakaian) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Tolak pengembalian bahan ini?')">
        @csrf
        <button type="submit" class="btn btn-danger btn-sm">
            <i class="fas fa-times"></i> Tolak Pengembalian
        </button>
    </form>
</div>
@endcan
```

- [ ] **Step 3: Update return button visibility**

Change the `@can('return', $pemakaian)` condition to also check `status_pengembalian` is null:

The policy already handles this (Task 3), so the button will automatically hide when status is not null.

- [ ] **Step 4: Clear view cache**

Run: `php artisan view:clear`

---

### Task 7: Update Dashboard Views

**Files:**
- Modify: `resources/views/dashboard/user.blade.php`

**Interfaces:**
- Consumes: `$pendingPemakaianBahan` (with return status), `$myPemakaianBahan` (with return status)

- [ ] **Step 1: Add status column to "Pemakaian Bahan Perlu Diverifikasi" table**

In the staff section, add a column showing return status:

```blade
<th>Status Pengembalian</th>
```

And in the row:

```blade
<td>
    @if($item->status_pengembalian === 'pending')
        <span class="badge bg-warning">Menunggu Verifikasi</span>
        <form action="{{ route('pemakaian_bahan.verify_return', $item) }}" method="POST" class="d-inline mt-1"
              onsubmit="return confirm('Verifikasi pengembalian ini?')">
            @csrf
            <button type="submit" class="btn btn-success btn-xs"><i class="fas fa-check"></i></button>
        </form>
        <form action="{{ route('pemakaian_bahan.reject_return', $item) }}" method="POST" class="d-inline mt-1"
              onsubmit="return confirm('Tolak pengembalian ini?')">
            @csrf
            <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-times"></i></button>
        </form>
    @elseif($item->status_pengembalian === 'verified')
        <span class="badge bg-success">Diverifikasi</span>
    @elseif($item->status_pengembalian === 'rejected')
        <span class="badge bg-danger">Ditolak</span>
    @else
        <span class="badge bg-secondary">-</span>
    @endif
</td>
```

- [ ] **Step 2: Update "Pemakaian Bahan Aktif" section for mahasiswa/dosen**

Show return status in the user's active bahan list:

```blade
<td>
    @if($item->status_pengembalian === 'pending')
        <span class="badge bg-warning">Menunggu Verifikasi</span>
    @elseif($item->status_pengembalian === 'verified')
        <span class="badge bg-success">Diverifikasi</span>
    @elseif($item->status_pengembalian === 'rejected')
        <span class="badge bg-danger">Ditolak</span>
    @else
        <span class="badge bg-secondary">Belum Dikembalikan</span>
    @endif
</td>
```

- [ ] **Step 3: Update dashboard controller queries**

In `DashboardController::userDashboard()`, update the `pendingPemakaianBahan` query to also include items with `status_pengembalian = 'pending'`:

```php
$pendingPemakaianBahan = PemakaianBahan::whereNull('id_user_verifikasi')
    ->whereNull('jumlah_pengembalian')
    ->orWhere('status_pengembalian', 'pending')
    ->with(['bahan', 'userPemakai', 'userVerifikasi'])
    ->latest()
    ->get();
```

And update `myPemakaianBahan` to exclude pending returns:

```php
$myPemakaianBahan = PemakaianBahan::where('id_user_pemakai', Auth::id())
    ->whereNotNull('id_user_verifikasi')
    ->whereNull('jumlah_pengembalian')
    ->whereNull('status_pengembalian')
    ->with(['bahan', 'userPemakai', 'userVerifikasi'])
    ->latest()
    ->get();
```

- [ ] **Step 4: Clear view cache**

Run: `php artisan view:clear`

---

### Task 8: Final Verification

- [ ] **Step 1: Clear all caches**

Run: `php artisan view:clear && php artisan cache:clear`

- [ ] **Step 2: Test return flow for mahasiswa/dosen**

1. Login as mahasiswa/dosen
2. Go to pemakaian bahan show page
3. Click "Kembalikan Sisa"
4. Fill jumlah terpakai and jumlah pengembalian
5. Submit → should show "Menunggu Verifikasi" status
6. Check that stock is NOT restored yet

- [ ] **Step 3: Test verify flow for kalab**

1. Login as kalab
2. Go to pemakaian bahan show page with pending return
3. Click "Verifikasi Pengembalian"
4. Confirm → should show "Diverifikasi" status
5. Check that stock IS restored

- [ ] **Step 4: Test kadep return flow**

1. Login as kadep
2. Go to pemakaian bahan show page
3. Click "Kembalikan Sisa"
4. Submit → should show "Diverifikasi" status immediately
5. Check that stock IS restored

- [ ] **Step 5: Test reject flow**

1. Login as kalab
2. Find a pending return
3. Click "Tolak Pengembalian"
4. Confirm → should show "Ditolak" status
5. Check that stock is NOT restored

- [ ] **Step 6: Verify buttons are hidden correctly**

1. Return button should be hidden when status is not null
2. Verify/reject buttons should only show when status='pending'
3. Users cannot edit a pending return
