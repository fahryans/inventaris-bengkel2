<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\Laboratorium;
use App\Models\PeminjamanAlat;
use App\Models\PemakaianBahan;
use App\Models\PemeliharaanAlat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function monthExpression(): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER) as month"
            : 'MONTH(created_at) as month';
    }

    /**
     * Data section "card labor + tab peminjaman/pemakaian" ala dashboard mahasiswa.
     * Teknisi/kalab: hanya lab miliknya. Admin/kadep: semua lab.
     */
    private function pinjamPakaiSection(): array
    {
        $user = Auth::user();

        if ($user->role === 'teknisi') {
            $labIds = $user->laboratoriumTeknisi->pluck('id')->toArray();
        } elseif ($user->role === 'kepala_labor') {
            $labIds = $user->laboratoriumDikelola->pluck('id')->toArray();
        } else {
            $labIds = null; // admin_jurusan & kadep: semua lab
        }

        // Kartu lab selalu menampilkan SEMUA lab, agar pengguna bisa meminjam
        // alat/memakai bahan dari lab lain melalui kartu tersebut (semua role).
        $pkLabs = Laboratorium::withCount(['alat', 'bahan'])->get();

        $peminjamanScope = function ($q) use ($labIds) {
            if ($labIds) {
                $q->where(function ($inner) use ($labIds) {
                    $inner->whereHas('alat', fn($a) => $a->whereIn('id_labor', $labIds))
                        ->orWhereHas('unitAlat.alat', fn($a) => $a->whereIn('id_labor', $labIds));
                });
            }
        };

        $pkActiveCount = PeminjamanAlat::where('status', 'terpinjam')
            ->where($peminjamanScope)->count();

        $pkRiwayatCount = PeminjamanAlat::where($peminjamanScope)->count();

        $pkPeminjaman = PeminjamanAlat::where('status', 'terpinjam')
            ->where($peminjamanScope)
            ->with(['alat', 'unitAlat', 'userPeminjam'])
            ->latest()
            ->limit(5)
            ->get();

        $bahanScope = fn($q) => $labIds ? $q->whereIn('id_labor', $labIds) : $q;

        $pkPendingPemakaian = PemakaianBahan::whereNull('id_user_verifikasi')
            ->whereNull('jumlah_pengembalian')
            ->whereHas('bahan', $bahanScope)
            ->with(['bahan', 'pengadaanBahan', 'userPemakai'])
            ->latest()
            ->limit(10)
            ->get();

        $pkPendingReturns = PemakaianBahan::where('status_pengembalian', 'pending')
            ->whereHas('bahan', $bahanScope)
            ->with(['bahan', 'pengadaanBahan', 'userPemakai'])
            ->latest()
            ->limit(10)
            ->get();

        // Data milik user itu sendiri: pinjaman alat & pemakaian bahan.
        $pkMyPeminjaman = PeminjamanAlat::where('id_user_peminjam', Auth::id())
            ->with(['alat', 'unitAlat', 'userPeminjam'])
            ->latest()
            ->limit(10)
            ->get();

        $pkMyPemakaian = PemakaianBahan::where('id_user_pemakai', Auth::id())
            ->with(['bahan', 'pengadaanBahan', 'userVerifikasi'])
            ->latest()
            ->limit(10)
            ->get();

        return compact(
            'pkLabs',
            'pkActiveCount',
            'pkRiwayatCount',
            'pkPeminjaman',
            'pkPendingPemakaian',
            'pkPendingReturns',
            'pkMyPeminjaman',
            'pkMyPemakaian'
        );
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin_jurusan') {
            return $this->adminDashboard();
        } elseif ($user->role === 'kepala_labor') {
            return $this->kepalaLaborDashboard();
        } elseif ($user->role === 'teknisi') {
            return $this->teknisiDashboard();
        } elseif ($user->role === 'kadep') {
            return $this->kadepDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    private function adminDashboard()
    {
        $this->authorize('viewAny', PeminjamanAlat::class);
        $this->authorize('create', PeminjamanAlat::class);

        $totalAlat = Alat::count();
        $totalBahan = Bahan::count();
        $totalLaboratorium = Laboratorium::count();
        $totalUser = User::count();
        $totalPeminjaman = PeminjamanAlat::count();

        $lowStockBahan = Bahan::lowStock()->count();
        $overduePeminjaman = PeminjamanAlat::where('status', 'terpinjam')
            ->where('waktu_pengembalian', '<', now())
            ->count();
        $overdueMaintenance = PemeliharaanAlat::where('tanggal_cek_berikutnya', '<', now())->count();

        $recentPeminjaman = PeminjamanAlat::with(['userPeminjam', 'alat', 'unitAlat'])
            ->latest()
            ->limit(5)
            ->get();

        $alatPerLab = Alat::selectRaw('id_labor, COUNT(*) as total')
            ->groupBy('id_labor')
            ->with('laboratorium')
            ->get();

        $labNames = Laboratorium::pluck('nama_labor');
        $alatCounts = Laboratorium::withCount('alat')->pluck('alat_count');
        $pengadaanPerBulan = \App\Models\PengadaanAlat::whereYear('created_at', now()->year)
            ->selectRaw($this->monthExpression() . ', count(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();
        $pengadaanPerBulan = collect(range(1, 12))->map(fn($m) => $pengadaanPerBulan[$m] ?? 0)->toArray();

        return view('dashboard.admin', compact(
            'totalAlat',
            'totalBahan',
            'totalLaboratorium',
            'totalUser',
            'totalPeminjaman',
            'lowStockBahan',
            'overduePeminjaman',
            'overdueMaintenance',
            'recentPeminjaman',
            'alatPerLab',
            'labNames',
            'alatCounts',
            'pengadaanPerBulan'
        ) + $this->pinjamPakaiSection());
    }

    private function teknisiDashboard()
    {
        $this->authorize('viewAny', PemeliharaanAlat::class);

        $user = Auth::user();
        $labIds = $user->laboratoriumTeknisi->pluck('id')->toArray();

        $data = $this->maintenanceData(Auth::id(), $labIds) + $this->statusUnitData($labIds);

        return view('dashboard.teknisi', $data + $this->pinjamPakaiSection())
            ->with('roleLabel', 'Teknisi')
            ->with('labNames', $user->laboratoriumTeknisi->pluck('nama_labor')->implode(', '));
    }

    private function kepalaLaborDashboard()
    {
        $user = Auth::user();
        $labs = $user->laboratoriumDikelola;

        if ($labs->isEmpty()) {
            return view('dashboard.no-lab', ['message' => 'Anda belum ditugaskan sebagai kepala laboratorium']);
        }

        $labIds = $labs->pluck('id')->toArray();

        $data = $this->maintenanceData(null, $labIds) + $this->statusUnitData($labIds);

        return view('dashboard.teknisi', $data + $this->pinjamPakaiSection())
            ->with('roleLabel', 'Kepala Laboratorium')
            ->with('labNames', $labs->pluck('nama_labor')->implode(', '));
    }

    /**
     * Data jadwal pemeliharaan untuk view dashboard teknisi/kalab.
     * $teknisiId diisi untuk teknisi (hanya jadwal miliknya); null untuk kalab
     * (semua jadwal di lab miliknya).
     */
    private function maintenanceData(?int $teknisiId, array $labIds)
    {
        $base = PemeliharaanAlat::query()
            ->whereHas('unitAlat.alat', fn($q) => $q->whereIn('id_labor', $labIds));
        if ($teknisiId) {
            $base->where('id_teknisi', $teknisiId);
        }

        $maintenanceSchedule = (clone $base)
            ->where('tanggal_cek_berikutnya', '<=', now()->addDays(14))
            ->where(function ($q) {
                $q->whereNull('tanggal_cek')
                    ->orWhere('kondisi', '!=', 'baik')
                    ->orWhere('tanggal_cek_berikutnya', '<', now());
            })
            ->with('unitAlat.alat')
            ->orderBy('tanggal_cek_berikutnya')
            ->limit(10)
            ->get();

        $overdueCount = (clone $base)
            ->where('tanggal_cek_berikutnya', '<', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_cek')
                    ->orWhere('kondisi', '!=', 'baik');
            })
            ->count();

        $completedThisMonth = (clone $base)
            ->whereBetween('tanggal_cek', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('kondisi', 'baik')
            ->count();

        $pemeliharaanPerBulan = (clone $base)
            ->whereYear('created_at', now()->year)
            ->selectRaw($this->monthExpression() . ', count(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();
        $pemeliharaanPerBulan = collect(range(1, 12))->map(fn($m) => $pemeliharaanPerBulan[$m] ?? 0)->toArray();

        return compact('maintenanceSchedule', 'overdueCount', 'completedThisMonth', 'pemeliharaanPerBulan');
    }

    private function statusUnitData(array $labIds): array
    {
        $counts = \App\Models\UnitAlat::whereIn('id_alat', function ($q) use ($labIds) {
            $q->select('id')->from('alat')->whereIn('id_labor', $labIds);
        })
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusLabels = ['Tersedia', 'Dipinjam', 'Rusak', 'Maintenance'];
        $statusCounts = [
            $counts['tersedia'] ?? 0,
            $counts['dipinjam'] ?? 0,
            $counts['rusak'] ?? 0,
            $counts['maintenance'] ?? 0,
        ];

        return compact('statusLabels', 'statusCounts');
    }

    private function kadepDashboard()
    {
        $this->authorize('viewAny', PeminjamanAlat::class);

        $totalAlat = Alat::count();
        $totalBahan = Bahan::count();
        $totalLaboratorium = Laboratorium::count();
        $totalPeminjaman = PeminjamanAlat::count();
        $lowStockBahan = Bahan::lowStock()->count();

        $monthExprBulan = DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER) as bulan"
            : 'MONTH(created_at) as bulan';

        $peminjamPerBulan = PeminjamanAlat::selectRaw($monthExprBulan . ', COUNT(*) as total')
            ->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()])
            ->groupBy('bulan')
            ->get();

        // Pemakaian bahan yang sudah diverifikasi & belum dikembalikan (untuk dikembalikan)
        $myPemakaianBahan = \App\Models\PemakaianBahan::where('id_user_pemakai', Auth::id())
            ->whereNotNull('id_user_verifikasi')
            ->where(function ($q) {
                $q->whereNull('jumlah_pengembalian')
                    ->orWhere('status_pengembalian', 'pending');
            })
            ->with(['bahan', 'pengadaanBahan'])
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.kadep', compact(
            'totalAlat',
            'totalBahan',
            'totalLaboratorium',
            'totalPeminjaman',
            'lowStockBahan',
            'peminjamPerBulan',
            'myPemakaianBahan'
        ) + $this->pinjamPakaiSection());
    }

    private function userDashboard()
    {
        $this->authorize('viewAny', PeminjamanAlat::class);

        $user = Auth::user();
        if ($user->role === 'teknisi') {
            $labs = Laboratorium::whereIn('id', $user->laboratoriumTeknisi->pluck('id'))
                ->withCount(['alat', 'bahan'])->get();
        } elseif ($user->role === 'kepala_labor') {
            $labs = Laboratorium::whereIn('id', $user->laboratoriumDikelola->pluck('id'))
                ->withCount(['alat', 'bahan'])->get();
        } else {
            $labs = Laboratorium::withCount(['alat', 'bahan'])->get();
        }

        $activeCount = PeminjamanAlat::where('id_user_peminjam', Auth::id())
            ->where('status', 'terpinjam')
            ->count();

        $riwayatCount = PeminjamanAlat::where('id_user_peminjam', Auth::id())
            ->count();

        $myPeminjaman = PeminjamanAlat::where('id_user_peminjam', Auth::id())
            ->where('status', 'terpinjam')
            ->with(['alat', 'unitAlat'])
            ->latest()
            ->limit(5)
            ->get();

        $riwayatPeminjaman = PeminjamanAlat::where('id_user_peminjam', Auth::id())
            ->whereYear('created_at', now()->year)
            ->selectRaw($this->monthExpression() . ', count(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();
        $riwayatPeminjaman = collect(range(1, 12))->map(fn($m) => $riwayatPeminjaman[$m] ?? 0)->toArray();

        $isStaff = in_array(Auth::user()->role, ['admin_jurusan', 'kepala_labor', 'teknisi'], true);

        if ($isStaff) {
            $user = Auth::user();
            $labIds = $user->laboratoriumTeknisi->pluck('id')
                ->merge($user->laboratoriumDikelola->pluck('id'))
                ->unique();

            // Kalab/teknisi: pemakaian bahan yang belum diverifikasi (butuh verifikasi pemakaian)
            $pendingPemakaianBahan = \App\Models\PemakaianBahan::whereNull('id_user_verifikasi')
                ->whereNull('jumlah_pengembalian')
                ->whereHas('bahan', fn($q) => $q->whereIn('id_labor', $labIds))
                ->with(['bahan', 'pengadaanBahan', 'userPemakai'])
                ->latest()
                ->limit(10)
                ->get();

            // Kalab/teknisi: pengembalian bahan yang menunggu verifikasi
            $pendingReturns = \App\Models\PemakaianBahan::where('status_pengembalian', 'pending')
                ->whereHas('bahan', fn($q) => $q->whereIn('id_labor', $labIds))
                ->with(['bahan', 'pengadaanBahan', 'userPemakai'])
                ->latest()
                ->limit(10)
                ->get();
        } else {
            $pendingPemakaianBahan = collect();
            $pendingReturns = collect();
        }

        // Pemakaian bahan aktif: belum dikembalikan ATAU status pengembalian pending
        $myPemakaianBahan = \App\Models\PemakaianBahan::where('id_user_pemakai', Auth::id())
            ->whereNotNull('id_user_verifikasi')
            ->where(function ($q) {
                $q->whereNull('jumlah_pengembalian')
                    ->orWhere('status_pengembalian', 'pending');
            })
            ->with(['bahan', 'pengadaanBahan'])
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.user', compact(
            'labs',
            'myPeminjaman',
            'activeCount',
            'riwayatCount',
            'riwayatPeminjaman',
            'myPemakaianBahan',
            'pendingPemakaianBahan',
            'pendingReturns',
            'isStaff'
        ));
    }
}
