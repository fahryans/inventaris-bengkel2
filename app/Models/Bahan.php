<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bahan';

    protected $fillable = [
        'id_kategori',
        'id_labor',
        'nama_bahan',
        'stok_saat_ini',
        'stok_minimum',
        'satuan',
        'merek',
        'spesifikasi',
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

    public function pengadaanBahan(): HasMany
    {
        return $this->hasMany(PengadaanBahan::class, 'id_bahan');
    }

    public function pemakaianBahan(): HasMany
    {
        return $this->hasMany(PemakaianBahan::class, 'id_bahan');
    }

    public function isStokMenipis(): bool
    {
        return $this->stok_saat_ini <= $this->stok_minimum;
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stok_saat_ini', '<=', 'stok_minimum');
    }
}
