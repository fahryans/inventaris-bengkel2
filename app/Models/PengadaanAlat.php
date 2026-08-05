<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengadaanAlat extends Model
{
    use HasFactory;

    protected $table = 'pengadaan_alat';

    protected $fillable = [
        'id_alat',
        'id_user_input',
        'tanggal_pengadaan',
        'harga_perolehan',
        'jumlah',
        'supplier',
        'tanggal_masuk',
        'foto_transaksi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengadaan' => 'date',
            'tanggal_masuk' => 'date',
            'harga_perolehan' => 'decimal:2',
        ];
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }

    public function userInput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_input');
    }
}
