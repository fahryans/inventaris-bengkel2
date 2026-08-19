<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengadaanBahan extends Model
{
    use HasFactory;

    protected $table = 'pengadaan_bahan';

    protected $fillable = [
        'id_bahan',
        'id_user_input',
        'tanggal_pengadaan',
        'harga_perolehan',
        'jumlah',
        'stok_tersisa_batch',
        'masa_expire_bahan',
        'supplier',
        'tanggal_masuk',
        'foto_transaksi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengadaan' => 'date',
            'masa_expire_bahan' => 'date',
            'tanggal_masuk' => 'date',
            'harga_perolehan' => 'decimal:2',
        ];
    }

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'id_bahan');
    }

    public function userInput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_input');
    }

    public function pemakaianBahan(): HasMany
    {
        return $this->hasMany(PemakaianBahan::class, 'id_pengadaan_bahan');
    }

    // Batch yang masih ada stoknya, diurutkan dari yang paling dekat kadaluarsa.
    // Dipakai untuk menentukan batch mana yang harus diambil lebih dulu (FIFO by expiry).
    public function scopeTersediaUrutExpiry($query, int $idBahan)
    {
        return $query->where('id_bahan', $idBahan)
            ->whereNotNull('tanggal_masuk')
            ->where('stok_tersisa_batch', '>', 0)
            ->orderByRaw('masa_expire_bahan IS NULL, masa_expire_bahan ASC');
    }
}
