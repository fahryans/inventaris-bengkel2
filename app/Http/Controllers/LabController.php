<?php

namespace App\Http\Controllers;

use App\Models\Laboratorium;
use App\Services\FIFOService;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LabController extends Controller
{
    protected PeminjamanService $peminjamanService;
    protected FIFOService $fifoService;

    public function __construct(PeminjamanService $peminjamanService, FIFOService $fifoService)
    {
        $this->peminjamanService = $peminjamanService;
        $this->fifoService = $fifoService;
    }

    public function show(Laboratorium $lab)
    {
        $lab->load(['teknisi', 'kalab']);

        $alat = $lab->alat()
            ->with(['unitAlat' => fn($q) => $q->select('id', 'id_alat', 'status', 'kode_inventaris')])
            ->with('spesifikasiAlat')
            ->withSum('pengadaanAlat', 'jumlah')
            ->withSum(['peminjamanAlat' => fn($q) => $q->active()], 'jumlah')
            ->paginate(10);

        $bahan = $lab->bahan()
            ->with('spesifikasiBahan')
            ->withSum(['pengadaanBahan' => fn($q) => $q->where('stok_tersisa_batch', '>', 0)->whereNotNull('tanggal_masuk')], 'stok_tersisa_batch')
            ->paginate(10);

        // Hitung stok tersedia untuk setiap spesifikasi alat
        foreach ($alat as $item) {
            foreach ($item->spesifikasiAlat as $spec) {
                // Total pengadaan per spesifikasi
                $totalPengadaan = $spec->pengadaanAlat()->sum('jumlah');
                // Total dipinjam per spesifikasi (status terpinjam aktif)
                $totalDipinjam = $spec->peminjamanAlat()->where('status', 'terpinjam')->sum('jumlah');
                $spec->stok_tersedia = max(0, $totalPengadaan - $totalDipinjam);
                $spec->satuan_label = 'unit';
            }
        }

        // Hitung stok tersedia untuk setiap spesifikasi bahan
        foreach ($bahan as $item) {
            foreach ($item->spesifikasiBahan as $spec) {
                $spec->stok_tersedia = $spec->getTotalStok();
            }
        }

        return view('lab.show', compact('lab', 'alat', 'bahan'));
    }

    /**
     * Simpan SOP laboratory sebagai HTML (sanitize dulu dari XSS).
     */
    public function updateSop(Request $request, Laboratorium $lab)
    {
        $this->authorize('update', $lab);

        $request->validate([
            'sop' => ['nullable', 'string'],
        ]);

        $clean = $this->sanitizeHtml($request->input('sop'));

        $lab->update(['sop' => $clean]);

        return back()->with('success', 'SOP laboratorium berhasil diperbarui');
    }

    /**
     * Peminjaman / pemakaian massal alat & bahan dari halaman lab.
     */
    public function borrowMass(Request $request, Laboratorium $lab)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'in:alat,bahan'],
            'items.*.id_alat' => ['nullable', 'integer'],
            'items.*.id_unit_alat' => ['nullable', 'integer'],
            'items.*.id_bahan' => ['nullable', 'integer'],
            'items.*.id_spesifikasi_bahan' => ['nullable', 'integer'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.keperluan' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = Auth::id();
        $errors = [];
        $createdAlat = 0;
        $createdBahan = 0;

        DB::transaction(function () use ($validated, $lab, $userId, &$errors, &$createdAlat, &$createdBahan) {
            foreach ($validated['items'] as $item) {
                $keperluan = $item['keperluan'] ?? 'Peminjaman dari laboratorium';

                if ($item['type'] === 'alat') {
                    try {
                        // Unit: identifier = id_unit_alat; agregat: identifier = id_alat
                        $idAlat = (int)($item['id_alat'] ?? 0);
                        $idUnit = (int)($item['id_unit_alat'] ?? 0);

                        if ($idUnit) {
                            $unit = \App\Models\UnitAlat::with('alat')->find($idUnit);
                            if (!$unit || $unit->alat->id_labor !== $lab->id) {
                                $errors[] = "Unit alat {$idUnit} tidak ditemukan di laboratorium ini.";
                                continue;
                            }
                            $created = $this->peminjamanService->createBorrowing([
                                'id_unit_alat' => $unit->id,
                                'jumlah' => 1,
                                'keperluan' => $keperluan,
                                'id_user_peminjam' => $userId,
                                'id_spesifikasi_alat' => $unit->id_spesifikasi_alat,
                                'waktu_peminjaman' => now(),
                                'waktu_pengembalian' => now()->addHours(4),
                                'kondisi_saat_peminjaman' => 'baik',
                            ]);
                        } else {
                            $alat = $idAlat ? \App\Models\Alat::find($idAlat) : null;
                            if (!$alat || $alat->id_labor !== $lab->id) {
                                $errors[] = "Alat {$idAlat} tidak ditemukan di laboratorium ini.";
                                continue;
                            }
                            $created = $this->peminjamanService->createBorrowing([
                                'id_alat' => $alat->id,
                                'jumlah' => $item['jumlah'],
                                'keperluan' => $keperluan,
                                'id_user_peminjam' => $userId,
                                'waktu_peminjaman' => now(),
                                'waktu_pengembalian' => now()->addHours(4),
                                'kondisi_saat_peminjaman' => 'baik',
                            ]);
                        }
                        $createdAlat++;
                    } catch (\Throwable $e) {
                        $errors[] = ($unit->alat->nama_alat ?? $alat->nama_alat ?? 'Alat') . ": " . $e->getMessage();
                    }

                } else {
                    // Bahan: pemakaian
                    try {
                        $idBahan = (int)($item['id_bahan'] ?? 0);
                        $bahan = $idBahan ? \App\Models\Bahan::find($idBahan) : null;
                        if (!$bahan || $bahan->id_labor !== $lab->id) {
                            $errors[] = "Bahan {$idBahan} tidak ditemukan di laboratorium ini.";
                            continue;
                        }

                        $batches = $this->fifoService->consumeFromBatches(
                            $bahan->id,
                            $item['jumlah'],
                            $item['id_spesifikasi_bahan'] ?? null
                        );

                        \App\Models\PemakaianBahan::create([
                            'id_bahan' => $bahan->id,
                            'id_pengadaan_bahan' => $batches[0]['id_pengadaan_bahan'],
                            'id_laboratorium' => $lab->id,
                            'id_user_pemakai' => $userId,
                            'keperluan' => $keperluan,
                            'jumlah_pengambilan' => $item['jumlah'],
                            'jumlah_terpakai' => $item['jumlah'],
                            'waktu_pemakaian' => now(),
                        ]);
                        $createdBahan++;
                    } catch (\Throwable $e) {
                        $errors[] = ($bahan->nama_bahan ?? 'Bahan') . ": " . $e->getMessage();
                    }
                }
            }
        });

        $msg = "Peminjaman berhasil: {$createdAlat} alat, {$createdBahan} pemakaian bahan.";
        if (!empty($errors)) {
            $msg .= " Sebagian gagal: " . implode(' | ', $errors);
        }

        $ok = $createdAlat + $createdBahan > 0;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $ok,
                'message' => $msg,
            ]);
        }

        return back()->with($ok ? 'success' : 'error', $msg);
    }

    /**
     * Sanitasi HTML: hanya izinkan tag & atribut teks yang aman.
     */
    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return null;
        }

        // 1. Hapus semua tag yang berbahaya
        $disallowed = '~<(script|iframe|object|embed|form|input|style|meta|link|svg|math)\b[^>]*>.*?</\1>~is';
        $html = preg_replace($disallowed, '', $html);
        // Juga tag yang tidak ada pasangan penutup
        $html = preg_replace('~<(script|iframe|object|embed|style|meta|link)(\s[^>]*)?/?>~i', '', $html);

        // 2. Allowlist tag yang aman, strip sisanya
        $allowedTags = '<p><br><strong><b><em><i><u><s><strike><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><pre><code><a><table><thead><tbody><tr><th><td><span><div>';
        $html = strip_tags($html, $allowedTags);

        // 3. Hapus semua atribut on* (event handler) dan javascript: URL
        $html = preg_replace('/(\s)on\w+="[^"]*"/i', '', $html);
        $html = preg_replace("/(\s)on\w+='[^']*'/i", '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*[^ >]+/i', '', $html);
        $html = preg_replace('/(href|src)=["\']javascript:[^"\']*["\']/i', '$1="#"', $html);

        return trim($html);
    }
}
