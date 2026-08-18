<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PemeliharaanAlatResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'id_unit_alat' => $this->id_unit_alat,
            'id_teknisi' => $this->id_teknisi,
            'tanggal_cek' => $this->tanggal_cek,
            'tanggal_cek_berikutnya' => $this->tanggal_cek_berikutnya,
            'kondisi' => $this->kondisi,
            'biaya' => $this->biaya,
            'detail_biaya' => $this->detail_biaya,
            'catatan' => $this->catatan,
            'hasil_pemeliharaan' => $this->hasil_pemeliharaan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}