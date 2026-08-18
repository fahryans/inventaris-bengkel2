<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitAlatRequest;
use App\Models\Alat;
use App\Models\UnitAlat;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;

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

    public function store(UnitAlatRequest $request)
    {
        $this->authorize('create', UnitAlat::class);

        $validated = $request->validated();
        $validated['status'] = 'tersedia';

        $unitAlat = UnitAlat::create($validated);

        activity()
            ->performedOn($unitAlat)
            ->withProperties(['attributes' => $unitAlat->toArray()])
            ->event('created')
            ->log('Unit alat baru ditambahkan');

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

    public function update(UnitAlatRequest $request, UnitAlat $unitAlat)
    {
        $this->authorize('update', $unitAlat);

        $oldData = $unitAlat->toArray();
        $validated = $request->validated();

        $unitAlat->update($validated);

        activity()
            ->performedOn($unitAlat)
            ->withProperties(['old' => $oldData, 'attributes' => $unitAlat->toArray()])
            ->event('updated')
            ->log('Unit alat diperbarui');

        return redirect()->route('unit-alat.show', $unitAlat)
            ->with('success', 'Unit alat berhasil diperbarui');
    }

    public function destroy(UnitAlat $unitAlat)
    {
        $this->authorize('delete', $unitAlat);

        activity()
            ->performedOn($unitAlat)
            ->withProperties(['attributes' => $unitAlat->toArray()])
            ->event('deleted')
            ->log('Unit alat dihapus');

        $unitAlat->delete();

        return redirect()->route('unit-alat.index')
            ->with('success', 'Unit alat berhasil dihapus');
    }
}
