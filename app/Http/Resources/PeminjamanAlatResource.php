<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanAlatResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'id_alat' => $this->id_alat,
            'id_unit_alat' => $this->id_unit_alat,
            'id_user_peminjam' => $this->id_user_peminjam,
            'keperluan' => $this->keperluan,
            'waktu_peminjaman' => $this->waktu_peminjaman,
            'waktu_pengembalian' => $this->waktu_pengembalian,
            'waktu_kembali_aktual' => $this->waktu_kembali_aktual,
            'jumlah' => $this->jumlah,
            'kondisi_saat_peminjaman' => $this->kondisi_saat_peminjaman,
            'kondisi_saat_pengembalian' => $this->kondisi_saat_pengembalian,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}