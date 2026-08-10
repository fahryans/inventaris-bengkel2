<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeminjamanAlat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'peminjaman_alat';

    protected $fillable = [
        'id_alat',
        'id_unit_alat',
        'id_user_peminjam',
        'keperluan',
        'waktu_peminjaman',
        'waktu_pengembalian',
        'waktu_kembali_aktual',
        'jumlah',
        'kondisi_saat_peminjaman',
        'kondisi_saat_pengembalian',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'waktu_peminjaman' => 'datetime',
            'waktu_pengembalian' => 'datetime',
            'waktu_kembali_aktual' => 'datetime',
        ];
    }

    protected static function booting(): void
    {
        static::creating(function ($model) {
            if (!$model->id_alat && !$model->id_unit_alat) {
                throw new \InvalidArgumentException(
                    'Peminjaman harus memiliki id_alat (untuk agregat) atau id_unit_alat (untuk unit)'
                );
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('id_alat') || $model->isDirty('id_unit_alat')) {
                if (!$model->id_alat && !$model->id_unit_alat) {
                    throw new \InvalidArgumentException(
                        'Peminjaman harus memiliki id_alat (untuk agregat) atau id_unit_alat (untuk unit)'
                    );
                }
            }
        });
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'id_alat');
    }

    public function unitAlat(): BelongsTo
    {
        return $this->belongsTo(UnitAlat::class, 'id_unit_alat');
    }

    public function userPeminjam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_peminjam');
    }

    public function getEquipmentNameAttribute(): string
    {
        return $this->alat?->nama_alat ?? $this->unitAlat?->alat?->nama_alat ?? 'Unknown';
    }

    public function getEquipmentTypeAttribute(): string
    {
        return $this->id_alat ? 'Agregat' : 'Unit';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'terpinjam');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'sudah_dikembalikan');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'terpinjam' && $this->waktu_pengembalian < now();
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->waktu_pengembalian);
    }
}

