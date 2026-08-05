<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeliharaanAlat extends Model
{
    use HasFactory;

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
}
