<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaboratoriumRequest;
use App\Models\Laboratorium;
use App\Models\User;
use Illuminate\Http\Request;

class LaboratoriumController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Laboratorium::class);

        $query = Laboratorium::with('kalab')->latest();

        if ($request->filled('search')) {
            $query->where('nama_labor', 'like', '%' . $request->search . '%');
        }

        $laboratoriums = $query->paginate(15);

        return view('laboratorium.index', compact('laboratoriums'));
    }

    public function create()
    {
        $this->authorize('create', Laboratorium::class);

        $users = User::where('role', 'kepala_labor')->get();

        return view('laboratorium.create', compact('users'));
    }

    public function store(LaboratoriumRequest $request)
    {
        $this->authorize('create', Laboratorium::class);

        Laboratorium::create($request->validated());

        return redirect()->route('laboratorium.index')
            ->with('success', 'Laboratorium berhasil ditambahkan');
    }

    public function show(Laboratorium $laboratorium)
    {
        $this->authorize('view', $laboratorium);

        $laboratorium->load(['kalab', 'alat', 'bahan']);

        return view('laboratorium.show', compact('laboratorium'));
    }

    public function edit(Laboratorium $laboratorium)
    {
        $this->authorize('update', $laboratorium);

        $users = User::where('role', 'kepala_labor')->get();

        return view('laboratorium.edit', compact('laboratorium', 'users'));
    }

    public function update(LaboratoriumRequest $request, Laboratorium $laboratorium)
    {
        $this->authorize('update', $laboratorium);

        $laboratorium->update($request->validated());

        return redirect()->route('laboratorium.show', $laboratorium)
            ->with('success', 'Laboratorium berhasil diperbarui');
    }

    public function destroy(Laboratorium $laboratorium)
    {
        $this->authorize('delete', $laboratorium);

        $laboratorium->delete();

        return redirect()->route('laboratorium.index')
            ->with('success', 'Laboratorium berhasil dihapus');
    }
}
