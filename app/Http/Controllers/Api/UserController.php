<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        if ($request->has('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return UserResource::collection($query->latest()->paginate(15));
    }

    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);
        $user = User::create($request->validated());
        return new UserResource($user);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        return new UserResource($user);
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $user->update($request->validated());
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus']);
    }
}