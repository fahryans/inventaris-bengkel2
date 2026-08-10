# ✅ ACTIONABLE CHECKLIST - NEXT STEPS

**Project:** inventaris-bengkel2  
**Current Progress:** 70% complete  
**Target:** Production-ready  
**Estimated Time:** 12-25 hours depending on scope  

---

## 🎯 PHASE 1: BLOCKING ISSUES (12-14 hours) - START HERE

### [ ] 1. Create Peminjaman Views (2-3 hours)

**Status:** ❌ BLOCKING - No UI for borrowing feature

**Files to create:**
```
resources/views/peminjaman/
├── index.blade.php          # List all borrowings
├── create.blade.php         # New borrowing form
├── show.blade.php           # View borrowing details
├── edit.blade.php           # Edit active borrowing
└── return.blade.php         # Return equipment form (optional)
```

**What to include in each:**
- `index`: Table with all borrowings, status badges, action buttons
- `create`: Form with equipment selector, borrower info, expected return date
- `show`: Display borrowing details, status, condition notes, action buttons
- `edit`: Form to modify borrowing details, return date, etc.

**Reference:** Check existing views like `resources/views/alat/` for styling patterns

**Checklist:**
- [ ] Create index view with table & filters
- [ ] Create create form with validation feedback
- [ ] Create show view with details & actions
- [ ] Create edit view
- [ ] Test all forms submit correctly
- [ ] Verify styling matches site theme

---

### [ ] 2. Add Authorization Enforcement (2-3 hours)

**Status:** ⚠️ SECURITY RISK - Policies exist but not used

**What to do:**
1. Open `app/Http/Controllers/Pinjam_alat.php`
2. Add `$this->authorize()` calls to each method
3. Update other controllers similarly
4. Add @can/@cannot checks in Blade views

**Example:**
```php
// In controller
public function edit(PeminjamanAlat $peminjaman)
{
    $this->authorize('update', $peminjaman);
    // ... rest of method
}

// In blade
@can('update', $peminjaman)
    <a href="{{ route('peminjaman.edit', $peminjaman) }}">Edit</a>
@endcan
```

**Checklist:**
- [ ] Add $this->authorize() to Pinjam_alat controller methods
- [ ] Add $this->authorize() to other controllers
- [ ] Add @can/@cannot directives in views
- [ ] Test that unauthorized users are denied
- [ ] Test that authorized users can access
- [ ] Clear cache: php artisan cache:clear

---

### [ ] 3. Set Up Test Suite (6-8 hours)

**Status:** ❌ CRITICAL - Zero test coverage

**Create test files:**
```
tests/
├── Unit/
│   ├── Models/
│   │   ├── PeminjamanAlatTest.php
│   │   ├── UserTest.php
│   │   └── AlatTest.php
│   └── Services/
│       └── FIFOServiceTest.php
└── Feature/
    ├── PeminjamanControllerTest.php
    ├── AlatControllerTest.php
    └── AuthTest.php
```

**What to test:**

**Unit Tests (models):**
- PeminjamanAlat validation (both IDs required)
- User role assignment & scopes
- Alat availability calculation
- Soft delete functionality

**Feature Tests (workflows):**
- Create borrowing (valid & invalid cases)
- Return equipment
- Edit active borrowing
- Unauthorized access denied
- Role-based access control

**Checklist:**
- [ ] Create PeminjamanAlatTest with 5-8 test cases
- [ ] Create PeminjamanControllerTest with workflow tests
- [ ] Create AuthTest for authorization
- [ ] Run: php artisan test
- [ ] Verify all tests pass
- [ ] Check coverage: php artisan test --coverage

---

## 🟡 PHASE 2: HIGH PRIORITY (8-10 hours)

### [ ] 4. Complete Profile Validation (1 hour)

**Files to update:**
- `app/Http/Requests/ProfileUpdateRequest.php`
- `app/Models/User.php`
- Migration: Add columns to users table (optional)

**What to add:**
- Phone number validation
- ID number (NIM/NIP) validation
- Photo/avatar upload
- Department field

**Checklist:**
- [ ] Update ProfileUpdateRequest rules
- [ ] Add user model casts/accessors if needed
- [ ] Update profile view form
- [ ] Test validation works
- [ ] Test form submission

---

### [ ] 5. Build API Endpoints (4-6 hours)

**Create new controller:**
- `app/Http/Controllers/Api/PeminjamanApiController.php`
- `app/Http/Controllers/Api/AlatApiController.php`

