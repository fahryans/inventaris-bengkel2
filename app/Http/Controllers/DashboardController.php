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

class DashboardController extends Controller
{
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
            ->selectRaw('MONTH(created_at) as month, count(*) as total')
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
            return view('dashboard.index', ['message' => 'Anda belum ditugaskan sebagai kepala laboratorium']);
        }

        $totalAlat = Alat::where('id_labor', $lab->id)->count();
        $totalBahan = Bahan::where('id_labor', $lab->id)->count();
        $lowStockBahan = Bahan::where('id_labor', $lab->id)->lowStock()->count();
        $upcomingMaintenance = PemeliharaanAlat::whereHas('unitAlat.alat', function ($q) use ($lab) {
            $q->where('id_labor', $lab->id);
        })->where('tanggal_cek_berikutnya', '<=', now()->addDays(7))->count();

        $activePeminjaman = PeminjamanAlat::where('status', 'terpinjam')
            ->with(['userPeminjam', 'alat', 'unitAlat'])
            ->latest()
            ->limit(5)
            ->get();

        $peminjamanPerBulan = PeminjamanAlat::whereHas('alat', fn($q) => $q->where('id_labor', $lab->id))
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, count(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();
        $peminjamanPerBulan = collect(range(1, 12))->map(fn($m) => $peminjamanPerBulan[$m] ?? 0)->toArray();

        $bahanNames = Bahan::where('id_labor', $lab->id)->pluck('nama_bahan');
        $stokBahan = Bahan::where('id_labor', $lab->id)->pluck('stok_saat_ini');

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

        return view('dashboard.teknisi', compact(
            'maintenanceSchedule',
            'overdueCount',
            'completedThisMonth'
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

        $peminjamPerBulan = PeminjamanAlat::selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
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

        $myPeminjaman = PeminjamanAlat::where('id_user_peminjam', Auth::id())
            ->with(['alat', 'unitAlat'])
            ->latest()
            ->limit(5)
            ->get();

        $activePeminjaman = $myPeminjaman->where('status', 'terpinjam');

        return view('dashboard.user', compact(
            'labs',
            'myPeminjaman',
            'activePeminjaman'
        ));
    }
}
