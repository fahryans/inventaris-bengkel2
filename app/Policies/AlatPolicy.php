<?php

namespace App\Policies;

use App\Models\Alat;
use App\Models\User;

class AlatPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi',
            'kadep'
        ]);
    }

    public function view(User $user, Alat $alat): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor', 'kadep'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return $user->isTeknisiOf($alat->id_labor);
        }
        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi'
        ]);
    }

    public function update(User $user, Alat $alat): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return $user->isTeknisiOf($alat->id_labor);
        }
        return false;
    }

    public function delete(User $user, Alat $alat): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }
}
