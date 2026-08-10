<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Kategori::class);

        $query = Kategori::latest();

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('search')) {
            $query->where('nama_kategori', 'like', '%' . $request->search . '%');
        }

        $kategoris = $query->paginate(15);

        return view('kategori.index', compact('kategoris'));
    }

    public function create()
    {
        $this->authorize('create', Kategori::class);

        return view('kategori.create');
    }

    public function store(KategoriRequest $request)
    {
        $this->authorize('create', Kategori::class);

        Kategori::create($request->validated());

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function show(Kategori $kategori)
    {
        $this->authorize('viewAny', Kategori::class);

        $kategori->load(['alat', 'bahan']);

        return view('kategori.show', compact('kategori'));
    }

    public function edit(Kategori $kategori)
    {
        $this->authorize('update', $kategori);

        return view('kategori.edit', compact('kategori'));
    }

    public function update(KategoriRequest $request, Kategori $kategori)
    {
        $this->authorize('update', $kategori);

        $kategori->update($request->validated());

        return redirect()->route('kategori.show', $kategori)
            ->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(Kategori $kategori)
    {
        $this->authorize('delete', $kategori);

        $kategori->delete();

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil dihapus');
    }
}
