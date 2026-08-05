<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratorium extends Model
{
    use HasFactory;

    protected $table = 'laboratorium';

    protected $fillable = [
        'id_user_kalab',
        'nama_labor',
        'lokasi',
        'sop',
    ];

    public function kalab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_kalab');
    }

    public function alat(): HasMany
    {
        return $this->hasMany(Alat::class, 'id_labor');
    }

    public function bahan(): HasMany
    {
        return $this->hasMany(Bahan::class, 'id_labor');
    }
}
