<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PengadaanBahanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'id_bahan' => $this->id_bahan,
            'id_user_input' => $this->id_user_input,
            'tanggal_pengadaan' => $this->tanggal_pengadaan,
            'harga_perolehan' => $this->harga_perolehan,
            'jumlah' => $this->jumlah,
            'stok_tersisa_batch' => $this->stok_tersisa_batch,
            'masa_expire_bahan' => $this->masa_expire_bahan,
            'supplier' => $this->supplier,
            'tanggal_masuk' => $this->tanggal_masuk,
            'foto_transaksi' => $this->foto_transaksi,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}