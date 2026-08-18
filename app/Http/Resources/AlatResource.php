<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AlatResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama_alat' => $this->nama_alat,
            'merek' => $this->merek,
            'spesifikasi' => $this->spesifikasi,
            'tipe_pelacakan' => $this->tipe_pelacakan,
            'jumlah_alat' => $this->jumlah_alat,
            'foto' => $this->foto,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'laboratorium' => new LaboratoriumResource($this->whenLoaded('laboratorium')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}