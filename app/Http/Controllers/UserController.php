<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        User::create($validated);

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

        $validated = $request->validated();

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.show', $user)
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus');
    }
}
