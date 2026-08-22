<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitAlatRequest;
use App\Http\Resources\UnitAlatResource;
use App\Models\UnitAlat;
use Illuminate\Http\Request;

class UnitAlatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', UnitAlat::class);

        $query = UnitAlat::with(['alat']);

        if ($request->has('search')) {
            $query->where('kode_inventaris', 'like', "%{$request->search}%");
        }
        if ($request->has('id_alat')) {
            $query->where('id_alat', $request->id_alat);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'tersedia');
        }

        return UnitAlatResource::collection($query->latest()->paginate(15));
    }

    public function store(UnitAlatRequest $request)
    {
        $this->authorize('create', UnitAlat::class);
        $validated = $request->validated();
        $validated['status'] = 'tersedia';
        $unitAlat = UnitAlat::create($validated);
        return new UnitAlatResource($unitAlat);
    }

    public function show(UnitAlat $unitAlat)
    {
        $this->authorize('view', $unitAlat);
        return new UnitAlatResource($unitAlat->load(['alat']));
    }

    public function update(UnitAlatRequest $request, UnitAlat $unitAlat)
    {
        $this->authorize('update', $unitAlat);
        $unitAlat->update($request->validated());
        return new UnitAlatResource($unitAlat->load(['alat']));
    }

    public function destroy(UnitAlat $unitAlat)
    {
        $this->authorize('delete', $unitAlat);
        $unitAlat->delete();
        return response()->json(['message' => 'Unit alat berhasil dihapus']);
    }
}