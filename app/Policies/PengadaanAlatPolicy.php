<?php

namespace App\Policies;

use App\Models\PengadaanAlat;
use App\Models\User;

class PengadaanAlatPolicy
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

    public function view(User $user, PengadaanAlat $pengadaan): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor', 'kadep'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return $user->isTeknisiOf($pengadaan->alat->id_labor ?? 0);
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

    public function update(User $user, PengadaanAlat $pengadaan): bool
    {
        if (in_array($user->role, ['admin_jurusan', 'kepala_labor'])) {
            return true;
        }
        if ($user->role === 'teknisi') {
            return $user->isTeknisiOf($pengadaan->alat->id_labor ?? 0);
        }
        return false;
    }

    public function delete(User $user, PengadaanAlat $pengadaan): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }
}
