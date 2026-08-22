<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\Laboratorium;
use App\Models\PeminjamanAlat;
use App\Models\PemakaianBahan;
use App\Models\PemeliharaanAlat;
use App\Models\PengadaanAlat;
use App\Models\PengadaanBahan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class LaporanController extends Controller
{
    private function getPeminjamanData(bool $isMahasiswa, ?string $filter)
    {
        $query = PeminjamanAlat::with(['alat', 'unitAlat', 'userPeminjam']);

        if ($isMahasiswa) {
            $query->where('id_user_peminjam', Auth::id());
        }

        if ($filter === 'terlambat') {
            $query->where('status', 'terpinjam')
                  ->where('waktu_pengembalian', '<', now());
        }

        return $query->latest()->paginate(20);
    }

    public function index()
    {
        $this->authorize('viewAny', \App\Models\PeminjamanAlat::class);

        $user = Auth::user();
        $isDosen = $user->role === 'dosen';
        $isMahasiswa = $user->role === 'mahasiswa';

        $summary = [
            'total_alat' => Alat::count(),
            'total_bahan' => Bahan::count(),
            'peminjaman_aktif' => $isMahasiswa
                ? PeminjamanAlat::where('id_user_peminjam', Auth::id())->where('status', 'terpinjam')->count()
                : PeminjamanAlat::where('status', 'terpinjam')->count(),
            'peminjaman_terlambat' => $isMahasiswa
                ? PeminjamanAlat::where('id_user_peminjam', Auth::id())->where('status', 'terpinjam')
                    ->where('waktu_pengembalian', '<', now())->count()
                : PeminjamanAlat::where('status', 'terpinjam')
                    ->where('waktu_pengembalian', '<', now())->count(),
        ];

        if (!$isMahasiswa) {
            $summary += [
                'pemeliharaan_upcoming' => PemeliharaanAlat::where('tanggal_cek_berikutnya', '>=', now())
                    ->where('tanggal_cek_berikutnya', '<=', now()->addDays(7))->count(),
                'pemeliharaan_overdue' => PemeliharaanAlat::where('tanggal_cek_berikutnya', '<', now())->count(),
                'pengadaan_alat_pending' => PengadaanAlat::whereNull('tanggal_masuk')->count(),
                'pengadaan_bahan_pending' => PengadaanBahan::whereNull('tanggal_masuk')->count(),
                'bahan_low_stock' => Bahan::whereRaw('
                    (SELECT COALESCE(SUM(stok_tersisa_batch), 0) FROM pengadaan_bahan WHERE pengadaan_bahan.id_bahan = bahan.id) <= stok_minimum
                ')->whereNull('deleted_at')->count(),
            ];
        }

        return view('laporan.index', compact('summary', 'user'));
    }

    public function show($tipe, Request $request)
    {
        $this->authorize('viewAny', \App\Models\PeminjamanAlat::class);

        $user = Auth::user();
        $isMahasiswa = $user->role === 'mahasiswa';

        if ($isMahasiswa && !in_array($tipe, ['alat', 'bahan', 'peminjaman'])) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        $filter = $request->query('filter');

        $data = match($tipe) {
            'alat' => Alat::with(['kategori', 'laboratorium'])->latest()->paginate(20),
            'bahan' => Bahan::with('kategori')->latest()->paginate(20),
            'peminjaman' => $this->getPeminjamanData($isMahasiswa, $filter),
            'pemeliharaan' => PemeliharaanAlat::with(['unitAlat', 'teknisi'])->latest()->paginate(20),
            'pengadaan_alat' => PengadaanAlat::with(['alat', 'userInput'])->latest()->paginate(20),
            'pengadaan_bahan' => PengadaanBahan::with(['bahan', 'userInput'])->latest()->paginate(20),
            'pemakaian_bahan' => PemakaianBahan::with(['bahan', 'userPemakai', 'userVerifikasi'])->latest()->paginate(20),
            default => abort(404),
        };

        $title = match($tipe) {
            'alat' => 'Laporan Data Alat',
            'bahan' => 'Laporan Data Bahan',
            'peminjaman' => $filter === 'terlambat' ? 'Laporan Peminjaman Terlambat' : 'Laporan Peminjaman Aktif Alat dan Bahan',
            'pemeliharaan' => 'Laporan Pemeliharaan Alat',
            'pengadaan_alat' => 'Laporan Pengadaan Alat',
            'pengadaan_bahan' => 'Laporan Pengadaan Bahan',
            'pemakaian_bahan' => 'Laporan Pemakaian Bahan',
            default => 'Laporan',
        };

        return view('laporan.show', compact('data', 'tipe', 'title'));
    }

    public function export($tipe, Request $request)
    {
        $this->authorize('viewAny', \App\Models\PeminjamanAlat::class);

        $user = Auth::user();
        $isMahasiswa = $user->role === 'mahasiswa';

        if ($isMahasiswa && !in_array($tipe, ['alat', 'bahan', 'peminjaman'])) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        $data = match($tipe) {
            'alat' => Alat::with(['kategori', 'laboratorium'])->latest()->get(),
            'bahan' => Bahan::with('kategori')->latest()->get(),
            'peminjaman' => $isMahasiswa
                ? PeminjamanAlat::where('id_user_peminjam', Auth::id())->with(['alat', 'unitAlat', 'userPeminjam'])->latest()->get()
                : PeminjamanAlat::with(['alat', 'unitAlat', 'userPeminjam'])->latest()->get(),
            'pemeliharaan' => PemeliharaanAlat::with(['unitAlat', 'teknisi'])->latest()->get(),
            'pengadaan_alat' => PengadaanAlat::with(['alat', 'userInput'])->latest()->get(),
            'pengadaan_bahan' => PengadaanBahan::with(['bahan', 'userInput'])->latest()->get(),
            'pemakaian_bahan' => PemakaianBahan::with(['bahan', 'userPemakai', 'userVerifikasi'])->latest()->get(),
            'peminjaman_saya' => PeminjamanAlat::where('id_user_peminjam', Auth::id())
                ->with(['alat', 'unitAlat', 'userPeminjam'])->latest()->get(),
            'pemakaian_saya' => PemakaianBahan::where('id_user_pemakai', Auth::id())
                ->with(['bahan', 'userPemakai', 'userVerifikasi'])->latest()->get(),
            default => null,
        };

        if (!$data) {
            return redirect()->route('laporan.show', $tipe)
                ->with('error', 'Tipe laporan tidak valid');
        }

        $title = match($tipe) {
            'alat' => 'Laporan Data Alat',
            'bahan' => 'Laporan Data Bahan',
            'peminjaman' => 'Laporan Peminjaman Aktif Alat dan Bahan',
            'pemeliharaan' => 'Laporan Pemeliharaan Alat',
            'pengadaan_alat' => 'Laporan Pengadaan Alat',
            'pengadaan_bahan' => 'Laporan Pengadaan Bahan',
            'pemakaian_bahan' => 'Laporan Pemakaian Bahan',
            'peminjaman_saya' => 'Riwayat Peminjaman Saya',
            'pemakaian_saya' => 'Riwayat Pemakaian Saya',
            default => 'Laporan',
        };

        $template = match($tipe) {
            'peminjaman_saya' => 'laporan.pdf.peminjaman',
            'pemakaian_saya' => 'laporan.pdf.pemakaian_bahan',
            default => "laporan.pdf.{$tipe}",
        };

        $pdf = Pdf::loadView($template, [
            'data' => $data,
            'title' => $title,
            'date' => now()->format('d/m/Y'),
        ]);

        $filename = "laporan_{$tipe}_" . now()->format('d-m-Y') . '.pdf';

        return $pdf->download($filename);
    }

    public function myReport(Request $request)
    {
        $user = Auth::user();
        $labs = Laboratorium::all();

        $peminjamanQuery = PeminjamanAlat::where('id_user_peminjam', Auth::id())
            ->with(['alat.laboratorium', 'unitAlat.alat.laboratorium']);

        $pemakaianQuery = PemakaianBahan::where('id_user_pemakai', Auth::id())
            ->with(['bahan.laboratorium', 'pengadaanBahan']);

        if ($request->filled('lab')) {
            $labId = $request->lab;

            $peminjamanQuery->where(function ($q) use ($labId) {
                $q->whereHas('alat', function ($q2) use ($labId) {
                    $q2->where('id_labor', $labId);
                })->orWhereHas('unitAlat.alat', function ($q2) use ($labId) {
                    $q2->where('id_labor', $labId);
                });
            });

            $pemakaianQuery->whereHas('bahan', function ($q) use ($labId) {
                $q->where('id_labor', $labId);
            });
        }

        $peminjaman = $peminjamanQuery->latest('waktu_peminjaman')->paginate(15)->withQueryString();
        $pemakaian = $pemakaianQuery->latest('waktu_pemakaian')->paginate(15)->withQueryString();

        return view('laporan.my-report', compact('peminjaman', 'pemakaian', 'labs'));
    }

    /**
     * Breakdown Alat per Merek & Supplier
     */
    public function breakdownMerekAlat()
    {
        $this->authorize('viewAny', \App\Models\PeminjamanAlat::class);

        $pengadaanAlat = PengadaanAlat::with(['alat'])
            ->latest('tanggal_pengadaan')
            ->get();

        return view('laporan.breakdown_merek_alat', compact('pengadaanAlat'));
    }

    /**
     * Breakdown Bahan per Merek & Supplier
     */
    public function breakdownMerekBahan()
    {
        $this->authorize('viewAny', \App\Models\PeminjamanAlat::class);

        $pengadaanBahan = PengadaanBahan::with(['bahan'])
            ->latest('tanggal_pengadaan')
            ->get();

        return view('laporan.breakdown_merek_bahan', compact('pengadaanBahan'));
    }
}
