<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitAlatRequest;
use App\Models\Alat;
use App\Models\UnitAlat;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Activitylog\Facades\Activity;

class UnitAlatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', UnitAlat::class);

        $query = UnitAlat::with(['alat', 'spesifikasiAlat']);

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

        // Sorting
        $sortParam = $request->get('sort', 'kode_inventaris');
        $parts = explode('|', $sortParam);
        $sortBy = $parts[0] ?? 'kode_inventaris';
        $sortDir = $parts[1] ?? 'asc';

        // Handle sorting by relationship fields
        switch ($sortBy) {
            case 'alat':
                $query->join('alat', 'unit_alat.id_alat', '=', 'alat.id')
                    ->select('unit_alat.*')
                    ->orderBy('alat.nama_alat', $sortDir === 'desc' ? 'desc' : 'asc');
                break;
            case 'spesifikasi':
                $query->join('spesifikasi_alat', 'unit_alat.id_spesifikasi_alat', '=', 'spesifikasi_alat.id')
                    ->select('unit_alat.*')
                    ->orderBy('spesifikasi_alat.kode_spesifikasi', $sortDir === 'desc' ? 'desc' : 'asc');
                break;
            default:
                $allowedSorts = ['kode_inventaris', 'status', 'kondisi_saat_ini', 'created_at'];
                if (in_array($sortBy, $allowedSorts)) {
                    $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
                } else {
                    $query->orderBy('kode_inventaris', 'asc');
                }
        }

        $unitAlats = $query->paginate(15)->withQueryString();
        $alats = Alat::with('spesifikasiAlat')->where('tipe_pelacakan', 'unit')->get();

        return view('unit_alat.index', compact('unitAlats', 'alats'));
    }

    public function create()
    {
        $this->authorize('create', UnitAlat::class);

        $alats = Alat::with('spesifikasiAlat')->where('tipe_pelacakan', 'unit')->get();

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

        $unitAlat->load(['alat', 'spesifikasiAlat', 'peminjamanAlat', 'pemeliharaanAlat']);

        return view('unit_alat.show', compact('unitAlat'));
    }

    public function qr(UnitAlat $unitAlat)
    {
        $this->authorize('view', $unitAlat);

        return view('unit_alat.qr', compact('unitAlat'));
    }

    public function edit(UnitAlat $unitAlat)
    {
        $this->authorize('update', $unitAlat);

        $alats = Alat::with('spesifikasiAlat')->where('tipe_pelacakan', 'unit')->get();

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
