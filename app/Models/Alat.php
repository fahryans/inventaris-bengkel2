<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alat';

    protected $fillable = [
        'id_kategori',
        'id_labor',
        'nama_alat',
        'merek',
        'spesifikasi',
        'tipe_pelacakan',
        'jumlah_alat',
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

    public function unitAlat(): HasMany
    {
        return $this->hasMany(UnitAlat::class, 'id_alat');
    }

    public function pengadaanAlat(): HasMany
    {
        return $this->hasMany(PengadaanAlat::class, 'id_alat');
    }

    public function peminjamanAlat(): HasMany
    {
        return $this->hasMany(PeminjamanAlat::class, 'id_alat');
    }

    public function isUnitTracked(): bool
    {
        return $this->tipe_pelacakan === 'unit';
    }

    public function getAvailableQuantity(): int
    {
        if ($this->isUnitTracked()) {
            return $this->unitAlat()->where('status', 'tersedia')->count();
        }
        return $this->jumlah_alat;
    }
}
