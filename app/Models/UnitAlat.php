<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitAlat extends Model
{
    use HasFactory;

    protected $table = 'unit_alat';

    protected $fillable = [
        'id_alat',
        'kode_inventaris',
        'kondisi_saat_ini',
        'status',
    ];

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }

    public function peminjamanAlat(): HasMany
    {
        return $this->hasMany(PeminjamanAlat::class, 'id_unit_alat');
    }

    public function pemeliharaanAlat(): HasMany
    {
        return $this->hasMany(PemeliharaanAlat::class, 'id_unit_alat');
    }
}
