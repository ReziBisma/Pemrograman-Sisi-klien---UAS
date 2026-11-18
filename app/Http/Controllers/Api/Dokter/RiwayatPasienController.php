<?php

namespace App\Http\Controllers\Api\Dokter;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeriksaResource;
use App\Models\Periksa;
use Illuminate\Http\Request;

class RiwayatPasienController extends Controller
{
    private function query(Request $request)
    {
        return Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($q) use ($request) {
                $q->where('id_dokter', $request->user()->id);
            })
            ->with([
                'daftarPoli.pasien',
                'daftarPoli.jadwalPeriksa.dokter.poli',
                'detailPeriksas.obat',
            ]);
    }

    public function index(Request $request)
    {
        return PeriksaResource::collection(
            $this->query($request)->orderByDesc('tgl_periksa')->get()
        );
    }

    public function show(Request $request, string $id)
    {
        return new PeriksaResource($this->query($request)->findOrFail($id));
    }
}