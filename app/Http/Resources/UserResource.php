<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'nama' => $this->nama,
            'no_hp' => $this->no_hp,
            'no_induk' => $this->no_induk,
            'email' => $this->email,
            'foto' => $this->foto,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}