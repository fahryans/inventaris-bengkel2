<?php

namespace App\Policies;

use App\Models\PemeliharaanAlat;
use App\Models\User;

class PemeliharaanAlatPolicy
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

    public function view(User $user, PemeliharaanAlat $pemeliharaan): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor', 'kadep'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return $user->isTeknisiOf($pemeliharaan->unitAlat->alat->id_labor ?? 0);
        }
        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }

    public function update(User $user, PemeliharaanAlat $pemeliharaan): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return $user->isTeknisiOf($pemeliharaan->unitAlat->alat->id_labor ?? 0);
        }
        return false;
    }

    public function complete(User $user, PemeliharaanAlat $pemeliharaan): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return ($user->id === $pemeliharaan->id_teknisi)
                || $user->isTeknisiOf($pemeliharaan->unitAlat->alat->id_labor ?? 0);
        }
        return false;
    }

    public function delete(User $user, PemeliharaanAlat $pemeliharaan): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }
}