**Routes to add** (in `routes/api.php`):
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('peminjaman', PeminjamanApiController::class);
    Route::apiResource('alat', AlatApiController::class);
});
```

**What to return:**
- JSON responses with proper status codes
- Pagination for list endpoints
- Error messages & validation errors
- Timestamps in ISO format

**Checklist:**
- [ ] Create ApiResource classes for JSON formatting
- [ ] Implement all CRUD endpoints
- [ ] Add JSON validation responses
- [ ] Test with Postman/Insomnia
- [ ] Document endpoints (in YAML or similar)

---

### [ ] 6. Implement Audit Logging (3-4 hours)

**Approach:**
- Use Laravel's `DB::listen()` or create custom observer
- Track model changes in `activity_log` table
- Log who made changes, when, and what changed

**Files to create:**
- `database/migrations/YYYY_MM_DD_create_activity_log_table.php`
- `app/Models/ActivityLog.php`
- `app/Traits/LogsActivity.php` (optional)

**What to log:**
- User login/logout
- Create borrowing
- Return equipment
- Edit records
- Delete records

**Checklist:**
- [ ] Create activity_log migration
- [ ] Set up model observer
- [ ] Add LogsActivity trait to models
- [ ] Test that actions are logged
- [ ] Create audit view (optional)

---

## 🟢 PHASE 3: NICE TO HAVE (6-8 hours)

### [ ] 7. Dashboard Enhancements

- [ ] Add charts (borrowing trends, equipment utilization)
- [ ] Add statistics cards (total items, active loans, etc.)
- [ ] Add recent activity feed
- [ ] Add role-specific dashboards

---

### [ ] 8. Search & Filtering

- [ ] Add search box to index views
- [ ] Add filter by status, date range, user, etc.
- [ ] Implement query scopes for common filters

---

### [ ] 9. Export & Reports

- [ ] PDF export of borrowing history
- [ ] CSV export of equipment list
- [ ] Generate reports by date range

---

## 📋 VERIFICATION CHECKLIST

Before considering project "done", verify:

- [ ] All views render without errors
- [ ] All forms submit and validate
- [ ] Authorization working (unauthorized → 403)
- [ ] Tests run: `php artisan test`
- [ ] No PHP errors: `php artisan tinker` works
- [ ] Database queries optimized (check with 25+ indexes)
- [ ] Cache cleared: `php artisan cache:clear`
- [ ] Migrations reversible: `php artisan migrate:refresh`

---

## 🚀 DEPLOYMENT CHECKLIST

Before pushing to production:

- [ ] Run full test suite: `php artisan test`
- [ ] Check code style: `php artisan pint` (or `composer fix`)
- [ ] Verify no console errors: Browser DevTools
- [ ] Test on staging environment
- [ ] Backup database
- [ ] Check `.env` production values
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Set APP_DEBUG=false in .env
- [ ] Monitor logs after deploy

---

## 💻 QUICK COMMANDS REFERENCE

```bash
# Development
npm run dev              # Start Vite dev server
php artisan serve       # Start Laravel server
php artisan tinker      # Interactive PHP shell
php artisan migrate     # Run migrations

# Testing
php artisan test                    # Run all tests
php artisan test --filter=Peminjaman  # Run specific tests
php artisan test --coverage         # Check coverage

# Code Quality
php artisan pint                    # Fix code style
php artisan lint:fix                # Fix PHP errors (if available)

# Database
php artisan migrate:refresh         # Reset DB (dev only!)
php artisan db:seed                # Run seeders
php artisan make:migration name     # Create migration

# Cache
php artisan cache:clear            # Clear all cache
php artisan view:clear             # Clear view cache
php artisan config:cache           # Cache config

# Utilities
php artisan make:controller Name    # Create controller
php artisan make:model Name         # Create model
php artisan make:test Name          # Create test
```

---

## 📊 PROGRESS TRACKING

Track your progress:

```
Phase 1 (Blocking): ___ / 3 tasks (12-14h)
  - [ ] Peminjaman views (2-3h)
  - [ ] Authorization (2-3h)
  - [ ] Tests (6-8h)

Phase 2 (High Priority): ___ / 3 tasks (8-10h)
  - [ ] Profile completion (1h)
  - [ ] API endpoints (4-6h)
  - [ ] Audit logging (3-4h)

Phase 3 (Nice to have): ___ / 3 tasks (6-8h)
  - [ ] Dashboard polish
  - [ ] Search & filtering
  - [ ] Export & reports

Status:
❌ Not started
🟡 In progress
✅ Complete
```

---

## 🎓 TIPS & BEST PRACTICES

1. **Start with Phase 1** - Unblocks development
2. **Write tests as you build** - Not all at once
3. **Use existing patterns** - Copy from working controllers
4. **Test incrementally** - After each file
5. **Commit regularly** - Small commits are easier to review
6. **Ask questions** - If unsure about implementation

---

## 📞 QUICK REFERENCE

**Get stuck?** Check these:
- Laravel docs: https://laravel.com/docs
- Eloquent ORM: https://laravel.com/docs/eloquent
- Blade templates: https://laravel.com/docs/blade
- Testing: https://laravel.com/docs/testing

**In project:**
- `COMPREHENSIVE_ANALYSIS.md` - Full audit
- `INDEX.md` - Documentation index
- `COMPLETION_REPORT.md` - What's done

---

**Last Updated:** 2026-08-09 14:14 UTC  
**Status:** Ready for Phase 1 work  
**Recommendation:** Focus on blocking issues first (Phase 1)
