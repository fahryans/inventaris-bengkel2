<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengadaanAlatRequest;
use App\Models\Alat;
use App\Models\PengadaanAlat;
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

        PengadaanAlat::create($validated);

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

        $validated = $request->validated();

        if ($request->hasFile('foto_transaksi')) {
            if ($pengadaan->foto_transaksi) {
                \Storage::disk('public')->delete($pengadaan->foto_transaksi);
            }
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        $pengadaan->update($validated);

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

        DB::transaction(function () use ($pengadaan, $request) {
            $pengadaan->update([
                'tanggal_masuk' => $request->tanggal_masuk,
            ]);

            $this->stokService->tambahAlatAgregat(
                $pengadaan->alat,
                $pengadaan->jumlah
            );
        });

        return redirect()->route('pengadaan_alat.show', $pengadaan)
            ->with('success', 'Alat berhasil diterima dan stok diperbarui');
    }

    public function destroy($id)
    {
        $pengadaan = PengadaanAlat::findOrFail($id);
        $this->authorize('delete', $pengadaan);

        $pengadaan->delete();

        return redirect()->route('pengadaan_alat.index')
            ->with('success', 'Pengadaan alat berhasil dihapus');
    }
}
