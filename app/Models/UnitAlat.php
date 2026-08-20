<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitAlat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'unit_alat';

    protected $fillable = [
        'id_alat',
        'id_spesifikasi_alat',
        'kode_inventaris',
        'kondisi_saat_ini',
        'status',
    ];

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }

    public function spesifikasiAlat(): BelongsTo
    {
        return $this->belongsTo(SpesifikasiAlat::class, 'id_spesifikasi_alat');
    }

    public function peminjamanAlat(): HasMany
    {
        return $this->hasMany(PeminjamanAlat::class, 'id_unit_alat');
    }

    public function pemeliharaanAlat(): HasMany
    {
        return $this->hasMany(PemeliharaanAlat::class, 'id_unit_alat');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'tersedia');
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', 'terpinjam');
    }
}
