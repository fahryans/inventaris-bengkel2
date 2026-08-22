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
        'tipe_pelacakan',
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

    public function spesifikasiAlat(): HasMany
    {
        return $this->hasMany(SpesifikasiAlat::class, 'id_alat');
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
        return $this->pengadaanAlat()->sum('jumlah');
    }

    /**
     * ponytail: kolom jumlah_alat dihapus oleh migrasi 2026_08_20; stok kini
     * dihitung dari pengadaan_alat. Accessor ini menjaga view/resource lama.
     * Hapus accessor + ganti pemanggilan saat refactor tampilan.
     */
    public function getJumlahAlatAttribute(): int
    {
        return $this->getAvailableQuantity();
    }
}
