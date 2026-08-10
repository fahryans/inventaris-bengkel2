<?php

namespace App\Policies;

use App\Models\Laboratorium;
use App\Models\User;

class LaboratoriumPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'kadep'
        ]);
    }

    public function view(User $user, Laboratorium $laboratorium): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'kadep'
        ]);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin_jurusan'
        ]);
    }

    public function update(User $user, Laboratorium $laboratorium): bool
    {
        return in_array($user->role, [
            'admin_jurusan'
        ]);
    }

    public function delete(User $user, Laboratorium $laboratorium): bool
    {
        return in_array($user->role, [
            'admin_jurusan'
        ]);
    }
}
