<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlatRequest;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Laboratorium;
use Illuminate\Http\Request;

class AlatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Alat::class);

        $query = Alat::with(['kategori', 'laboratorium'])->latest();

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->filled('labor')) {
            $query->where('id_labor', $request->labor);
        }

        if ($request->filled('tipe')) {
            $query->where('tipe_pelacakan', $request->tipe);
        }

        if ($request->filled('search')) {
            $query->where('nama_alat', 'like', '%' . $request->search . '%');
        }

        $alats = $query->paginate(15);
        $kategoris = Kategori::where('jenis', 'alat')->get();
        $laboratoriums = Laboratorium::all();

        return view('alat.index', compact('alats', 'kategoris', 'laboratoriums'));
    }

    public function create()
    {
        $this->authorize('create', Alat::class);

        $kategoris = Kategori::where('jenis', 'alat')->get();
        $laboratoriums = Laboratorium::all();

        return view('alat.create', compact('kategoris', 'laboratoriums'));
    }

    public function store(AlatRequest $request)
    {
        $this->authorize('create', Alat::class);

        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('alat', 'public');
        }

        Alat::create($validated);

        return redirect()->route('alat.index')
            ->with('success', 'Alat berhasil ditambahkan');
    }

    public function show(Alat $alat)
    {
        $this->authorize('view', $alat);

        $alat->load(['kategori', 'laboratorium', 'unitAlat', 'pengadaanAlat', 'peminjamanAlat']);

        return view('alat.show', compact('alat'));
    }

    public function edit(Alat $alat)
    {
        $this->authorize('update', $alat);

        $kategoris = Kategori::where('jenis', 'alat')->get();
        $laboratoriums = Laboratorium::all();

        return view('alat.edit', compact('alat', 'kategoris', 'laboratoriums'));
    }

    public function update(AlatRequest $request, Alat $alat)
    {
        $this->authorize('update', $alat);

        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($alat->foto) {
                \Storage::disk('public')->delete($alat->foto);
            }
            $validated['foto'] = $request->file('foto')->store('alat', 'public');
        }

        $alat->update($validated);

        return redirect()->route('alat.show', $alat)
            ->with('success', 'Alat berhasil diperbarui');
    }

    public function destroy(Alat $alat)
    {
        $this->authorize('delete', $alat);

        $alat->delete();

        return redirect()->route('alat.index')
            ->with('success', 'Alat berhasil dihapus');
    }
}
