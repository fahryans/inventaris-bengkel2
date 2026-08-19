<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanAlatResource;
use App\Models\PeminjamanAlat;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeminjamanAlatController extends Controller
{
    public function __construct(
        protected PeminjamanService $peminjamanService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PeminjamanAlat::class);

        $query = PeminjamanAlat::with(['alat', 'unitAlat', 'userPeminjam']);

        if (in_array(Auth::user()->role, ['dosen', 'mahasiswa'])) {
            $query->where('id_user_peminjam', Auth::id());
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('id_alat')) {
            $query->where('id_alat', $request->id_alat);
        }

        return PeminjamanAlatResource::collection($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PeminjamanAlat::class);

        $validated = $request->validate([
            'id_alat' => ['nullable', 'exists:alat,id'],
            'id_unit_alat' => ['nullable', 'exists:unit_alat,id'],
            'keperluan' => ['required', 'string', 'max:255'],
            'waktu_peminjaman' => ['required', 'date_format:Y-m-d H:i'],
            'waktu_pengembalian' => ['nullable', 'date_format:Y-m-d H:i', 'after:waktu_peminjaman'],
            'jumlah' => ['nullable', 'integer', 'min:1'],
            'kondisi_saat_peminjaman' => ['required', 'string', 'max:255'],
        ]);

        $validated['id_user_peminjam'] = Auth::id();

        try {
            $peminjaman = $this->peminjamanService->createBorrowing($validated);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new PeminjamanAlatResource($peminjaman->load(['alat', 'unitAlat', 'userPeminjam']));
    }

    public function show(PeminjamanAlat $peminjaman)
    {
        $this->authorize('view', $peminjaman);
        return new PeminjamanAlatResource($peminjaman->load(['alat', 'unitAlat', 'userPeminjam']));
    }

    public function update(Request $request, PeminjamanAlat $peminjaman)
    {
        $this->authorize('update', $peminjaman);

        if ($peminjaman->status === 'sudah_dikembalikan') {
            return response()->json(['message' => 'Tidak dapat mengedit peminjaman yang sudah dikembalikan'], 422);
        }

        $validated = $request->validate([
            'keperluan' => ['required', 'string', 'max:255'],
            'waktu_pengembalian' => ['nullable', 'date_format:Y-m-d H:i', 'after:waktu_peminjaman'],
            'kondisi_saat_peminjaman' => ['required', 'string', 'max:255'],
        ]);

        $peminjaman->update($validated);

        return new PeminjamanAlatResource($peminjaman->load(['alat', 'unitAlat', 'userPeminjam']));
    }

    public function return(Request $request, PeminjamanAlat $peminjaman)
    {
        $this->authorize('return', $peminjaman);

        $validated = $request->validate([
            'waktu_kembali_aktual' => ['required', 'date_format:Y-m-d H:i'],
            'kondisi_saat_pengembalian' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->peminjamanService->returnBorrowing($peminjaman, $validated);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $peminjaman->refresh();

        return new PeminjamanAlatResource($peminjaman->load(['alat', 'unitAlat', 'userPeminjam']));
    }

    public function destroy(PeminjamanAlat $peminjaman)
    {
        $this->authorize('delete', $peminjaman);

        if ($peminjaman->status === 'terpinjam') {
            return response()->json(['message' => 'Tidak dapat menghapus peminjaman yang masih aktif'], 422);
        }

        $peminjaman->delete();

        return response()->json(['message' => 'Peminjaman alat berhasil dihapus']);
    }
}