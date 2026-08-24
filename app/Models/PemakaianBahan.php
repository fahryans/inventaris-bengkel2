<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PemakaianBahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pemakaian_bahan';

    protected $fillable = [
        'id_bahan',
        'id_pengadaan_bahan',
        'id_laboratorium',
        'id_user_pemakai',
        'id_user_verifikasi',
        'keperluan',
        'jumlah_pengambilan',
        'jumlah_terpakai',
        'jumlah_pengembalian',
        'waktu_pemakaian',
        'status_pengembalian',
        'waktu_pengembalian',
    ];

    protected function casts(): array
    {
        return [
            'waktu_pemakaian' => 'datetime',
            'waktu_pengembalian' => 'datetime',
        ];
    }

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'id_bahan');
    }

    public function laboratorium(): BelongsTo
    {
        return $this->belongsTo(Laboratorium::class, 'id_laboratorium');
    }

    public function pengadaanBahan(): BelongsTo
    {
        return $this->belongsTo(PengadaanBahan::class, 'id_pengadaan_bahan');
    }

    public function userPemakai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_pemakai');
    }

    public function userVerifikasi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_verifikasi');
    }
}
