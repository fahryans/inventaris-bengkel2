<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\PeminjamanAlat;
use App\Models\PemakaianBahan;
use App\Models\PemeliharaanAlat;
use App\Models\PengadaanAlat;
use App\Models\PengadaanBahan;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Alat::class);

        $summary = [
            'total_alat' => Alat::count(),
            'total_bahan' => Bahan::count(),
            'peminjaman_aktif' => PeminjamanAlat::where('status', 'terpinjam')->count(),
            'peminjaman_terlambat' => PeminjamanAlat::where('status', 'terpinjam')
                ->where('waktu_pengembalian', '<', now())->count(),
            'pemeliharaan_upcoming' => PemeliharaanAlat::where('tanggal_cek_berikutnya', '>=', now())
                ->where('tanggal_cek_berikutnya', '<=', now()->addDays(7))->count(),
            'pemeliharaan_overdue' => PemeliharaanAlat::where('tanggal_cek_berikutnya', '<', now())->count(),
            'pengadaan_alat_pending' => PengadaanAlat::whereNull('tanggal_masuk')->count(),
            'pengadaan_bahan_pending' => PengadaanBahan::whereNull('tanggal_masuk')->count(),
            'bahan_low_stock' => Bahan::whereRaw('stok_saat_ini <= stok_minimum')->count(),
        ];

        return view('laporan.index', compact('summary'));
    }

    public function show($tipe)
    {
        $this->authorize('viewAny', Alat::class);

        $data = match($tipe) {
            'alat' => Alat::with(['kategori', 'laboratorium'])->latest()->paginate(20),
            'bahan' => Bahan::with('kategori')->latest()->paginate(20),
            'peminjaman' => PeminjamanAlat::with(['alat', 'unitAlat', 'userPeminjam'])->latest()->paginate(20),
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
        $this->authorize('viewAny', Alat::class);

        return redirect()->route('laporan.show', $tipe)
            ->with('info', 'Export sedang dalam pengembangan');
    }
}
