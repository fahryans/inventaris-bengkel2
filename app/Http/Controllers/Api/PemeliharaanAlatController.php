<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemeliharaanAlatResource;
use App\Models\PemeliharaanAlat;
use Illuminate\Http\Request;

class PemeliharaanAlatController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PemeliharaanAlat::class);

        $query = PemeliharaanAlat::with(['unitAlat', 'teknisi']);

        if ($request->has('id_unit_alat')) {
            $query->where('id_unit_alat', $request->id_unit_alat);
        }
        if ($request->has('status')) {
            if ($request->status === 'overdue') {
                $query->where('tanggal_cek_berikutnya', '<', now());
            } elseif ($request->status === 'upcoming') {
                $query->whereBetween('tanggal_cek_berikutnya', [now(), now()->addDays(7)]);
            }
        }

        return PemeliharaanAlatResource::collection($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PemeliharaanAlat::class);

        $validated = $request->validate([
            'id_unit_alat' => ['required', 'exists:unit_alat,id'],
            'id_teknisi' => ['required', 'exists:users,id'],
            'tanggal_cek' => ['required', 'date'],
            'tanggal_cek_berikutnya' => ['required', 'date'],
            'kondisi' => ['required', 'string', 'max:255'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'detail_biaya' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'hasil_pemeliharaan' => ['nullable', 'string'],
        ]);

        $pemeliharaan = PemeliharaanAlat::create($validated);

        return new PemeliharaanAlatResource($pemeliharaan->load(['unitAlat', 'teknisi']));
    }

    public function show(PemeliharaanAlat $pemeliharaan)
    {
        $this->authorize('view', $pemeliharaan);
        return new PemeliharaanAlatResource($pemeliharaan->load(['unitAlat', 'teknisi']));
    }

    public function update(Request $request, PemeliharaanAlat $pemeliharaan)
    {
        $this->authorize('update', $pemeliharaan);

        $validated = $request->validate([
            'id_unit_alat' => ['required', 'exists:unit_alat,id'],
            'id_teknisi' => ['required', 'exists:users,id'],
            'tanggal_cek' => ['required', 'date'],
            'tanggal_cek_berikutnya' => ['required', 'date'],
            'kondisi' => ['required', 'string', 'max:255'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'detail_biaya' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'hasil_pemeliharaan' => ['nullable', 'string'],
        ]);

        $pemeliharaan->update($validated);

        return new PemeliharaanAlatResource($pemeliharaan->load(['unitAlat', 'teknisi']));
    }

    public function complete(Request $request, PemeliharaanAlat $pemeliharaan)
    {
        $this->authorize('complete', $pemeliharaan);

        $validated = $request->validate([
            'kondisi' => ['required', 'string', 'max:255'],
            'hasil_pemeliharaan' => ['nullable', 'string'],
        ]);

        $pemeliharaan->update([
            'kondisi' => $validated['kondisi'],
            'hasil_pemeliharaan' => $validated['hasil_pemeliharaan'] ?? null,
            'tanggal_cek' => now(),
        ]);

        $pemeliharaan->unitAlat->update(['kondisi_saat_ini' => $validated['kondisi']]);

        return new PemeliharaanAlatResource($pemeliharaan->load(['unitAlat', 'teknisi']));
    }

    public function destroy(PemeliharaanAlat $pemeliharaan)
    {
        $this->authorize('delete', $pemeliharaan);
        $pemeliharaan->delete();
        return response()->json(['message' => 'Riwayat pemeliharaan alat berhasil dihapus']);
    }
}