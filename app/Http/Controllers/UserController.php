<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Facades\Activity;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->paginate(15);
        $roles = ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa'];
        $statuses = ['aktif', 'tidak_aktif'];

        return view('users.index', compact('users', 'roles', 'statuses'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa'];

        return view('users.create', compact('roles'));
    }

    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $logData = $user->toArray();
        unset($logData['password']);
        unset($logData['remember_token']);

        activity()
            ->performedOn($user)
            ->withProperties(['attributes' => $logData])
            ->event('created')
            ->log('User baru ditambahkan');

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['laboratoriumDikelola', 'pengadaanAlat', 'pengadaanBahan', 'peminjamanAlat', 'pemakaianBahan', 'pemeliharaanAlat']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = ['admin_jurusan', 'kepala_labor', 'kadep', 'teknisi', 'dosen', 'mahasiswa'];
        $statuses = ['aktif', 'tidak_aktif'];

        return view('users.edit', compact('user', 'roles', 'statuses'));
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $oldData = $user->toArray();
        unset($oldData['password']);
        unset($oldData['remember_token']);

        $validated = $request->validated();

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $newData = $user->toArray();
        unset($newData['password']);
        unset($newData['remember_token']);

        activity()
            ->performedOn($user)
            ->withProperties(['old' => $oldData, 'attributes' => $newData])
            ->event('updated')
            ->log('User diperbarui');

        return redirect()->route('users.show', $user)
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $logData = $user->toArray();
        unset($logData['password']);
        unset($logData['remember_token']);

        activity()
            ->performedOn($user)
            ->withProperties(['attributes' => $logData])
            ->event('deleted')
            ->log('User dihapus');

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
