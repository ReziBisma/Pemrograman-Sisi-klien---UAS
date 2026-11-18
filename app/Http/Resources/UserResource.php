<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'nama'   => $this->nama,
            'alamat' => $this->alamat,
            'no_ktp' => $this->no_ktp,
            'no_hp'  => $this->no_hp,
            'no_rm'  => $this->no_rm,
            'role'   => $this->role,
            'email'  => $this->email,
            'id_poli' => $this->id_poli,
            'poli'    => new PoliResource($this->whenLoaded('poli')),
        ];
    }
}
