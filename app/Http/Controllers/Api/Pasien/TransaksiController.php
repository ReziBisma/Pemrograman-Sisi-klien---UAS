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

        // Query dasar tanpa eager loading (ringan), hanya milik pasien yang login
    private function dasar(Request $request)
    {
        return Periksa::whereHas('daftarPoli', function ($q) use ($request) {
            $q->where('id_pasien', $request->user()->id);
        });
    }

    public function index(Request $request)
    {
        // Seluruh transaksi pasien (tanpa filter) untuk menentukan status lunas dan total tagihan
        $semua      = $this->dasar($request)->get(['id', 'biaya_periksa']);
        $lunasIds   = $semua->filter(fn ($t) => $t->sudahDibayar())->pluck('id');
        $totalBelum = (int) $semua->whereNotIn('id', $lunasIds)->sum('biaya_periksa');

        $query = $this->query($request)
            ->when($request->query('status') === 'lunas', fn ($q) => $q->whereIn('id', $lunasIds))
            ->when($request->query('status') === 'belum', fn ($q) => $q->whereNotIn('id', $lunasIds))
            ->when($request->query('dari'), fn ($q, $tgl) => $q->whereDate('tgl_periksa', '>=', $tgl))
            ->when($request->query('sampai'), fn ($q, $tgl) => $q->whereDate('tgl_periksa', '<=', $tgl))
            ->orderByDesc('tgl_periksa');

        return $this->halaman($request, $query, PeriksaResource::class)
            ->additional(['total_belum_dibayar' => $totalBelum]);
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