<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengadaanAlatResource;
use App\Models\PengadaanAlat;
use App\Models\UnitAlat;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengadaanAlatController extends Controller
{
    public function __construct(
        protected StokService $stokService,
    ) {}

    public function index(Request $request)
    {
        $query = PengadaanAlat::with(['alat', 'userInput']);

        if ($request->has('search')) {
            $query->where('supplier', 'like', "%{$request->search}%");
        }
        if ($request->has('id_alat')) {
            $query->where('id_alat', $request->id_alat);
        }

        return PengadaanAlatResource::collection($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PengadaanAlat::class);

        $validated = $request->validate([
            'id_alat' => ['required', 'exists:alat,id'],
            'tanggal_pengadaan' => ['required', 'date'],
            'harga_perolehan' => ['required', 'numeric', 'min:0'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'supplier' => ['required', 'string', 'max:255'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto_transaksi' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['id_user_input'] = Auth::id();

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        $pengadaan = PengadaanAlat::create($validated);

        return new PengadaanAlatResource($pengadaan->load(['alat', 'userInput']));
    }

    public function show(PengadaanAlat $pengadaanAlat)
    {
        $this->authorize('view', $pengadaanAlat);
        return new PengadaanAlatResource($pengadaanAlat->load(['alat', 'userInput']));
    }

    public function markReceived(Request $request, PengadaanAlat $pengadaanAlat)
    {
        $this->authorize('update', $pengadaanAlat);

        if ($pengadaanAlat->tanggal_masuk) {
            return response()->json(['message' => 'Pengadaan ini sudah pernah diterima'], 422);
        }

        $validated = $request->validate([
            'tanggal_masuk' => ['required', 'date'],
        ]);

        try {
            DB::transaction(function () use ($pengadaanAlat, $validated) {
                $pengadaanAlat->update(['tanggal_masuk' => $validated['tanggal_masuk']]);

                if ($pengadaanAlat->alat->isUnitTracked()) {
                    $existingCount = UnitAlat::where('id_alat', $pengadaanAlat->id_alat)->count();
                    for ($i = 1; $i <= $pengadaanAlat->jumlah; $i++) {
                        UnitAlat::create([
                            'id_alat' => $pengadaanAlat->id_alat,
                            'kode_inventaris' => strtoupper('INV-' . $pengadaanAlat->id_alat . '-' . str_pad($existingCount + $i, 3, '0', STR_PAD_LEFT)),
                            'kondisi_saat_ini' => 'baik',
                            'status' => 'tersedia',
                        ]);
                    }
                } else {
                // Stok agregat: jumlah tersedia dihitung dari pengadaan_alat.
                // Tidak perlu mutasi — riwayat pembelian tidak diubah.
            }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $pengadaanAlat->refresh();

        return new PengadaanAlatResource($pengadaanAlat->load(['alat', 'userInput']));
    }

    public function update(Request $request, PengadaanAlat $pengadaanAlat)
    {
        $this->authorize('update', $pengadaanAlat);

        $validated = $request->validate([
            'id_alat' => ['required', 'exists:alat,id'],
            'tanggal_pengadaan' => ['required', 'date'],
            'harga_perolehan' => ['required', 'numeric', 'min:0'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'supplier' => ['required', 'string', 'max:255'],
            'foto_transaksi' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        try {
            DB::transaction(function () use ($pengadaanAlat, $validated) {
                if ($pengadaanAlat->tanggal_masuk) {
                    $oldJumlah = $pengadaanAlat->jumlah;

                    if ($pengadaanAlat->alat->isUnitTracked()) {
                        $delta = (int) $validated['jumlah'] - $oldJumlah;
                        $existingCount = UnitAlat::where('id_alat', $pengadaanAlat->id_alat)->count();

                        if ($delta > 0) {
                            for ($i = $existingCount + 1; $i <= $existingCount + $delta; $i++) {
                                UnitAlat::create([
                                    'id_alat' => $pengadaanAlat->id_alat,
                                    'kode_inventaris' => strtoupper('INV-' . $pengadaanAlat->id_alat . '-' . str_pad($i, 3, '0', STR_PAD_LEFT)),
                                    'kondisi_saat_ini' => 'baik',
                                    'status' => 'tersedia',
                                ]);
                            }
                        } elseif ($delta < 0) {
                            $removed = UnitAlat::where('id_alat', $pengadaanAlat->id_alat)
                                ->where('status', 'tersedia')
                                ->latest()
                                ->limit(abs($delta))
                                ->get();

                            if ($removed->count() < abs($delta)) {
                                throw new \Exception('Tidak dapat mengurangi jumlah karena unit yang tersedia kurang dari selisihnya');
                            }

                            UnitAlat::whereIn('id', $removed->pluck('id'))->delete();
                        }
                    } else {
                        $delta = (int) $validated['jumlah'] - $oldJumlah;

                        if ($delta > 0) {
                            // Stok agregat: tambah dihitung dari pengadaan_alat;
                            // kolom jumlah_alat di alat sudah tidak digunakan.
                        } elseif ($delta < 0) {
                            // Stok agregat: kurangi juga dihitung dari pengadaan_alat.
                        }
                    }
                }

                $pengadaanAlat->update($validated);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $pengadaanAlat->refresh();

        return new PengadaanAlatResource($pengadaanAlat->load(['alat', 'userInput']));
    }

    public function destroy(PengadaanAlat $pengadaanAlat)
    {
        $this->authorize('delete', $pengadaanAlat);

        try {
            DB::transaction(function () use ($pengadaanAlat) {
                // Stok agregat: kurangi dihitung dari tabel pengadaan_alat;
                // kolom jumlah_alat di alat sudah dihapus migrasi 2026_08_20.
                $pengadaanAlat->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Pengadaan alat berhasil dihapus']);
    }
}