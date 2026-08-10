<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PemeliharaanAlat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pemeliharaan_alat';

    protected $fillable = [
        'id_unit_alat',
        'id_teknisi',
        'tanggal_cek',
        'tanggal_cek_berikutnya',
        'kondisi',
        'biaya',
        'detail_biaya',
        'catatan',
        'hasil_pemeliharaan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_cek' => 'date',
            'tanggal_cek_berikutnya' => 'date',
            'biaya' => 'decimal:2',
        ];
    }

    public function unitAlat(): BelongsTo
    {
        return $this->belongsTo(UnitAlat::class, 'id_unit_alat');
    }

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_teknisi');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_cek_berikutnya', '<=', now()->addDays(7));
    }

    public function scopeOverdue($query)
    {
        return $query->where('tanggal_cek_berikutnya', '<', now());
    }

    public function isOverdue(): bool
    {
        return $this->tanggal_cek_berikutnya && $this->tanggal_cek_berikutnya->isPast();
    }
}
