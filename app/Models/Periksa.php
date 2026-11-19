<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Periksa extends Model
{
    use HasFactory;
    protected $table = 'periksa';

    protected $fillable = [
        'id_daftar_poli',
        'tgl_periksa',
        'catatan',
        'biaya_periksa'
    ];

    public function daftarPoli()
    {
        return $this->belongsTo(DaftarPoli::class, 'id_daftar_poli');
    }

    public function detailPeriksas()
    {
        return $this->hasMany(DetailPeriksa::class, 'id_periksa');
    }

        // Jasa dokter tetap, sama dengan angka di PeriksaPasienController@store
    public const JASA_DOKTER = 150000;

    // Kunci penyimpanan status pembayaran di cache
    public function kunciPembayaran(): string
    {
        return 'pembayaran_periksa_' . $this->id;
    }

    // Data pembayaran (metode, jumlah, tgl_bayar) atau null jika belum bayar
    public function pembayaran(): ?array
    {
        return Cache::get($this->kunciPembayaran());
    }

    public function sudahDibayar(): bool
    {
        return $this->pembayaran() !== null;
    }

    // Total harga obat = total tagihan dikurangi jasa dokter
    public function totalObat(): int
    {
        return max(0, (int) $this->biaya_periksa - self::JASA_DOKTER);
    }
}