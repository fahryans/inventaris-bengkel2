<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KategoriResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama_kategori' => $this->nama_kategori,
            'jenis' => $this->jenis,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}