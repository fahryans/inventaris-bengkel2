<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role',
        'nama',
        'no_hp',
        'no_induk',
        'email',
        'password',
        'foto',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => 'string',
            'status' => 'string',
        ];
    }

    public function laboratoriumDikelola(): HasMany
    {
        return $this->hasMany(Laboratorium::class, 'id_user_kalab');
    }

    public function pengadaanAlat(): HasMany
    {
        return $this->hasMany(PengadaanAlat::class, 'id_user_input');
    }

    public function pengadaanBahan(): HasMany
    {
        return $this->hasMany(PengadaanBahan::class, 'id_user_input');
    }

    public function peminjamanAlat(): HasMany
    {
        return $this->hasMany(PeminjamanAlat::class, 'id_user_peminjam');
    }

    public function pemakaianBahan(): HasMany
    {
        return $this->hasMany(PemakaianBahan::class, 'id_user_pemakai');
    }

    public function verifikasiPemakaianBahan(): HasMany
    {
        return $this->hasMany(PemakaianBahan::class, 'id_user_verifikasi');
    }

    public function pemeliharaanAlat(): HasMany
    {
        return $this->hasMany(PemeliharaanAlat::class, 'id_teknisi');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
