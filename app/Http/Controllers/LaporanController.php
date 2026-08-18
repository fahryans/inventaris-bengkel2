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
use Barryvdh\DomPDF\Facades\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
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
                'bahan_low_stock' => Bahan::whereRaw('stok_saat_ini <= stok_minimum')->count(),
            ];
        }

        return view('laporan.index', compact('summary', 'user'));
    }

    public function show($tipe)
    {
        $this->authorize('viewAny', \App\Models\PeminjamanAlat::class);

        $user = Auth::user();
        $isMahasiswa = $user->role === 'mahasiswa';

        if ($isMahasiswa && !in_array($tipe, ['alat', 'bahan', 'peminjaman'])) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        $data = match($tipe) {
            'alat' => Alat::with(['kategori', 'laboratorium'])->latest()->paginate(20),
            'bahan' => Bahan::with('kategori')->latest()->paginate(20),
            'peminjaman' => $isMahasiswa
                ? PeminjamanAlat::where('id_user_peminjam', Auth::id())->with(['alat', 'unitAlat', 'userPeminjam'])->latest()->paginate(20)
                : PeminjamanAlat::with(['alat', 'unitAlat', 'userPeminjam'])->latest()->paginate(20),
            'pemeliharaan' => PemeliharaanAlat::with(['unitAlat', 'teknisi'])->latest()->paginate(20),
            'pengadaan_alat' => PengadaanAlat::with(['alat', 'userInput'])->latest()->paginate(20),
            'pengadaan_bahan' => PengadaanBahan::with(['bahan', 'userInput'])->latest()->paginate(20),
            'pemakaian_bahan' => PemakaianBahan::with(['bahan', 'userPemakai', 'userVerifikasi'])->latest()->paginate(20),
            default => abort(404),
        };

        $title = match($tipe) {
            'alat' => 'Laporan Data Alat',
            'bahan' => 'Laporan Data Bahan',
            'peminjaman' => 'Laporan Peminjaman Alat',
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
            default => 'Laporan',
        };

        $pdf = Pdf::loadView("laporan.pdf.{$tipe}", [
            'data' => $data,
            'title' => $title,
            'date' => now()->format('d/m/Y'),
        ]);

        $filename = "laporan_{$tipe}_" . now()->format('d/m/Y') . '.pdf';

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
}
