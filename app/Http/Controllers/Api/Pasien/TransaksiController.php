<?php

namespace App\Http\Controllers\Api\Pasien;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeriksaResource;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TransaksiController extends Controller
{
    // Hanya pemeriksaan milik pasien yang login
    private function query(Request $request)
    {
        return Periksa::with([
                'daftarPoli.pasien',
                'daftarPoli.jadwalPeriksa.dokter.poli',
                'detailPeriksas.obat',
            ])
            ->whereHas('daftarPoli', function ($q) use ($request) {
                $q->where('id_pasien', $request->user()->id);
            });
    }

    public function index(Request $request)
    {
        $transaksis = $this->query($request)->orderByDesc('tgl_periksa')->get();

        return response()->json([
            'total_belum_dibayar' => (int) $transaksis
                ->filter(fn ($t) => !$t->sudahDibayar())
                ->sum('biaya_periksa'),
            'data' => PeriksaResource::collection($transaksis),
        ]);
    }

    public function show(Request $request, string $id)
    {
        return new PeriksaResource($this->query($request)->findOrFail($id));
    }

    public function bayar(Request $request, string $id)
    {
        $request->validate([
            'metode' => 'required|in:Tunai,Transfer Bank,E-Wallet',
        ]);

        $transaksi = $this->query($request)->findOrFail($id);

        if ($transaksi->sudahDibayar()) {
            return response()->json(['message' => 'Transaksi ini sudah dibayar.'], 422);
        }

        Cache::forever($transaksi->kunciPembayaran(), [
            'metode'    => $request->metode,
            'jumlah'    => (int) $transaksi->biaya_periksa,
            'tgl_bayar' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'message' => 'Pembayaran berhasil.',
            'data'    => new PeriksaResource($transaksi),
        ]);
    }
}