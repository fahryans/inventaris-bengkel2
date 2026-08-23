<?php

namespace App\Policies;

use App\Models\UnitAlat;
use App\Models\User;

class UnitAlatPolicy
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

    public function view(User $user, UnitAlat $unitAlat): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor', 'kadep'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return $user->isTeknisiOf($unitAlat->alat->id_labor ?? 0);
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

    public function update(User $user, UnitAlat $unitAlat): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return $user->isTeknisiOf($unitAlat->alat->id_labor ?? 0);
        }
        return false;
    }

    public function delete(User $user, UnitAlat $unitAlat): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }
}
