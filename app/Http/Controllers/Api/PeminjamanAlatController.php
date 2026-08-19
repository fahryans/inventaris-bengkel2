<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeminjamanAlatRequest;
use App\Http\Resources\PeminjamanAlatResource;
use App\Models\PeminjamanAlat;
use Illuminate\Http\Request;

class PeminjamanAlatController extends Controller
{
    public function index(Request $request)
    {
        $query = PeminjamanAlat::with(['alat', 'unitAlat', 'userPeminjam']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('id_alat')) {
            $query->where('id_alat', $request->id_alat);
        }

        return PeminjamanAlatResource::collection($query->latest()->paginate(15));
    }

    public function store(PeminjamanAlatRequest $request)
    {
        $this->authorize('create', PeminjamanAlat::class);
        $peminjaman = PeminjamanAlat::create($request->validated());
        return new PeminjamanAlatResource($peminjaman->load(['alat', 'unitAlat', 'userPeminjam']));
    }

    public function show(PeminjamanAlat $peminjaman)
    {
        $this->authorize('view', $peminjaman);
        return new PeminjamanAlatResource($peminjaman->load(['alat', 'unitAlat', 'userPeminjam']));
    }

    public function update(PeminjamanAlatRequest $request, PeminjamanAlat $peminjaman)
    {
        $this->authorize('update', $peminjaman);
        $peminjaman->update($request->validated());
        return new PeminjamanAlatResource($peminjaman->load(['alat', 'unitAlat', 'userPeminjam']));
    }

    public function destroy(PeminjamanAlat $peminjaman)
    {
        $this->authorize('delete', $peminjaman);
        $peminjaman->delete();
        return response()->json(['message' => 'Peminjaman alat berhasil dibatalkan']);
    }
}