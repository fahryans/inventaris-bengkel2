<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PemeliharaanAlatRequest;
use App\Http\Resources\PemeliharaanAlatResource;
use App\Models\PemeliharaanAlat;
use Illuminate\Http\Request;

class PemeliharaanAlatController extends Controller
{
    public function index(Request $request)
    {
        $query = PemeliharaanAlat::with(['unitAlat', 'teknisi']);

        if ($request->has('id_unit_alat')) {
            $query->where('id_unit_alat', $request->id_unit_alat);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return PemeliharaanAlatResource::collection($query->latest()->paginate(15));
    }

    public function store(PemeliharaanAlatRequest $request)
    {
        $this->authorize('create', PemeliharaanAlat::class);
        $pemeliharaan = PemeliharaanAlat::create($request->validated());
        return new PemeliharaanAlatResource($pemeliharaan->load(['unitAlat', 'teknisi']));
    }

    public function show(PemeliharaanAlat $pemeliharaan)
    {
        $this->authorize('view', $pemeliharaan);
        return new PemeliharaanAlatResource($pemeliharaan->load(['unitAlat', 'teknisi']));
    }

    public function update(PemeliharaanAlatRequest $request, PemeliharaanAlat $pemeliharaan)
    {
        $this->authorize('update', $pemeliharaan);
        $pemeliharaan->update($request->validated());
        return new PemeliharaanAlatResource($pemeliharaan->load(['unitAlat', 'teknisi']));
    }

    public function destroy(PemeliharaanAlat $pemeliharaan)
    {
        $this->authorize('delete', $pemeliharaan);
        $pemeliharaan->delete();
        return response()->json(['message' => 'Riwayat pemeliharaan alat berhasil dihapus']);
    }
}