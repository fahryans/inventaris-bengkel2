<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PemakaianBahanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'id_bahan' => $this->id_bahan,
            'id_pengadaan_bahan' => $this->id_pengadaan_bahan,
            'id_user_pemakai' => $this->id_user_pemakai,
            'id_user_verifikasi' => $this->id_user_verifikasi,
            'keperluan' => $this->keperluan,
            'jumlah_pengambilan' => $this->jumlah_pengambilan,
            'jumlah_terpakai' => $this->jumlah_terpakai,
            'jumlah_pengembalian' => $this->jumlah_pengembalian,
            'waktu_pemakaian' => $this->waktu_pemakaian,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}