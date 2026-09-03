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
            'kadep',
            'dosen',
            'mahasiswa',
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
        return $user->role === 'admin_jurusan';
    }

    public function verify(User $user, PemakaianBahan $pemakaian): bool
    {
        if (!in_array($user->role, ['admin_jurusan', 'kepala_labor', 'teknisi'])) {
            return false;
        }

        return $this->isLabOwner($user, $pemakaian);
    }

    private function isLabOwner(User $user, PemakaianBahan $pemakaian): bool
    {
        if ($user->role === 'admin_jurusan') {
            return true;
        }

        $labIds = $user->role === 'teknisi'
            ? $user->laboratoriumTeknisi()->pluck('laboratorium.id')
            : $user->laboratoriumDikelola()->pluck('laboratorium.id');

        return $labIds->contains($pemakaian->bahan?->id_labor);
    }

    public function return(User $user, PemakaianBahan $pemakaian): bool
    {
        return !is_null($pemakaian->id_user_verifikasi) && is_null($pemakaian->jumlah_pengembalian)
            && in_array($pemakaian->status_pengembalian, [null, 'pending'])
            && ($user->id === $pemakaian->id_user_pemakai || in_array($user->role, ['admin_jurusan', 'kepala_labor', 'teknisi', 'kadep']));
    }

    public function verifyReturn(User $user, PemakaianBahan $pemakaian): bool
    {
        return in_array($user->role, ['admin_jurusan', 'kepala_labor', 'teknisi'])
            && $pemakaian->status_pengembalian === 'pending'
            && $this->isLabOwner($user, $pemakaian);
    }

    public function rejectReturn(User $user, PemakaianBahan $pemakaian): bool
    {
        return in_array($user->role, ['admin_jurusan', 'kepala_labor', 'teknisi'])
            && $pemakaian->status_pengembalian === 'pending'
            && $this->isLabOwner($user, $pemakaian);
    }

    public function delete(User $user, PemakaianBahan $pemakaian): bool
    {
        return in_array($user->role, [
            'admin_jurusan',
            'kepala_labor'
        ]);
    }
}
