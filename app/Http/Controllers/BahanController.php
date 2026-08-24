<?php

namespace App\Http\Controllers;

use App\Http\Requests\BahanRequest;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

class BahanController extends Controller
{
    private function getLabIds()
    {
        $user = Auth::user();
        if ($user->role === 'teknisi') {
            return $user->laboratoriumTeknisi->pluck('id')->toArray();
        } elseif ($user->role === 'kepala_labor') {
            return $user->laboratoriumDikelola->pluck('id')->toArray();
        }
        return null;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Bahan::class);

        $user = Auth::user();
        $query = Bahan::with(['kategori', 'laboratorium']);

        $labIds = $this->getLabIds();
        if ($labIds) {
            $query->whereIn('id_labor', $labIds);
        }

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->filled('labor')) {
            $query->where('id_labor', $request->labor);
        }

        if ($request->filled('search')) {
            $query->where('nama_bahan', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortParam = $request->get('sort', 'nama_bahan');
        $parts = explode('|', $sortParam);
        $sortBy = $parts[0] ?? 'nama_bahan';
        $sortDir = $parts[1] ?? 'asc';

        $allowedSorts = ['nama_bahan', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama_bahan', 'asc');
        }

        $bahans = $query->paginate(15)->withQueryString();
        $kategoris = Kategori::where('jenis', 'bahan')->get();
        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();

        return view('bahan.index', compact('bahans', 'kategoris', 'laboratoriums'));
    }

    public function create()
    {
        $this->authorize('create', Bahan::class);

        $labIds = $this->getLabIds();
        $kategoris = Kategori::where('jenis', 'bahan')->get();
        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();
        $isTeknisi = Auth::user()->role === 'teknisi';
        $autoLab = $isTeknisi ? Auth::user()->laboratoriumTeknisi->first() : null;

        return view('bahan.create', compact('kategoris', 'laboratoriums', 'isTeknisi', 'autoLab'));
    }

    public function store(BahanRequest $request)
    {
        $this->authorize('create', Bahan::class);

        $validated = $request->validated();

        // Auto-set lab for teknisi
        if (Auth::user()->role === 'teknisi') {
            $validated['id_labor'] = Auth::user()->laboratoriumTeknisi->first()?->id;
        }

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

        $labIds = $this->getLabIds();
        $kategoris = Kategori::where('jenis', 'bahan')->get();
        $laboratoriums = $labIds ? Laboratorium::whereIn('id', $labIds)->get() : Laboratorium::all();
        $isTeknisi = Auth::user()->role === 'teknisi';
        $autoLab = $isTeknisi ? Auth::user()->laboratoriumTeknisi->first() : null;

        return view('bahan.edit', compact('bahan', 'kategoris', 'laboratoriums', 'isTeknisi', 'autoLab'));
    }

    public function update(BahanRequest $request, Bahan $bahan)
    {
        $this->authorize('update', $bahan);

        $oldData = $bahan->toArray();

        $validated = $request->validated();

        // Auto-set lab for teknisi
        if (Auth::user()->role === 'teknisi') {
            $validated['id_labor'] = Auth::user()->laboratoriumTeknisi->first()?->id;
        }

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
