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
        $query = $this->query($request)
            ->when($request->query('search'), function ($q, $cari) {
                $q->whereHas('daftarPoli', function ($d) use ($cari) {
                    $d->where('keluhan', 'like', "%{$cari}%")
                      ->orWhereHas('pasien', fn ($p) => $p->where('nama', 'like', "%{$cari}%"));
                });
            })
            ->when($request->query('dari'), fn ($q, $tgl) => $q->whereDate('tgl_periksa', '>=', $tgl))
            ->when($request->query('sampai'), fn ($q, $tgl) => $q->whereDate('tgl_periksa', '<=', $tgl))
            ->orderByDesc('tgl_periksa');

        return $this->halaman($request, $query, PeriksaResource::class);
    }

    public function show(Request $request, string $id)
    {
        return new PeriksaResource($this->query($request)->findOrFail($id));
    }
}