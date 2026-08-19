<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriRequest;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kategori::latest();

        if ($request->has('search')) {
            $query->where('nama_kategori', 'like', "%{$request->search}%");
        }

        return KategoriResource::collection($query->paginate(15));
    }

    public function store(KategoriRequest $request)
    {
        $this->authorize('create', Kategori::class);
        $kategori = Kategori::create($request->validated());
        return new KategoriResource($kategori);
    }

    public function show(Kategori $kategori)
    {
        $this->authorize('view', $kategori);
        return new KategoriResource($kategori);
    }

    public function update(KategoriRequest $request, Kategori $kategori)
    {
        $this->authorize('update', $kategori);
        $kategori->update($request->validated());
        return new KategoriResource($kategori);
    }

    public function destroy(Kategori $kategori)
    {
        $this->authorize('delete', $kategori);
        $kategori->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}