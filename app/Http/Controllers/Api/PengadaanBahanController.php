<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengadaanBahanRequest;
use App\Http\Resources\PengadaanBahanResource;
use App\Models\PengadaanBahan;
use Illuminate\Http\Request;

class PengadaanBahanController extends Controller
{
    public function index(Request $request)
    {
        $query = PengadaanBahan::with(['bahan', 'userInput']);

        if ($request->has('search')) {
            $query->where('supplier', 'like', "%{$request->search}%");
        }
        if ($request->has('id_bahan')) {
            $query->where('id_bahan', $request->id_bahan);
        }

        return PengadaanBahanResource::collection($query->latest()->paginate(15));
    }

    public function store(PengadaanBahanRequest $request)
    {
        $this->authorize('create', PengadaanBahan::class);
        $pengadaan = PengadaanBahan::create($request->validated());
        return new PengadaanBahanResource($pengadaan->load(['bahan', 'userInput']));
    }

    public function show(PengadaanBahan $pengadaan)
    {
        $this->authorize('view', $pengadaan);
        return new PengadaanBahanResource($pengadaan->load(['bahan', 'userInput']));
    }

    public function update(PengadaanBahanRequest $request, PengadaanBahan $pengadaan)
    {
        $this->authorize('update', $pengadaan);
        $pengadaan->update($request->validated());
        return new PengadaanBahanResource($pengadaan->load(['bahan', 'userInput']));
    }

    public function destroy(PengadaanBahan $pengadaan)
    {
        $this->authorize('delete', $pengadaan);
        $pengadaan->delete();
        return response()->json(['message' => 'Pengadaan bahan berhasil dibatalkan']);
    }
}