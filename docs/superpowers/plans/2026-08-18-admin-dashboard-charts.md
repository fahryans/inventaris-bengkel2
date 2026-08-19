# Admin Dashboard Charts Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Chart.js visualizations (bar chart for alat per lab, line chart for pengadaan per bulan) to the admin dashboard.

**Architecture:** Add Chart.js canvas elements and scripts to the existing admin dashboard view, and add corresponding data queries to DashboardController.

**Tech Stack:** Laravel, Blade templating, Chart.js CDN, Eloquent ORM

## Global Constraints
- Follow existing code style and patterns
- Use Chart.js CDN already used in kadep dashboard
- Ensure charts are responsive
- Use `@json` directive for passing data to JavaScript

---

### Task 1: Add Chart Data to DashboardController

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php:33-71`

**Interfaces:**
- Consumes: `Laboratorium` model with `alat` relationship, `PengadaanAlat` model
- Produces: `$labNames`, `$alatCounts`, `$pengadaanPerBulan` variables for admin view

- [ ] **Step 1: Add necessary import for PengadaanAlat**

```php
use App\Models\PengadaanAlat;
```

- [ ] **Step 2: Add chart data queries in adminDashboard method**

After existing queries and before `return view(...)`, add:

```php
$labNames = Laboratorium::pluck('nama_labor');
$alatCounts = Laboratorium::withCount('alat')->pluck('alat_count');
$pengadaanPerBulan = PengadaanAlat::whereYear('created_at', now()->year)
    ->selectRaw('MONTH(created_at) as month, count(*) as total')
    ->groupBy('month')
    ->pluck('total', 'month')
    ->toArray();
// Fill missing months with 0
$pengadaanPerBulan = collect(range(1, 12))->map(fn($m) => $pengadaanPerBulan[$m] ?? 0)->toArray();
```

- [ ] **Step 3: Pass new variables to view**

Update `compact()` to include `$labNames`, `$alatCounts`, `$pengadaanPerBulan`.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/DashboardController.php
git commit -m "feat: add chart data queries to admin dashboard controller"
```

### Task 2: Add Chart Elements to Admin Dashboard View

**Files:**
- Modify: `resources/views/dashboard/admin.blade.php:270-293`

**Interfaces:**
- Consumes: `$labNames`, `$alatCounts`, `$pengadaanPerBulan` from controller
- Produces: Two Chart.js canvas elements with scripts

- [ ] **Step 1: Add Chart.js canvas elements before @endsection**

Insert after the "Informasi Sistem" section (after line 269) and before `@push('js')`:

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
```

- [ ] **Step 2: Add Chart.js scripts inside @push('js')**

Inside the existing `@push('js')` block, after the existing script, add:

```html
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

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard/admin.blade.php
git commit -m "feat: add charts to admin dashboard view"
```

### Task 3: Verification and Final Commit

**Files:**
- None (verification only)

**Interfaces:**
- Consumes: Previous tasks completed
- Produces: Verified working charts

- [ ] **Step 1: Verify view cache**

```bash
php artisan view:cache
```

Expected: No errors

- [ ] **Step 2: Verify routes**

```bash
php artisan route:list --path=dashboard
```

Expected: Dashboard route exists

- [ ] **Step 3: Combine commits if needed**

If separate commits were made, ensure they are logical. No squashing needed unless requested.

- [ ] **Step 4: Final verification**

Check that both files have been modified correctly and contain the expected code.

---

## Self-Review Checklist

1. **Spec coverage:** Both bar chart (alat per lab) and line chart (pengadaan per bulan) are implemented.
2. **Placeholder scan:** No TBD/TODO placeholders. All code is complete.
3. **Type consistency:** Variables `$labNames`, `$alatCounts`, `$pengadaanPerBulan` match between controller and view.
4. **Existing patterns:** Follows similar pattern used in kadep dashboard (Chart.js CDN, canvas elements, `@push('js')`).
5. **Responsive design:** Charts use `responsive: true` and appropriate height.
6. **Data handling:** Missing months filled with 0 values as per brief.