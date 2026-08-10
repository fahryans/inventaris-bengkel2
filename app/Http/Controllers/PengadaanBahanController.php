<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengadaanBahanRequest;
use App\Models\Bahan;
use App\Models\PengadaanBahan;
use Illuminate\Http\Request;

class PengadaanBahanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PengadaanBahan::class);

        $query = PengadaanBahan::with(['bahan', 'userInput'])->latest();

        if ($request->filled('bahan')) {
            $query->where('id_bahan', $request->bahan);
        }

        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . $request->supplier . '%');
        }

        if ($request->filled('search')) {
            $query->whereHas('bahan', function ($q) use ($request) {
                $q->where('nama_bahan', 'like', '%' . $request->search . '%');
            });
        }

        $pengadaans = $query->paginate(15);
        $bahans = Bahan::all();

        return view('pengadaan_bahan.index', compact('pengadaans', 'bahans'));
    }

    public function create()
    {
        $this->authorize('create', PengadaanBahan::class);

        $bahans = Bahan::all();

        return view('pengadaan_bahan.create', compact('bahans'));
    }

    public function store(PengadaanBahanRequest $request)
    {
        $this->authorize('create', PengadaanBahan::class);

        $validated = $request->validated();
        $validated['id_user_input'] = auth()->id();

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        PengadaanBahan::create($validated);

        return redirect()->route('pengadaan_bahan.index')
            ->with('success', 'Pengadaan bahan berhasil dicatat');
    }

    public function show(PengadaanBahan $pengadaan)
    {
        $this->authorize('view', $pengadaan);

        $pengadaan->load(['bahan', 'userInput', 'pemakaianBahan']);

        return view('pengadaan_bahan.show', compact('pengadaan'));
    }

    public function edit(PengadaanBahan $pengadaan)
    {
        $this->authorize('update', $pengadaan);

        $bahans = Bahan::all();

        return view('pengadaan_bahan.edit', compact('pengadaan', 'bahans'));
    }

    public function update(PengadaanBahanRequest $request, PengadaanBahan $pengadaan)
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

        return redirect()->route('pengadaan_bahan.show', $pengadaan)
            ->with('success', 'Pengadaan bahan berhasil diperbarui');
    }

    public function markReceived(Request $request, PengadaanBahan $pengadaan)
    {
        $this->authorize('update', $pengadaan);

        $request->validate([
            'tanggal_masuk' => ['required', 'date'],
        ]);

        $pengadaan->update([
            'tanggal_masuk' => $request->tanggal_masuk,
        ]);

        $pengadaan->bahan->increment('stok_saat_ini', $pengadaan->jumlah);

        return redirect()->route('pengadaan_bahan.show', $pengadaan)
            ->with('success', 'Bahan berhasil diterima dan stok diperbarui');
    }

    public function destroy(PengadaanBahan $pengadaan)
    {
        $this->authorize('delete', $pengadaan);

        $pengadaan->delete();

        return redirect()->route('pengadaan_bahan.index')
            ->with('success', 'Pengadaan bahan berhasil dihapus');
    }
}
