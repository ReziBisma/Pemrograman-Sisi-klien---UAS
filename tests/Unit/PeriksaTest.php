<?php

namespace Tests\Unit;

use App\Models\Periksa;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PeriksaTest extends TestCase
{
    public function test_jasa_dokter_tetap_150000(): void
    {
        $this->assertSame(150000, Periksa::JASA_DOKTER);
    }

    public function test_total_obat_adalah_biaya_dikurangi_jasa_dokter(): void
    {
        $periksa = new Periksa(['biaya_periksa' => 165000]);

        $this->assertSame(15000, $periksa->totalObat());
    }

    public function test_total_obat_tidak_pernah_negatif(): void
    {
        $periksa = new Periksa(['biaya_periksa' => 100000]);

        $this->assertSame(0, $periksa->totalObat());
    }

    public function test_kunci_pembayaran_berbeda_untuk_tiap_periksa(): void
    {
        $a = new Periksa();
        $a->id = 1;
        $b = new Periksa();
        $b->id = 2;

        $this->assertNotSame($a->kunciPembayaran(), $b->kunciPembayaran());
    }

    public function test_status_pembayaran_dibaca_dari_cache(): void
    {
        $periksa = new Periksa();
        $periksa->id = 99;

        $this->assertFalse($periksa->sudahDibayar());
        $this->assertNull($periksa->pembayaran());

        Cache::forever($periksa->kunciPembayaran(), ['metode' => 'Tunai', 'jumlah' => 165000]);

        $this->assertTrue($periksa->sudahDibayar());
        $this->assertSame('Tunai', $periksa->pembayaran()['metode']);
    }
}