# Code Audit Findings - inventaris-bengkel2

## Phase 1: Root Cause Investigation Complete

### CRITICAL ISSUES FOUND

#### 1. **Incomplete Controller: Pinjam_alat.php**
- **File:** `app/Http/Controllers/Pinjam_alat.php`
- **Issue:** Controller is completely empty with only a comment `//pppp22`
- **Impact:** No routes can actually handle equipment borrowing operations
- **Status:** BLOCKING - feature is non-functional
- **Evidence:** Lines 1-10 show empty class definition

#### 2. **Missing Foreign Key Validation in PeminjamanAlat Model**
- **File:** `app/Models/PeminjamanAlat.php`
- **Issue:** Allows both `id_alat` and `id_unit_alat` to be nullable, but no validation ensures at least ONE is set
- **Problem:** Can create records with neither ID set - violates domain logic
- **Migration shows:** Both ForeignKeys are nullable (lines 16-25 in migration)
- **Evidence:** No guard clause or validation in model or controller

#### 3. **No Authorization/Access Control**
- **Files:** All controllers inherit from `Controller` base class
- **Issue:** No middleware to check user roles or permissions
- **Problem:** Any authenticated user can access any feature (admin routes same as student routes)
- **User roles exist:** admin_jurusan, kepala_labor, kadep, teknisi, dosen, mahasiswa (in users table)
- **Missing:** No route middleware or policy checks for role-based access
- **Evidence:** Routes only have `auth` middleware, no `role:*` checks

#### 4. **Incomplete Profile Update Validation**
- **File:** `app/Http/Requests/ProfileUpdateRequest.php`
- **Issue:** Missing validation for fields that exist in User model
- **Missing fields:** `no_hp`, `no_induk`, `foto`, `role`, `status`
- **Problem:** Users can update profile but can't change phone/ID/photo through form
- **Current state:** Only validates `nama` and `email` (lines 19-36)

#### 5. **Registration Defaults User Role Hardcoded**
- **File:** `app/Http/Controllers/Auth/RegisteredUserController.php` line 51
- **Issue:** `'role' => 'mahasiswa'` hardcoded - only students can register
- **Problem:** Admins and other roles must be created manually; no admin registration flow
- **Should be:** Configurable or selectable during registration

#### 6. **Inconsistent Null Handling in PemakaianBahan**
- **File:** `app/Models/PemakaianBahan.php`
- **Issue:** `id_user_verifikasi` can be null (line 19, but no nullable constraint shown in fillable)
- **Problem:** Material usage can be recorded without verification - tracking integrity compromised
- **Question:** Is verification optional or mandatory?

#### 7. **Missing Relationship Validation in PengadaanBahan**
- **File:** `app/Models/PengadaanBahan.php`
- **Issue:** `scopeTersediaUrutExpiry` is defined but never called from controllers
- **Problem:** Material batches likely NOT being consumed FIFO by expiry date
- **Impact:** Risk of expired materials being used; inventory waste
- **Evidence:** Scope exists (lines 56-61) but no usage in codebase

#### 8. **Laboratorium kalab Foreign Key Not Validated**
- **File:** `database/migrations/2026_08_04_000003_create_laboratorium_table.php`
- **Issue:** `id_user_kalab` (head/lab manager) can be null
- **Problem:** Lab can exist without a manager - unclear ownership/responsibility
- **Should be:** Required field or have clear business logic for null case

#### 9. **No Timestamps on PemeliharaanAlat Results**
- **File:** `app/Models/PemeliharaanAlat.php`
- **Issue:** Tracks `tanggal_cek` and `tanggal_cek_berikutnya` but no `created_at`/`updated_at`
- **Problem:** Can't track when maintenance record was created vs. when check occurred
- **Current:** Model has `protected $table = 'pemeliharaan_alat'` but timestamps likely missing from migration

#### 10. **No Decimal Precision on PengadaanBahan Prices**
- **File:** `app/Models/PengadaanBahan.php` line 35
- **Issue:** `harga_perolehan` cast as `decimal:2` but migration may not match
- **Problem:** Potential rounding/precision issues with financial data
- **Requires:** Verification that migration defines `decimal(8,2)` or similar

#### 11. **Missing Query Scope for Active Status**
- **File:** `app/Models/User.php` and related models
- **Issue:** No `scopeActive()` or `whereActive()` method
- **Problem:** Queries can't easily filter inactive users - may show deleted/inactive personnel
- **Impact:** UX: inactive staff appearing in dropdown lists

#### 12. **No Soft Deletes**
- **File:** All models
- **Issue:** Using hard delete (e.g., `$user->delete()` in ProfileController line 50)
- **Problem:** Deleted inventory records can't be recovered; audit trail lost
- **Should use:** `SoftDeletes` trait for historical tracking

#### 13. **Inconsistent Table Naming Convention**
- **Tables:** `alat`, `bahan`, `laboratorium` (singular, lowercase)
- **vs Laravel convention:** `products`, `categories` (plural)
- **Issue:** Not breaking functionality but inconsistent with Laravel ecosystem expectations
- **Note:** Migration files follow convention but table names don't

#### 14. **Missing Database Indexes on Foreign Keys**
- **Issue:** Migrations don't show indexes on frequently-queried columns
- **Example:** `id_user_peminjam`, `id_kategori`, `id_labor`
- **Problem:** Slow queries when filtering by user/category/lab
- **Performance impact:** Noticeable with 1000+ records

---

## Summary by Severity

| Severity | Count | Issues |
|----------|-------|--------|
| CRITICAL | 3 | Empty controller, no auth/roles, missing domain validation |
| HIGH | 5 | Incomplete validation, hardcoded roles, missing scopes, hard deletes |
| MEDIUM | 4 | Naming inconsistency, missing indexes, decimal precision, null handling |
| LOW | 2 | Timestamps missing, incomplete profile form |

---

## Next Steps (Phase 2-4)

1. **Implement Pinjam_alat controller** - BLOCKING
2. **Add authorization middleware and policies** - CRITICAL
3. **Add domain validation** - CRITICAL
4. **Implement soft deletes** - HIGH
5. **Add role-based access control** - HIGH
6. **Fix hardcoded defaults** - HIGH
7. **Add database indexes** - MEDIUM
8. **Audit all migrations** - MEDIUM

