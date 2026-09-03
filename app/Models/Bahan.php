<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Bahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bahan';

    protected $fillable = [
        'id_kategori',
        'id_labor',
        'nama_bahan',
        'stok_minimum',
        'satuan',
        'foto',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function laboratorium(): BelongsTo
    {
        return $this->belongsTo(Laboratorium::class, 'id_labor');
    }

    public function spesifikasiBahan(): HasMany
    {
        return $this->hasMany(SpesifikasiBahan::class, 'id_bahan');
    }

    public function pengadaanBahan(): HasMany
    {
        return $this->hasMany(PengadaanBahan::class, 'id_bahan');
    }

    public function pemakaianBahan(): HasMany
    {
        return $this->hasMany(PemakaianBahan::class, 'id_bahan');
    }

    /**
     * Hitung total stok dari semua pengadaan
     */
    public function getTotalStock(): int
    {
        return $this->pengadaanBahan()->sum('stok_tersisa_batch');
    }

    /**
     * Hitung total jumlah yang pernah diadakan (agregat dari semua pengadaan)
     */
    public function getTotalAcquired(): int
    {
        return $this->pengadaanBahan()->sum('jumlah');
    }

    /**
     * Cek apakah stok saat ini melebihi stok minimum
     */
    public function isStokMenipis(): bool
    {
        return $this->getTotalStock() < $this->stok_minimum;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('(
            SELECT COALESCE(SUM(pb.stok_tersisa_batch), 0)
            FROM pengadaan_bahan pb
            WHERE pb.id_bahan = bahan.id
        ) < bahan.stok_minimum');
    }
}
