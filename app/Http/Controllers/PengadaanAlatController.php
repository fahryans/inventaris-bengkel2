<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengadaanAlatRequest;
use App\Models\Alat;
use App\Models\PengadaanAlat;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class PengadaanAlatController extends Controller
{
    public function __construct(
        protected StokService $stokService,
    ) {}
    public function index(Request $request)
    {
        $this->authorize('viewAny', PengadaanAlat::class);

        $query = PengadaanAlat::with(['alat', 'userInput'])->latest();

        if ($request->filled('alat')) {
            $query->where('id_alat', $request->alat);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
        }

        if ($request->filled('search')) {
            $query->whereHas('alat', function ($q) use ($request) {
                $q->where('nama_alat', 'like', '%' . $request->search . '%');
            });
        }

        $pengadaans = $query->paginate(15);
        $alats = Alat::all();

        return view('pengadaan_alat.index', compact('pengadaans', 'alats'));
    }

    public function create()
    {
        $this->authorize('create', PengadaanAlat::class);

        $alats = Alat::all();

        return view('pengadaan_alat.create', compact('alats'));
    }

    public function store(PengadaanAlatRequest $request)
    {
        $this->authorize('create', PengadaanAlat::class);

        $validated = $request->validated();
        $validated['id_user_input'] = Auth::id();

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        $pengadaan = PengadaanAlat::create($validated);

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['attributes' => $pengadaan->toArray()])
            ->event('created')
            ->log('Pengadaan alat baru dicatat');

        return redirect()->route('pengadaan_alat.index')
            ->with('success', 'Pengadaan alat berhasil dicatat');
    }

    public function show($id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('view', $pengadaan);

        $pengadaan->load(['alat', 'userInput']);

        return view('pengadaan_alat.show', compact('pengadaan'));
    }

    public function edit($id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('update', $pengadaan);

        $pengadaan->load('alat');
        $alats = Alat::all();

        return view('pengadaan_alat.edit', compact('pengadaan', 'alats'));
    }

    public function update(PengadaanAlatRequest $request, $id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('update', $pengadaan);

        $oldData = $pengadaan->toArray();
        $validated = $request->validated();

        if ($request->hasFile('foto_transaksi')) {
            if ($pengadaan->foto_transaksi) {
                \Storage::disk('public')->delete($pengadaan->foto_transaksi);
            }
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        DB::transaction(function () use ($pengadaan, $validated) {
            if ($pengadaan->tanggal_masuk) {
                $oldJumlah = $pengadaan->jumlah;

                if ($pengadaan->alat->isUnitTracked()) {
                    $delta = (int) $validated['jumlah'] - $oldJumlah;
                    $existingCount = \App\Models\UnitAlat::where('id_alat', $pengadaan->id_alat)->count();

                    if ($delta > 0) {
                        for ($i = $existingCount + 1; $i <= $existingCount + $delta; $i++) {
                            \App\Models\UnitAlat::create([
                                'id_alat' => $pengadaan->id_alat,
                                'kode_inventaris' => strtoupper('INV-' . $pengadaan->id_alat . '-' . str_pad($i, 3, '0', STR_PAD_LEFT)),
                                'kondisi_saat_ini' => 'baik',
                                'status' => 'tersedia',
                            ]);
                        }
                    } elseif ($delta < 0) {
                        $removed = \App\Models\UnitAlat::where('id_alat', $pengadaan->id_alat)
                            ->where('status', 'tersedia')
                            ->latest()
                            ->limit(abs($delta))
                            ->get();

                        if ($removed->count() < abs($delta)) {
                            throw new \Exception('Tidak dapat mengurangi jumlah karena unit yang tersedia kurang dari selisihnya');
                        }

                        \App\Models\UnitAlat::whereIn('id', $removed->pluck('id'))->delete();
                    }
                } else {
                    $delta = (int) $validated['jumlah'] - $oldJumlah;

                    if ($delta > 0) {
                        $this->stokService->tambahAlatAgregat($pengadaan->alat, $delta);
                    } elseif ($delta < 0) {
                        $this->stokService->kurangiAlatAgregat($pengadaan->alat, abs($delta));
                    }
                }
            }

            unset($validated['tanggal_masuk']);

            $pengadaan->update($validated);
        });

        $pengadaan->refresh();

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['old' => $oldData, 'attributes' => $pengadaan->toArray()])
            ->event('updated')
            ->log('Pengadaan alat diperbarui');

        return redirect()->route('pengadaan_alat.show', $pengadaan)
            ->with('success', 'Pengadaan alat berhasil diperbarui');
    }

    public function markReceived(Request $request, $id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('update', $pengadaan);

        if ($pengadaan->tanggal_masuk) {
            return redirect()->route('pengadaan_alat.show', $pengadaan)
                ->with('error', 'Pengadaan ini sudah pernah diterima');
        }

        $request->validate([
            'tanggal_masuk' => ['required', 'date'],
        ]);

        $oldData = $pengadaan->toArray();

        DB::transaction(function () use ($pengadaan, $request) {
            $pengadaan->update([
                'tanggal_masuk' => $request->tanggal_masuk,
            ]);

            if ($pengadaan->alat->isUnitTracked()) {
                $existingCount = \App\Models\UnitAlat::where('id_alat', $pengadaan->id_alat)->count();
                for ($i = 1; $i <= $pengadaan->jumlah; $i++) {
                    \App\Models\UnitAlat::create([
                        'id_alat' => $pengadaan->id_alat,
                        'kode_inventaris' => strtoupper('INV-' . $pengadaan->id_alat . '-' . str_pad($existingCount + $i, 3, '0', STR_PAD_LEFT)),
                        'kondisi_saat_ini' => 'baik',
                        'status' => 'tersedia',
                    ]);
                }
            } else {
                $this->stokService->tambahAlatAgregat(
                    $pengadaan->alat,
                    $pengadaan->jumlah
                );
            }
        });

        $pengadaan->refresh();

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['old' => $oldData, 'attributes' => $pengadaan->toArray()])
            ->event('received')
            ->log('Alat berhasil diterima');

        return redirect()->route('pengadaan_alat.show', $pengadaan)
            ->with('success', 'Alat berhasil diterima dan stok diperbarui');
    }

    public function destroy($id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('delete', $pengadaan);

        activity()
            ->performedOn($pengadaan)
            ->withProperties(['attributes' => $pengadaan->toArray()])
            ->event('deleted')
            ->log('Pengadaan alat dihapus');

        DB::transaction(function () use ($pengadaan) {
            if ($pengadaan->tanggal_masuk && !$pengadaan->alat->isUnitTracked()) {
                $this->stokService->kurangiAlatAgregat(
                    $pengadaan->alat,
                    $pengadaan->jumlah
                );
            }

            $pengadaan->delete();
        });

        return redirect()->route('pengadaan_alat.index')
            ->with('success', 'Pengadaan alat berhasil dihapus');
    }
}
