<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpesifikasiAlat extends Model
{
    use HasFactory;

    protected $table = 'spesifikasi_alat';

    protected $fillable = [
        'id_alat',
        'kode_spesifikasi',
        'nama_spesifikasi',
        'deskripsi',
    ];

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }

    public function pengadaanAlat(): HasMany
    {
        return $this->hasMany(PengadaanAlat::class, 'id_spesifikasi_alat');
    }

    public function unitAlat(): HasMany
    {
        return $this->hasMany(UnitAlat::class, 'id_spesifikasi_alat');
    }

    public function getTotalUnit(): int
    {
        return $this->pengadaanAlat()->sum('jumlah');
    }
}
