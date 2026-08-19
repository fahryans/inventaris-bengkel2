<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengadaanAlatRequest;
use App\Http\Resources\PengadaanAlatResource;
use App\Models\PengadaanAlat;
use Illuminate\Http\Request;

class PengadaanAlatController extends Controller
{
    public function index(Request $request)
    {
        $query = PengadaanAlat::with(['alat', 'userInput']);

        if ($request->has('search')) {
            $query->where('supplier', 'like', "%{$request->search}%");
        }
        if ($request->has('id_alat')) {
            $query->where('id_alat', $request->id_alat);
        }

        return PengadaanAlatResource::collection($query->latest()->paginate(15));
    }

    public function store(PengadaanAlatRequest $request)
    {
        $this->authorize('create', PengadaanAlat::class);
        $pengadaan = PengadaanAlat::create($request->validated());
        return new PengadaanAlatResource($pengadaan->load(['alat', 'userInput']));
    }

    public function show(PengadaanAlat $pengadaan)
    {
        $this->authorize('view', $pengadaan);
        return new PengadaanAlatResource($pengadaan->load(['alat', 'userInput']));
    }

    public function update(PengadaanAlatRequest $request, PengadaanAlat $pengadaan)
    {
        $this->authorize('update', $pengadaan);
        $pengadaan->update($request->validated());
        return new PengadaanAlatResource($pengadaan->load(['alat', 'userInput']));
    }

    public function destroy(PengadaanAlat $pengadaan)
    {
        $this->authorize('delete', $pengadaan);
        $pengadaan->delete();
        return response()->json(['message' => 'Pengadaan alat berhasil dibatalkan']);
    }
}