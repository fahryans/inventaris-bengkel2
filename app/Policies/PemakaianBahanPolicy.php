<?php

namespace App\Policies;

use App\Models\PemakaianBahan;
use App\Models\User;

class PemakaianBahanPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi',
            'kadep',
            'dosen',
            'mahasiswa'
        ]);
    }

    public function view(User $user, PemakaianBahan $pemakaian): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi',
            'kadep'
        ]) || $user->id === $pemakaian->id_user_pemakai;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi',
            'dosen',
            'mahasiswa'
        ]);
    }

    public function update(User $user, PemakaianBahan $pemakaian): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi'
        ]);
    }

    public function verify(User $user, PemakaianBahan $pemakaian): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }

    public function return(User $user, PemakaianBahan $pemakaian): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi'
        ]) && is_null($pemakaian->jumlah_pengembalian);
    }

    public function delete(User $user, PemakaianBahan $pemakaian): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }
}
