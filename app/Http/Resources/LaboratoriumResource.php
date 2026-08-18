<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LaboratoriumResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama_labor' => $this->nama_labor,
            'lokasi' => $this->lokasi,
            'sop' => $this->sop,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}