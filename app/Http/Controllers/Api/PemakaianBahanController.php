<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PemakaianBahanRequest;
use App\Http\Resources\PemakaianBahanResource;
use App\Models\PemakaianBahan;
use Illuminate\Http\Request;

class PemakaianBahanController extends Controller
{
    public function index(Request $request)
    {
        $query = PemakaianBahan::with(['bahan', 'userPemakai', 'userVerifikasi']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('id_bahan')) {
            $query->where('id_bahan', $request->id_bahan);
        }

        return PemakaianBahanResource::collection($query->latest()->paginate(15));
    }

    public function store(PemakaianBahanRequest $request)
    {
        $this->authorize('create', PemakaianBahan::class);
        $pemakaian = PemakaianBahan::create($request->validated());
        return new PemakaianBahanResource($pemakaian->load(['bahan', 'userPemakai', 'userVerifikasi']));
    }

    public function show(PemakaianBahan $pemakaian)
    {
        $this->authorize('view', $pemakaian);
        return new PemakaianBahanResource($pemakaian->load(['bahan', 'userPemakai', 'userVerifikasi']));
    }

    public function update(PemakaianBahanRequest $request, PemakaianBahan $pemakaian)
    {
        $this->authorize('update', $pemakaian);
        $pemakaian->update($request->validated());
        return new PemakaianBahanResource($pemakaian->load(['bahan', 'userPemakai', 'userVerifikasi']));
    }

    public function destroy(PemakaianBahan $pemakaian)
    {
        $this->authorize('delete', $pemakaian);
        $pemakaian->delete();
        return response()->json(['message' => 'Pemakaian bahan berhasil dibatalkan']);
    }
}