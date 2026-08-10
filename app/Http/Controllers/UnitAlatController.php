<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\UnitAlat;
use Illuminate\Http\Request;

class UnitAlatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', UnitAlat::class);

        $query = UnitAlat::with('alat')->latest();

        if ($request->filled('alat')) {
            $query->where('id_alat', $request->alat);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi_saat_ini', $request->kondisi);
        }

        if ($request->filled('search')) {
            $query->where('kode_inventaris', 'like', '%' . $request->search . '%');
        }

        $unitAlats = $query->paginate(15);
        $alats = Alat::where('tipe_pelacakan', 'unit')->get();

        return view('unit_alat.index', compact('unitAlats', 'alats'));
    }

    public function create()
    {
        $this->authorize('create', UnitAlat::class);

        $alats = Alat::where('tipe_pelacakan', 'unit')->get();

        return view('unit_alat.create', compact('alats'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', UnitAlat::class);

        $validated = $request->validate([
            'id_alat' => 'required|exists:alat,id',
            'kode_inventaris' => 'required|string|max:255|unique:unit_alat,kode_inventaris',
            'kondisi_saat_ini' => 'required|in:baik,rusak_ringan,rusak_berat',
        ]);

        $validated['status'] = 'tersedia';

        UnitAlat::create($validated);

        return redirect()->route('unit-alat.index')
            ->with('success', 'Unit alat berhasil ditambahkan');
    }

    public function show(UnitAlat $unitAlat)
    {
        $this->authorize('view', $unitAlat);

        $unitAlat->load(['alat', 'peminjamanAlat', 'pemeliharaanAlat']);

        return view('unit_alat.show', compact('unitAlat'));
    }

    public function edit(UnitAlat $unitAlat)
    {
        $this->authorize('update', $unitAlat);

        $alats = Alat::where('tipe_pelacakan', 'unit')->get();

        return view('unit_alat.edit', compact('unitAlat', 'alats'));
    }

    public function update(Request $request, UnitAlat $unitAlat)
    {
        $this->authorize('update', $unitAlat);

        $validated = $request->validate([
            'id_alat' => 'required|exists:alat,id',
            'kode_inventaris' => 'required|string|max:255|unique:unit_alat,kode_inventaris,' . $unitAlat->id,
            'kondisi_saat_ini' => 'required|in:baik,rusak_ringan,rusak_berat',
            'status' => 'required|in:tersedia,dipinjam,rusak,maintenance',
        ]);

        $unitAlat->update($validated);

        return redirect()->route('unit-alat.show', $unitAlat)
            ->with('success', 'Unit alat berhasil diperbarui');
    }

    public function destroy(UnitAlat $unitAlat)
    {
        $this->authorize('delete', $unitAlat);

        $unitAlat->delete();

        return redirect()->route('unit-alat.index')
            ->with('success', 'Unit alat berhasil dihapus');
    }
}
