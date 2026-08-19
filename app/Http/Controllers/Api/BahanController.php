<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BahanRequest;
use App\Http\Resources\BahanResource;
use App\Models\Bahan;
use Illuminate\Http\Request;

class BahanController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Bahan::class);

        $query = Bahan::with(['kategori', 'laboratorium']);

        if ($request->has('search')) {
            $query->where('nama_bahan', 'like', "%{$request->search}%");
        }
        if ($request->has('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }
        if ($request->has('id_labor')) {
            $query->where('id_labor', $request->id_labor);
        }

        return BahanResource::collection($query->latest()->paginate(15));
    }

    public function store(BahanRequest $request)
    {
        $this->authorize('create', Bahan::class);
        $bahan = Bahan::create($request->validated());
        return new BahanResource($bahan->load(['kategori', 'laboratorium']));
    }

    public function show(Bahan $bahan)
    {
        $this->authorize('view', $bahan);
        return new BahanResource($bahan->load(['kategori', 'laboratorium']));
    }

    public function update(BahanRequest $request, Bahan $bahan)
    {
        $this->authorize('update', $bahan);
        $bahan->update($request->validated());
        return new BahanResource($bahan->load(['kategori', 'laboratorium']));
    }

    public function destroy(Bahan $bahan)
    {
        $this->authorize('delete', $bahan);
        $bahan->delete();
        return response()->json(['message' => 'Bahan berhasil dihapus']);
    }
}