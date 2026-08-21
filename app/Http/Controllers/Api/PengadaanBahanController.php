<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengadaanBahanResource;
use App\Models\PengadaanBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request)
    {
        $this->authorize('create', PengadaanBahan::class);

        $validated = $request->validate([
            'id_bahan' => ['required', 'exists:bahan,id'],
            'tanggal_pengadaan' => ['required', 'date'],
            'harga_perolehan' => ['required', 'numeric', 'min:0'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'masa_expire_bahan' => ['nullable', 'date'],
            'supplier' => ['required', 'string', 'max:255'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto_transaksi' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['id_user_input'] = Auth::id();
        $validated['stok_tersisa_batch'] = 0;

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        $pengadaan = PengadaanBahan::create($validated);

        return new PengadaanBahanResource($pengadaan->load(['bahan', 'userInput']));
    }

    public function show(PengadaanBahan $pengadaanBahan)
    {
        $this->authorize('view', $pengadaanBahan);
        return new PengadaanBahanResource($pengadaanBahan->load(['bahan', 'userInput']));
    }

    public function markReceived(Request $request, PengadaanBahan $pengadaanBahan)
    {
        $this->authorize('update', $pengadaanBahan);

        if ($pengadaanBahan->tanggal_masuk) {
            return response()->json(['message' => 'Pengadaan ini sudah pernah diterima'], 422);
        }

        $validated = $request->validate([
            'tanggal_masuk' => ['required', 'date'],
        ]);

        try {
            DB::transaction(function () use ($pengadaanBahan, $validated) {
                $pengadaanBahan->update([
                    'tanggal_masuk' => $validated['tanggal_masuk'],
                    'stok_tersisa_batch' => $pengadaanBahan->jumlah,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $pengadaanBahan->refresh();

        return new PengadaanBahanResource($pengadaanBahan->load(['bahan', 'userInput']));
    }

    public function update(Request $request, PengadaanBahan $pengadaanBahan)
    {
        $this->authorize('update', $pengadaanBahan);

        $validated = $request->validate([
            'id_bahan' => ['required', 'exists:bahan,id'],
            'tanggal_pengadaan' => ['required', 'date'],
            'harga_perolehan' => ['required', 'numeric', 'min:0'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'masa_expire_bahan' => ['nullable', 'date'],
            'supplier' => ['required', 'string', 'max:255'],
            'foto_transaksi' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($pengadaanBahan->tanggal_masuk && (int) $validated['jumlah'] < $pengadaanBahan->jumlah - $pengadaanBahan->stok_tersisa_batch) {
            return response()->json(['message' => 'Jumlah tidak boleh kurang dari yang sudah terpakai'], 422);
        }

        if ($request->hasFile('foto_transaksi')) {
            $validated['foto_transaksi'] = $request->file('foto_transaksi')->store('pengadaan', 'public');
        }

        try {
            DB::transaction(function () use ($pengadaanBahan, $validated) {
                if ($pengadaanBahan->tanggal_masuk) {
                    $oldStok = $pengadaanBahan->stok_tersisa_batch;
                    $used = $pengadaanBahan->jumlah - $oldStok;
                    $newStok = (int) $validated['jumlah'] - $used;

                    $validated['stok_tersisa_batch'] = $newStok;
                }

                $pengadaanBahan->update($validated);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $pengadaanBahan->refresh();

        return new PengadaanBahanResource($pengadaanBahan->load(['bahan', 'userInput']));
    }

    public function destroy(PengadaanBahan $pengadaanBahan)
    {
        $this->authorize('delete', $pengadaanBahan);

        try {
            DB::transaction(function () use ($pengadaanBahan) {
                $pengadaanBahan->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Pengadaan bahan berhasil dihapus']);
    }
}