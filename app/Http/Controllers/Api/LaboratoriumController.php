<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaboratoriumRequest;
use App\Http\Resources\LaboratoriumResource;
use App\Models\Laboratorium;
use Illuminate\Http\Request;

class LaboratoriumController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Laboratorium::class);

        $query = Laboratorium::latest();

        if ($request->has('search')) {
            $query->where('nama_labor', 'like', "%{$request->search}%");
        }

        return LaboratoriumResource::collection($query->paginate(15));
    }

    public function store(LaboratoriumRequest $request)
    {
        $this->authorize('create', Laboratorium::class);
        $laboratorium = Laboratorium::create($request->validated());
        return new LaboratoriumResource($laboratorium);
    }

    public function show(Laboratorium $laboratorium)
    {
        $this->authorize('view', $laboratorium);
        return new LaboratoriumResource($laboratorium);
    }

    public function update(LaboratoriumRequest $request, Laboratorium $laboratorium)
    {
        $this->authorize('update', $laboratorium);
        $laboratorium->update($request->validated());
        return new LaboratoriumResource($laboratorium);
    }

    public function destroy(Laboratorium $laboratorium)
    {
        $this->authorize('delete', $laboratorium);
        $laboratorium->delete();
        return response()->json(['message' => 'Laboratorium berhasil dihapus']);
    }
}