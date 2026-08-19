<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\Laboratorium;
use App\Models\PeminjamanAlat;
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
        ));
    }

    private function kepalaLaborDashboard()
    {
        $lab = Laboratorium::where('id_user_kalab', Auth::id())->first();

        if (!$lab) {
            return view('dashboard.no-lab', ['message' => 'Anda belum ditugaskan sebagai kepala laboratorium']);
        }

        $totalAlat = Alat::where('id_labor', $lab->id)->count();
        $totalBahan = Bahan::where('id_labor', $lab->id)->count();
        $lowStockBahan = Bahan::where('id_labor', $lab->id)->lowStock()->count();
        $upcomingMaintenance = PemeliharaanAlat::whereHas('unitAlat.alat', function ($q) use ($lab) {
            $q->where('id_labor', $lab->id);
        })->whereNull('tanggal_cek')
            ->where('tanggal_cek_berikutnya', '<=', now()->addDays(7))
            ->count();

        $activePeminjaman = PeminjamanAlat::where('status', 'terpinjam')
            ->where(function ($q) use ($lab) {
                $q->whereHas('alat', fn($a) => $a->where('id_labor', $lab->id))
                  ->orWhereHas('unitAlat.alat', fn($a) => $a->where('id_labor', $lab->id));
            })
            ->with(['userPeminjam', 'alat', 'unitAlat'])
            ->latest()
            ->limit(5)
            ->get();

        $peminjamanPerBulan = PeminjamanAlat::where(function ($q) use ($lab) {
            $q->whereHas('alat', fn($a) => $a->where('id_labor', $lab->id))
              ->orWhereHas('unitAlat.alat', fn($a) => $a->where('id_labor', $lab->id));
        })
            ->whereYear('created_at', now()->year)
            ->selectRaw($this->monthExpression() . ', count(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();
        $peminjamanPerBulan = collect(range(1, 12))->map(fn($m) => $peminjamanPerBulan[$m] ?? 0)->toArray();

        $bahans = Bahan::where('id_labor', $lab->id)->orderBy('nama_bahan')->get(['nama_bahan', 'stok_saat_ini']);
        $bahanNames = $bahans->pluck('nama_bahan');
        $stokBahan = $bahans->pluck('stok_saat_ini');

        return view('dashboard.kepala-labor', compact(
            'lab',
            'totalAlat',
            'totalBahan',
            'lowStockBahan',
            'upcomingMaintenance',
            'activePeminjaman',
            'peminjamanPerBulan',
            'bahanNames',
            'stokBahan'
        ));
    }

    private function teknisiDashboard()
    {
        $this->authorize('viewAny', PemeliharaanAlat::class);

        $maintenanceSchedule = PemeliharaanAlat::where('id_teknisi', Auth::id())
            ->where('tanggal_cek_berikutnya', '<=', now()->addDays(14))
            ->with('unitAlat.alat')
            ->orderBy('tanggal_cek_berikutnya')
            ->limit(10)
            ->get();

        $overdueCount = PemeliharaanAlat::where('id_teknisi', Auth::id())
            ->where('tanggal_cek_berikutnya', '<', now())
            ->count();

        $completedThisMonth = PemeliharaanAlat::where('id_teknisi', Auth::id())
            ->whereBetween('tanggal_cek', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $pemeliharaanPerBulan = PemeliharaanAlat::where('id_teknisi', Auth::id())
            ->whereYear('created_at', now()->year)
            ->selectRaw($this->monthExpression() . ', count(*) as total')
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

        return view('dashboard.teknisi', compact(
            'maintenanceSchedule',
            'overdueCount',
            'completedThisMonth',
            'pemeliharaanPerBulan',
            'statusLabels',
            'statusCounts'
        ));
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

        return view('dashboard.kadep', compact(
            'totalAlat',
            'totalBahan',
            'totalLaboratorium',
            'totalPeminjaman',
            'lowStockBahan',
            'peminjamPerBulan'
        ));
    }

    private function userDashboard()
    {
        $this->authorize('viewAny', PeminjamanAlat::class);

        $labs = Laboratorium::withCount(['alat', 'bahan'])->get();

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

        return view('dashboard.user', compact(
            'labs',
            'myPeminjaman',
            'activeCount',
            'riwayatCount',
            'riwayatPeminjaman'
        ));
    }
}
