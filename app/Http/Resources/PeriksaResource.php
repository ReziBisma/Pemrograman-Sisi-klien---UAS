<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Periksa;

class PeriksaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'tgl_periksa'   => $this->tgl_periksa,
            'catatan'       => $this->catatan,
            'total_obat'    => $this->totalObat(),
            'jasa_dokter'   => Periksa::JASA_DOKTER,
            'biaya_periksa' => (int) $this->biaya_periksa,
            'status_bayar'  => $this->sudahDibayar() ? 'Lunas' : 'Belum Dibayar',
            'pembayaran'    => $this->pembayaran(),
            'obat'          => $this->whenLoaded('detailPeriksas', fn () => ObatResource::collection(
                                    $this->detailPeriksas->pluck('obat')->filter()->values()
                               )),
            'pasien'        => $this->whenLoaded('daftarPoli', fn () => new UserResource($this->daftarPoli->pasien)),
            'dokter'        => $this->whenLoaded('daftarPoli', fn () => new UserResource($this->daftarPoli->jadwalPeriksa->dokter)),
            'no_antrian'    => $this->whenLoaded('daftarPoli', fn () => $this->daftarPoli->no_antrian),
            'keluhan'       => $this->whenLoaded('daftarPoli', fn () => $this->daftarPoli->keluhan),
        ];
    }
}
