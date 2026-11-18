<?php

namespace App\Http\Controllers\Api\Dokter;

use App\Http\Controllers\Controller;
use App\Http\Resources\DaftarPoliResource;
use App\Http\Resources\PeriksaResource;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ObatResource;

class PeriksaPasienController extends Controller
{
    // Pasien yang mendaftar ke jadwal dokter ini, urut nomor antrian
    public function index(Request $request)
    {
        $daftar = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($q) use ($request) {
                $q->where('id_dokter', $request->user()->id);
            })
            ->orderBy('no_antrian')
            ->get();

        return DaftarPoliResource::collection($daftar);
    }

    // Simpan hasil pemeriksaan + resep obat
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_daftar_poli' => 'required|exists:daftar_poli,id',
            'catatan'        => 'nullable|string',
            'obat'           => 'nullable|array',
            'obat.*'         => 'integer|exists:obat,id',
        ]);

        // Pendaftaran harus milik jadwal dokter yang login
        $daftar = DaftarPoli::whereHas('jadwalPeriksa', function ($q) use ($request) {
                $q->where('id_dokter', $request->user()->id);
            })->find($data['id_daftar_poli']);

        if (!$daftar) {
            return response()->json(['message' => 'Pendaftaran tidak ditemukan untuk jadwal Anda.'], 404);
        }

        if ($daftar->periksas()->exists()) {
            return response()->json(['message' => 'Pasien ini sudah diperiksa.'], 422);
        }

        $obatIds   = array_values(array_unique($data['obat'] ?? []));
        $totalObat = (int) Obat::whereIn('id', $obatIds)->sum('harga');

        $periksa = DB::transaction(function () use ($data, $obatIds, $totalObat) {
            $periksa = Periksa::create([
                'id_daftar_poli' => $data['id_daftar_poli'],
                'tgl_periksa'    => now(),
                'catatan'        => $data['catatan'] ?? null,
                'biaya_periksa'  => $totalObat + Periksa::JASA_DOKTER,
            ]);

            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat'    => $idObat,
                ]);
            }

            return $periksa;
        });

        $periksa->load([
            'daftarPoli.pasien',
            'daftarPoli.jadwalPeriksa.dokter.poli',
            'detailPeriksas.obat',
        ]);

        return (new PeriksaResource($periksa))->response()->setStatusCode(201);
    }

        // Daftar obat untuk form pemeriksaan (dokter tidak boleh memakai /admin/obat)
    public function obat()
    {
        return ObatResource::collection(Obat::all());
    }
}