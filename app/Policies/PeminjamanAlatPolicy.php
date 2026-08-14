<?php

namespace App\Policies;

use App\Models\PeminjamanAlat;
use App\Models\User;

class PeminjamanAlatPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi',
            'dosen',
            'mahasiswa',
            'kadep'
        ]);
    }

    public function view(User $user, PeminjamanAlat $peminjaman): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi',
            'kadep'
        ]) || $user->id === $peminjaman->id_user_peminjam;
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

    public function update(User $user, PeminjamanAlat $peminjaman): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]) || ($user->id === $peminjaman->id_user_peminjam && $peminjaman->status === 'terpinjam');
    }

    public function return(User $user, PeminjamanAlat $peminjaman): bool
    {
        return (in_array($user->role, [
            'admin_jurusan',
            'kepala_labor',
            'teknisi',
            'dosen',
            'mahasiswa'
        ]) || $user->id === $peminjaman->id_user_peminjam) && $peminjaman->status === 'terpinjam';
    }

    public function delete(User $user, PeminjamanAlat $peminjaman): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]) && $peminjaman->status === 'sudah_dikembalikan';
    }
}
