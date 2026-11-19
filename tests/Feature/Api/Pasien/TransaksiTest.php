<?php

namespace Tests\Feature\Api\Pasien;

use App\Models\DaftarPoli;
use App\Models\Periksa;
use App\Models\User;
use Tests\TestCase;

class TransaksiTest extends TestCase
{
    private User $pasien;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pasien = $this->masukSebagaiPasien();
    }

    private function buatTransaksi(User $pasien, int $biaya = 165000, array $timpa = []): Periksa
    {
        $daftar = DaftarPoli::factory()->create(['id_pasien' => $pasien->id]);

        return Periksa::factory()->create(array_merge([
            'id_daftar_poli' => $daftar->id,
            'biaya_periksa'  => $biaya,
        ], $timpa));
    }

    // ===== Daftar dan rincian =====

    public function test_daftar_transaksi_memuat_rincian_dan_total_belum_dibayar(): void
    {
        $this->buatTransaksi($this->pasien, 165000);

        $this->getJson('/api/pasien/transaksi')
            ->assertOk()
            ->assertJsonPath('total_belum_dibayar', 165000)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.total_obat', 15000)
            ->assertJsonPath('data.0.jasa_dokter', 150000)
            ->assertJsonPath('data.0.biaya_periksa', 165000)
            ->assertJsonPath('data.0.status_bayar', 'Belum Dibayar');
    }

    public function test_hanya_transaksi_milik_sendiri_yang_tampil(): void
    {
        $this->buatTransaksi($this->pasien);
        $this->buatTransaksi(User::factory()->pasien()->create());

        $this->getJson('/api/pasien/transaksi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('total_belum_dibayar', 165000);
    }

    public function test_transaksi_berhalaman(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->buatTransaksi($this->pasien);
        }

        $this->getJson('/api/pasien/transaksi?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('total_belum_dibayar', 12 * 165000);   // total dari seluruh data, bukan halaman ini
    }

    // ===== Pembayaran =====

    public function test_bayar_transaksi_mengubah_status_menjadi_lunas(): void
    {
        $transaksi = $this->buatTransaksi($this->pasien);

        $this->postJson("/api/pasien/transaksi/{$transaksi->id}/bayar", ['metode' => 'Tunai'])
            ->assertOk()
            ->assertJsonPath('data.status_bayar', 'Lunas')
            ->assertJsonPath('data.pembayaran.metode', 'Tunai')
            ->assertJsonPath('data.pembayaran.jumlah', 165000);

        $this->getJson('/api/pasien/transaksi')
            ->assertOk()
            ->assertJsonPath('total_belum_dibayar', 0)
            ->assertJsonPath('data.0.status_bayar', 'Lunas');
    }

    public function test_transaksi_tidak_bisa_dibayar_dua_kali(): void
    {
        $transaksi = $this->buatTransaksi($this->pasien);
        $url       = "/api/pasien/transaksi/{$transaksi->id}/bayar";

        $this->postJson($url, ['metode' => 'Tunai'])->assertOk();

        $this->postJson($url, ['metode' => 'E-Wallet'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Transaksi ini sudah dibayar.');
    }

    public function test_validasi_metode_pembayaran(): void
    {
        $transaksi = $this->buatTransaksi($this->pasien);
        $url       = "/api/pasien/transaksi/{$transaksi->id}/bayar";

        $this->postJson($url, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['metode']);

        $this->postJson($url, ['metode' => 'Kartu Kredit'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['metode']);
    }

    // ===== Filter status (berbasis cache) =====

    public function test_filter_status_lunas_dan_belum(): void
    {
        $lunas = $this->buatTransaksi($this->pasien, 165000);
        $belum = $this->buatTransaksi($this->pasien, 200000);

        $this->postJson("/api/pasien/transaksi/{$lunas->id}/bayar", ['metode' => 'Tunai'])->assertOk();

        $this->getJson('/api/pasien/transaksi?status=lunas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $lunas->id);

        $this->getJson('/api/pasien/transaksi?status=belum')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $belum->id)
            ->assertJsonPath('total_belum_dibayar', 200000);
    }

    public function test_filter_tanggal_transaksi(): void
    {
        $this->buatTransaksi($this->pasien, 165000, ['tgl_periksa' => '2026-11-01 10:00:00']);
        $baru = $this->buatTransaksi($this->pasien, 165000, ['tgl_periksa' => '2026-12-01 10:00:00']);

        $this->getJson('/api/pasien/transaksi?dari=2026-11-15')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $baru->id);
    }

    // ===== Keamanan antar-pasien =====

    public function test_transaksi_pasien_lain_tidak_bisa_dilihat_atau_dibayar(): void
    {
        $milikLain = $this->buatTransaksi(User::factory()->pasien()->create());

        $this->getJson("/api/pasien/transaksi/{$milikLain->id}")->assertStatus(404);
        $this->postJson("/api/pasien/transaksi/{$milikLain->id}/bayar", ['metode' => 'Tunai'])->assertStatus(404);

        $this->assertFalse($milikLain->sudahDibayar());
    }
}