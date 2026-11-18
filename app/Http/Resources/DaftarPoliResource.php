<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DaftarPoliResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'no_antrian'      => $this->no_antrian,
            'keluhan'         => $this->keluhan,
            'pasien'          => new UserResource($this->whenLoaded('pasien')),
            'jadwal'          => new JadwalPeriksaResource($this->whenLoaded('jadwalPeriksa')),
            'sudah_diperiksa' => $this->whenLoaded('periksas', fn () => $this->periksas->isNotEmpty()),
            'created_at'      => $this->created_at,
        ];
    }
}
