<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpesifikasiBahan extends Model
{
    use HasFactory;

    protected $table = 'spesifikasi_bahan';

    protected $fillable = [
        'id_bahan',
        'kode_spesifikasi',
        'nama_spesifikasi',
        'deskripsi',
    ];

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'id_bahan');
    }

    public function pengadaanBahan(): HasMany
    {
        return $this->hasMany(PengadaanBahan::class, 'id_spesifikasi_bahan');
    }

    /**
     * Hitung total stok untuk spesifikasi ini
     */
    public function getTotalStok(): int
    {
        return $this->pengadaanBahan()->sum('stok_tersisa_batch');
    }
}
