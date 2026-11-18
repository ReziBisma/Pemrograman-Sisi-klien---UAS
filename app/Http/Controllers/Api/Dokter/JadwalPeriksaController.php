<?php

namespace App\Http\Controllers\Api\Dokter;

use App\Http\Controllers\Controller;
use App\Http\Resources\JadwalPeriksaResource;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;

class JadwalPeriksaController extends Controller
{
    private const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    private function rules(): array
    {
        return [
            'hari'        => 'required|in:' . implode(',', self::HARI),
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'status'      => 'nullable|in:Aktif,Tidak Aktif',
        ];
    }

    // Hanya jadwal milik dokter yang sedang login
    private function milikDokter(Request $request, string $id): JadwalPeriksa
    {
        return JadwalPeriksa::where('id_dokter', $request->user()->id)->findOrFail($id);
    }

    public function index(Request $request)
    {
        $jadwals = JadwalPeriksa::where('id_dokter', $request->user()->id)
            ->get()
            ->sortBy(fn ($j) => array_search($j->hari, self::HARI)) // Senin..Minggu
            ->values();

        return JadwalPeriksaResource::collection($jadwals);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $jadwal = JadwalPeriksa::create([
            'id_dokter'   => $request->user()->id, // dari token, bukan dari input
            'hari'        => $data['hari'],
            'jam_mulai'   => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'status'      => $data['status'] ?? 'Tidak Aktif',
        ]);

        return (new JadwalPeriksaResource($jadwal))->response()->setStatusCode(201);
    }

    public function show(Request $request, string $id)
    {
        return new JadwalPeriksaResource($this->milikDokter($request, $id));
    }

    public function update(Request $request, string $id)
    {
        $jadwal = $this->milikDokter($request, $id);
        $data   = $request->validate($this->rules());

        // status hanya diubah jika dikirim
        if (!array_key_exists('status', $data) || $data['status'] === null) {
            unset($data['status']);
        }

        $jadwal->update($data);

        return new JadwalPeriksaResource($jadwal);
    }

    public function destroy(Request $request, string $id)
    {
        $this->milikDokter($request, $id)->delete();

        return response()->json(['message' => 'Jadwal berhasil dihapus.']);
    }
}