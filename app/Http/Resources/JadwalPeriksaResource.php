<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JadwalPeriksaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
            return [
            'id'          => $this->id,
            'hari'        => $this->hari,
            'jam_mulai'   => substr($this->jam_mulai, 0, 5),
            'jam_selesai' => substr($this->jam_selesai, 0, 5),
            'status'      => $this->status,
            'dokter'      => new UserResource($this->whenLoaded('dokter')),
        ];
    }
}
