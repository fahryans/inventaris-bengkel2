<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanAlat extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_alat';

    protected $fillable = [
        'id_alat',
        'id_unit_alat',
        'id_user_peminjam',
        'keperluan',
        'waktu_peminjaman',
        'waktu_pengembalian',
        'waktu_kembali_aktual',
        'jumlah',
        'kondisi_saat_peminjaman',
        'kondisi_saat_pengembalian',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'waktu_peminjaman' => 'datetime',
            'waktu_pengembalian' => 'datetime',
            'waktu_kembali_aktual' => 'datetime',
        ];
    }

    // Diisi kalau alat bertipe agregat
    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }

    // Diisi kalau alat bertipe unit (dilacak per unit fisik)
    public function unitAlat(): BelongsTo
    {
        return $this->belongsTo(UnitAlat::class, 'id_unit_alat');
    }

    public function userPeminjam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_peminjam');
    }
}
