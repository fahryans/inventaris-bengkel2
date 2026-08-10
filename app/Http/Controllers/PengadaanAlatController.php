<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengadaanAlatRequest;
use App\Models\Alat;
use App\Models\PengadaanAlat;
use Illuminate\Http\Request;

class PengadaanAlatController extends Controller
{
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
        $validated['id_user_input'] = auth()->id();

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        PengadaanAlat::create($validated);

        return redirect()->route('pengadaan_alat.index')
            ->with('success', 'Pengadaan alat berhasil dicatat');
    }

    public function show(PengadaanAlat $pengadaan)
    {
        $this->authorize('view', $pengadaan);

        $pengadaan->load(['alat', 'userInput']);

        return view('pengadaan_alat.show', compact('pengadaan'));
    }

    public function edit(PengadaanAlat $pengadaan)
    {
        $this->authorize('update', $pengadaan);

        $alats = Alat::all();

        return view('pengadaan_alat.edit', compact('pengadaan', 'alats'));
    }

    public function update(PengadaanAlatRequest $request, PengadaanAlat $pengadaan)
    {
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

    public function markReceived(Request $request, PengadaanAlat $pengadaan)
    {
        $this->authorize('update', $pengadaan);

        $request->validate([
            'tanggal_masuk' => ['required', 'date'],
        ]);

        $pengadaan->update([
            'tanggal_masuk' => $request->tanggal_masuk,
        ]);

        $pengadaan->alat->increment('jumlah_alat', $pengadaan->jumlah);

        return redirect()->route('pengadaan_alat.show', $pengadaan)
            ->with('success', 'Alat berhasil diterima dan stok diperbarui');
    }

    public function destroy(PengadaanAlat $pengadaan)
    {
        $this->authorize('delete', $pengadaan);

        $pengadaan->delete();

        return redirect()->route('pengadaan_alat.index')
            ->with('success', 'Pengadaan alat berhasil dihapus');
    }
}
