<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaboratoriumRequest;
use App\Models\Laboratorium;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

class LaboratoriumController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Laboratorium::class);

        $user = Auth::user();
        $query = Laboratorium::with('kalab')->latest();

        if ($user->role === 'teknisi') {
            $labIds = $user->laboratoriumTeknisi->pluck('id')->toArray();
            $query->whereIn('id', $labIds);
        } elseif ($user->role === 'kepala_labor') {
            $query->where('id_user_kalab', $user->id);
        }

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

        $laboratorium = Laboratorium::create($request->validated());

        activity()
            ->performedOn($laboratorium)
            ->withProperties(['attributes' => $laboratorium->toArray()])
            ->event('created')
            ->log('Laboratorium baru ditambahkan');

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
        $teknisis = User::where('role', 'teknisi')->get();

        return view('laboratorium.edit', compact('laboratorium', 'users', 'teknisis'));
    }

    public function update(LaboratoriumRequest $request, Laboratorium $laboratorium)
    {
        $this->authorize('update', $laboratorium);

        $oldData = $laboratorium->toArray();
        $laboratorium->update($request->validated());

        if ($request->has('teknisi')) {
            $laboratorium->teknisi()->sync($request->teknisi);
        }

        activity()
            ->performedOn($laboratorium)
            ->withProperties(['old' => $oldData, 'attributes' => $laboratorium->toArray()])
            ->event('updated')
            ->log('Laboratorium diperbarui');

        return redirect()->route('laboratorium.show', $laboratorium)
            ->with('success', 'Laboratorium berhasil diperbarui');
    }

    public function destroy(Laboratorium $laboratorium)
    {
        $this->authorize('delete', $laboratorium);

        activity()
            ->performedOn($laboratorium)
            ->withProperties(['attributes' => $laboratorium->toArray()])
            ->event('deleted')
            ->log('Laboratorium dihapus');

        $laboratorium->delete();

        return redirect()->route('laboratorium.index')
            ->with('success', 'Laboratorium berhasil dihapus');
    }
}
