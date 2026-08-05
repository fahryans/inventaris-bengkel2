<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';

    protected $fillable = [
        'nama_kategori',
        'jenis',
    ];

    public function alat(): HasMany
    {
        return $this->hasMany(Alat::class, 'id_kategori');
    }

    public function bahan(): HasMany
    {
        return $this->hasMany(Bahan::class, 'id_kategori');
    }
}
