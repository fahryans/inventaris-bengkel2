<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemakaianBahanResource;
use App\Models\PemakaianBahan;
use App\Services\FIFOService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PemakaianBahanController extends Controller
{
    public function __construct(
        protected FIFOService $fifoService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PemakaianBahan::class);

        $query = PemakaianBahan::with(['bahan', 'userPemakai', 'userVerifikasi']);

        if ($request->has('id_bahan')) {
            $query->where('id_bahan', $request->id_bahan);
        }
        if ($request->has('verified') && $request->verified !== '') {
            if (in_array($request->verified, ['yes', '1', 1, true], true)) {
                $query->whereNotNull('id_user_verifikasi');
            } else {
                $query->whereNull('id_user_verifikasi');
            }
        }

        return PemakaianBahanResource::collection($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PemakaianBahan::class);

        $validated = $request->validate([
            'id_bahan' => ['required', 'exists:bahan,id'],
            'id_pengadaan_bahan' => ['required', 'exists:pengadaan_bahan,id'],
            'keperluan' => ['required', 'string', 'max:255'],
            'jumlah_pengambilan' => ['required', 'integer', 'min:1'],
            'jumlah_terpakai' => ['required', 'integer', 'min:1'],
            'jumlah_pengembalian' => ['nullable', 'integer', 'min:0'],
            'waktu_pemakaian' => ['required', 'date_format:Y-m-d H:i'],
        ]);

        $validated['id_user_pemakai'] = Auth::id();

        try {
            $pemakaian = DB::transaction(function () use ($validated) {
                $pemakaian = PemakaianBahan::create($validated);
                $this->fifoService->consumeFromBatches($validated['id_bahan'], $validated['jumlah_terpakai']);
                return $pemakaian;
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new PemakaianBahanResource($pemakaian->load(['bahan', 'userPemakai', 'userVerifikasi']));
    }

    public function show(PemakaianBahan $pemakaianBahan)
    {
        $this->authorize('view', $pemakaianBahan);
        return new PemakaianBahanResource($pemakaianBahan->load(['bahan', 'userPemakai', 'userVerifikasi']));
    }

    public function update(Request $request, PemakaianBahan $pemakaianBahan)
    {
        $this->authorize('update', $pemakaianBahan);

        $validated = $request->validate([
            'id_bahan' => ['required', 'exists:bahan,id'],
            'id_pengadaan_bahan' => ['required', 'exists:pengadaan_bahan,id'],
            'keperluan' => ['required', 'string', 'max:255'],
            'jumlah_pengambilan' => ['required', 'integer', 'min:1'],
            'jumlah_terpakai' => ['required', 'integer', 'min:1'],
            'jumlah_pengembalian' => ['nullable', 'integer', 'min:0'],
            'waktu_pemakaian' => ['required', 'date_format:Y-m-d H:i'],
        ]);

        $oldJumlah = $pemakaianBahan->jumlah_terpakai;
        $oldBahanId = $pemakaianBahan->id_bahan;
        $newJumlah = $validated['jumlah_terpakai'];
        $newBahanId = $validated['id_bahan'];

        try {
            DB::transaction(function () use ($pemakaianBahan, $validated, $oldJumlah, $oldBahanId, $newJumlah, $newBahanId) {
                $pemakaianBahan->update($validated);

                if ($oldBahanId === $newBahanId) {
                    $selisih = $newJumlah - $oldJumlah;
                    if ($selisih > 0) {
                        $this->fifoService->consumeFromBatches($newBahanId, $selisih);
                    } elseif ($selisih < 0) {
                        $this->fifoService->reverseConsumeFromBatches($newBahanId, abs($selisih));
                    }
                } else {
                    $this->fifoService->reverseConsumeFromBatches($oldBahanId, $oldJumlah);
                    $this->fifoService->consumeFromBatches($newBahanId, $newJumlah);
                }
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $pemakaianBahan->refresh();

        return new PemakaianBahanResource($pemakaianBahan->load(['bahan', 'userPemakai', 'userVerifikasi']));
    }

    public function verify(Request $request, PemakaianBahan $pemakaianBahan)
    {
        $this->authorize('verify', $pemakaianBahan);

        $pemakaianBahan->update(['id_user_verifikasi' => Auth::id()]);

        return new PemakaianBahanResource($pemakaianBahan->load(['bahan', 'userPemakai', 'userVerifikasi']));
    }

    public function destroy(PemakaianBahan $pemakaianBahan)
    {
        $this->authorize('delete', $pemakaianBahan);

        try {
            DB::transaction(function () use ($pemakaianBahan) {
                $this->fifoService->reverseConsumeFromBatches(
                    $pemakaianBahan->id_bahan,
                    $pemakaianBahan->jumlah_terpakai
                );
                $pemakaianBahan->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Pemakaian bahan berhasil dihapus']);
    }
}