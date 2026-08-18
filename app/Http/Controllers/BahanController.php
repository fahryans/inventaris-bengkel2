<?php

namespace App\Http\Controllers;

use App\Http\Requests\BahanRequest;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;

class BahanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Bahan::class);

        $query = Bahan::with(['kategori', 'laboratorium'])->latest();

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->filled('labor')) {
            $query->where('id_labor', $request->labor);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stok_saat_ini', '<=', 'stok_minimum');
            }
        }

        if ($request->filled('search')) {
            $query->where('nama_bahan', 'like', '%' . $request->search . '%');
        }

        $bahans = $query->paginate(15);
        $kategoris = Kategori::where('jenis', 'bahan')->get();
        $laboratoriums = Laboratorium::all();

        return view('bahan.index', compact('bahans', 'kategoris', 'laboratoriums'));
    }

    public function create()
    {
        $this->authorize('create', Bahan::class);

        $kategoris = Kategori::where('jenis', 'bahan')->get();
        $laboratoriums = Laboratorium::all();

        return view('bahan.create', compact('kategoris', 'laboratoriums'));
    }

    public function store(BahanRequest $request)
    {
        $this->authorize('create', Bahan::class);

        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('bahan', 'public');
        }

        $bahan = Bahan::create($validated);

        activity()
            ->performedOn($bahan)
            ->withProperties(['attributes' => $bahan->toArray()])
            ->event('created')
            ->log('Bahan baru ditambahkan');

        return redirect()->route('bahan.index')
            ->with('success', 'Bahan berhasil ditambahkan');
    }

    public function show(Bahan $bahan)
    {
        $this->authorize('view', $bahan);

        $bahan->load(['kategori', 'laboratorium', 'pengadaanBahan', 'pemakaianBahan']);

        return view('bahan.show', compact('bahan'));
    }

    public function edit(Bahan $bahan)
    {
        $this->authorize('update', $bahan);

        $kategoris = Kategori::where('jenis', 'bahan')->get();
        $laboratoriums = Laboratorium::all();

        return view('bahan.edit', compact('bahan', 'kategoris', 'laboratoriums'));
    }

    public function update(BahanRequest $request, Bahan $bahan)
    {
        $this->authorize('update', $bahan);

        $oldData = $bahan->toArray();

        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($bahan->foto) {
                \Storage::disk('public')->delete($bahan->foto);
            }
            $validated['foto'] = $request->file('foto')->store('bahan', 'public');
        }

        $bahan->update($validated);

        activity()
            ->performedOn($bahan)
            ->withProperties(['old' => $oldData, 'attributes' => $bahan->toArray()])
            ->event('updated')
            ->log('Bahan diperbarui');

        return redirect()->route('bahan.show', $bahan)
            ->with('success', 'Bahan berhasil diperbarui');
    }

    public function destroy(Bahan $bahan)
    {
        $this->authorize('delete', $bahan);

        activity()
            ->performedOn($bahan)
            ->withProperties(['attributes' => $bahan->toArray()])
            ->event('deleted')
            ->log('Bahan dihapus');

        $bahan->delete();

        return redirect()->route('bahan.index')
            ->with('success', 'Bahan berhasil dihapus');
    }
}
