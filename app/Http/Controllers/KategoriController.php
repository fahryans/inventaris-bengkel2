<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;

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

        $kategori = Kategori::create($request->validated());

        activity()
            ->performedOn($kategori)
            ->withProperties(['attributes' => $kategori->toArray()])
            ->event('created')
            ->log('Kategori baru ditambahkan');

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function show(Kategori $kategori)
    {
        $this->authorize('view', $kategori);

        $kategori->load([
            'alat' => fn($q) => $q
                ->withCount(['unitAlat' => fn($u) => $u->where('status', 'tersedia')])
                ->withSum('pengadaanAlat', 'jumlah')
                ->withSum(['peminjamanAlat' => fn($p) => $p->active()], 'jumlah'),
            'bahan' => fn($q) => $q->withSum('pengadaanBahan', 'stok_tersisa_batch'),
        ]);

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

        $oldData = $kategori->toArray();
        $kategori->update($request->validated());

        activity()
            ->performedOn($kategori)
            ->withProperties(['old' => $oldData, 'attributes' => $kategori->toArray()])
            ->event('updated')
            ->log('Kategori diperbarui');

        return redirect()->route('kategori.show', $kategori)
            ->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(Kategori $kategori)
    {
        $this->authorize('delete', $kategori);

        activity()
            ->performedOn($kategori)
            ->withProperties(['attributes' => $kategori->toArray()])
            ->event('deleted')
            ->log('Kategori dihapus');

        $kategori->delete();

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori berhasil dihapus');
    }
}
