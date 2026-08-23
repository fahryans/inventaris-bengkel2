<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitAlatRequest;
use App\Models\Alat;
use App\Models\UnitAlat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Activitylog\Facades\Activity;

class UnitAlatController extends Controller
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
        $this->authorize('viewAny', UnitAlat::class);

        $user = Auth::user();
        $query = UnitAlat::with(['alat', 'spesifikasiAlat']);

        $labIds = $this->getLabIds();
        if ($labIds) {
            $query->whereHas('alat', fn($q) => $q->whereIn('id_labor', $labIds));
        }

        if ($request->filled('search')) {
            // Saat search digunakan, abaikan filter id_alat, search mencari di semua alat
            $query->where(function ($q) use ($request) {
                $q->where('kode_investaris', 'like', '%' . $request->search . '%')
                  ->orWhereHas('alat', function ($subq) use ($request) {
                      $subq->where('nama_alat', 'like', '%' . $request->search . '%');
                  });
            });
        } elseif ($request->filled('id_alat')) {
            // Hanya aplikasikan filter id_alat jika search BUKADEH AKTIF
            $query->where('id_alat', $request->id_alat);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi_saat_ini', $request->kondisi);
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
        $alatsQuery = Alat::with('spesifikasiAlat')->where('tipe_pelacakan', 'unit');
        if ($labIds) {
            $alatsQuery->whereIn('id_labor', $labIds);
        }
        $alats = $alatsQuery->get();

        return view('unit_alat.index', compact('unitAlats', 'alats'));
    }

    public function create()
    {
        $this->authorize('create', UnitAlat::class);

        $labIds = $this->getLabIds();
        $alatsQuery = Alat::with('spesifikasiAlat')->where('tipe_pelacakan', 'unit');
        if ($labIds) {
            $alatsQuery->whereIn('id_labor', $labIds);
        }
        $alats = $alatsQuery->get();

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

        $labIds = $this->getLabIds();
        $alatsQuery = Alat::with('spesifikasiAlat')->where('tipe_pelacakan', 'unit');
        if ($labIds) {
            $alatsQuery->whereIn('id_labor', $labIds);
        }
        $alats = $alatsQuery->get();

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
