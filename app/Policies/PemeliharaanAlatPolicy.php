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
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi',
            'kadep'
        ]);
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
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }

    public function complete(User $user, PemeliharaanAlat $pemeliharaan): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi'
        ]) || $user->id === $pemeliharaan->id_teknisi;
    }

    public function delete(User $user, PemeliharaanAlat $pemeliharaan): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }
}
