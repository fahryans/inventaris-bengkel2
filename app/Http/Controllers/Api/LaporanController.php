<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaporanRequest;
use App\Http\Resources\LaporanResource;
use App\Models\PeminjamanAlat;
use App\Models\PengadaanAlat;
use App\Models\PemeliharaanAlat;
use App\Models\PemakaianBahan;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $peminjaman = PeminjamanAlat::with(['alat', 'userPeminjam']);
        $pengadaan = PengadaanAlat::with(['alat', 'userInput']);
        $pemeliharaan = PemeliharaanAlat::with(['unitAlat', 'teknisi']);
        $pemakaian = PemakaianBahan::with(['bahan', 'userPemakai', 'userVerifikasi']);

        if ($request->has('type')) {
            $type = $request->type;
            if ($type === 'peminjaman') {
                $peminjaman = $peminjaman->when($request->has('tanggal_mulai') && $request->has('tanggal_akhir'), fn($q) =>
                    $q->whereBetween('waktu_peminjaman', [$request->tanggal_mulai, $request->tanggal_akhir])
                );
            } elseif ($type === 'pengadaan') {
                $pengadaan = $pengadaan->when($request->has('tanggal_mulai') && $request->has('tanggal_akhir'), fn($q) =>
                    $q->whereBetween('tanggal_pengadaan', [$request->tanggal_mulai, $request->tanggal_akhir])
                );
            } elseif ($type === 'pemeliharaan') {
                $pemeliharaan = $pemeliharaan->when($request->has('tanggal_mulai') && $request->has('tanggal_akhir'), fn($q) =>
                    $q->whereBetween('tanggal_cek', [$request->tanggal_mulai, $request->tanggal_akhir])
                );
            } elseif ($type === 'pemakaian') {
                $pemakaian = $pemakaian->when($request->has('tanggal_mulai') && $request->has('tanggal_akhir'), fn($q) =>
                    $q->whereBetween('waktu_pemakaian', [$request->tanggal_mulai, $request->tanggal_akhir])
                );
            }
        }

        return response()->json([
            'peminjaman' => $peminjaman->latest()->paginate(15),
            'pengadaan' => $pengadaan->latest()->paginate(15),
            'pemeliharaan' => $pemeliharaan->latest()->paginate(15),
            'pemakaian' => $pemakaian->latest()->paginate(15),
        ]);
    }

    public function laporanPeminjaman(Request $request)
    {
        $query = PeminjamanAlat::with(['alat', 'userPeminjam']);

        if ($request->has('tanggal_mulai') && $request->has('tanggal_akhir')) {
            $query->whereBetween('waktu_peminjaman', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return new LaporanResource($query->latest()->paginate(15));
    }

    public function laporanPengadaan(Request $request)
    {
        $query = PengadaanAlat::with(['alat', 'userInput']);

        if ($request->has('tanggal_mulai') && $request->has('tanggal_akhir')) {
            $query->whereBetween('tanggal_pengadaan', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        return new LaporanResource($query->latest()->paginate(15));
    }

    public function laporanPemeliharaan(Request $request)
    {
        $query = PemeliharaanAlat::with(['unitAlat', 'teknisi']);

        if ($request->has('tanggal_mulai') && $request->has('tanggal_akhir')) {
            $query->whereBetween('tanggal_cek', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return new LaporanResource($query->latest()->paginate(15));
    }

    public function laporanPemakaian(Request $request)
    {
        $query = PemakaianBahan::with(['bahan', 'userPemakai', 'userVerifikasi']);

        if ($request->has('tanggal_mulai') && $request->has('tanggal_akhir')) {
            $query->whereBetween('waktu_pemakaian', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        return new LaporanResource($query->latest()->paginate(15));
    }
}