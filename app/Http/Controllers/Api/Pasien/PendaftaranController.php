<?php

namespace App\Http\Controllers\Api\Pasien;

use App\Http\Controllers\Controller;
use App\Http\Resources\DaftarPoliResource;
use App\Http\Resources\JadwalPeriksaResource;
use App\Http\Resources\PoliResource;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Http\Request;

class PendaftaranController extends Controller
{
    // Data untuk form pendaftaran: daftar poli dan jadwal dokter
    public function poli()
    {
        return response()->json([
            'polis'   => PoliResource::collection(Poli::all()),
            'jadwals' => JadwalPeriksaResource::collection(
                JadwalPeriksa::with('dokter.poli')->get()
            ),
        ]);
    }

    // Riwayat pendaftaran milik pasien yang login
    public function index(Request $request)
    {
        $query = DaftarPoli::with(['jadwalPeriksa.dokter.poli', 'periksas'])
            ->where('id_pasien', $request->user()->id)
            ->when($request->query('status') === 'menunggu', fn ($q) => $q->whereDoesntHave('periksas'))
            ->when($request->query('status') === 'sudah', fn ($q) => $q->whereHas('periksas'))
            ->latest('id');

        return $this->halaman($request, $query, DaftarPoliResource::class);
    }

    // Daftar ke sebuah jadwal
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan'   => 'nullable|string',
        ]);

        $pasienId = $request->user()->id;

        $masihAktif = DaftarPoli::where('id_pasien', $pasienId)
            ->where('id_jadwal', $data['id_jadwal'])
            ->whereDoesntHave('periksas')
            ->exists();

        if ($masihAktif) {
            return response()->json([
                'message' => 'Anda sudah terdaftar di jadwal ini dan belum diperiksa.',
            ], 422);
        }

        $noAntrian = DaftarPoli::where('id_jadwal', $data['id_jadwal'])->count() + 1;

        $daftar = DaftarPoli::create([
            'id_pasien'  => $pasienId,
            'id_jadwal'  => $data['id_jadwal'],
            'keluhan'    => $data['keluhan'] ?? null,
            'no_antrian' => $noAntrian,
        ]);

        $daftar->load('jadwalPeriksa.dokter.poli');

        return (new DaftarPoliResource($daftar))->response()->setStatusCode(201);
    }
}