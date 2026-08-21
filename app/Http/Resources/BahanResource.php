<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BahanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama_bahan' => $this->nama_bahan,
            'stok_saat_ini' => $this->getTotalStock(),
            'stok_minimum' => $this->stok_minimum,
            'satuan' => $this->satuan,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}