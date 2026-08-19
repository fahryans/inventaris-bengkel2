<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlatRequest;
use App\Http\Resources\AlatResource;
use App\Models\Alat;
use Illuminate\Http\Request;

class AlatController extends Controller
{
    public function index(Request $request)
    {
        $query = Alat::with(['kategori', 'laboratorium']);

        if ($request->has('search')) {
            $query->where('nama_alat', 'like', "%{$request->search}%");
        }
        if ($request->has('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }
        if ($request->has('id_labor')) {
            $query->where('id_labor', $request->id_labor);
        }

        return AlatResource::collection($query->latest()->paginate(15));
    }

    public function store(AlatRequest $request)
    {
        $this->authorize('create', Alat::class);
        $alat = Alat::create($request->validated());
        return new AlatResource($alat->load(['kategori', 'laboratorium']));
    }

    public function show(Alat $alat)
    {
        $this->authorize('view', $alat);
        return new AlatResource($alat->load(['kategori', 'laboratorium', 'unitAlat']));
    }

    public function update(AlatRequest $request, Alat $alat)
    {
        $this->authorize('update', $alat);
        $alat->update($request->validated());
        return new AlatResource($alat->load(['kategori', 'laboratorium']));
    }

    public function destroy(Alat $alat)
    {
        $this->authorize('delete', $alat);
        $alat->delete();
        return response()->json(['message' => 'Alat berhasil dihapus']);
    }
}