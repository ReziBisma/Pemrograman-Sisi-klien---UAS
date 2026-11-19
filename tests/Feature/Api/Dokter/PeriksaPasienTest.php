<?php

namespace Tests\Feature\Api\Dokter;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\User;
use Tests\TestCase;

class PeriksaPasienTest extends TestCase
{
    private User $dokter;
    private DaftarPoli $daftar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dokter = $this->masukSebagaiDokter();

        $jadwal = JadwalPeriksa::factory()->create(['id_dokter' => $this->dokter->id]);
        $this->daftar = DaftarPoli::factory()->create(['id_jadwal' => $jadwal->id, 'no_antrian' => 1]);
    }

    // ===== Daftar antrian =====

    public function test_antrian_hanya_memuat_pasien_pada_jadwal_dokter_ini(): void
    {
        DaftarPoli::factory()->create();   // jadwal dokter lain

        $this->getJson('/api/dokter/periksa-pasien')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->daftar->id)
            ->assertJsonPath('data.0.sudah_diperiksa', false);
    }

    public function test_pencarian_dan_filter_status_antrian(): void
    {
        $siti = User::factory()->pasien()->create(['nama' => 'Siti Aminah']);
        DaftarPoli::factory()->create([
            'id_jadwal'  => $this->daftar->id_jadwal,
            'id_pasien'  => $siti->id,
            'no_antrian' => 2,
        ]);
        Periksa::factory()->create(['id_daftar_poli' => $this->daftar->id]);   // antrian 1 sudah diperiksa

        $this->getJson('/api/dokter/periksa-pasien?search=Siti')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.no_antrian', 2);

        $this->getJson('/api/dokter/periksa-pasien?status=belum')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.no_antrian', 2);

        $this->getJson('/api/dokter/periksa-pasien?status=sudah')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.no_antrian', 1);
    }

    public function test_antrian_berhalaman_dan_parameter_semua(): void
    {
        DaftarPoli::factory()->count(6)->create(['id_jadwal' => $this->daftar->id_jadwal]);

        $this->getJson('/api/dokter/periksa-pasien?per_page=3')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 7);

        $this->getJson('/api/dokter/periksa-pasien?semua=1')
            ->assertOk()
            ->assertJsonCount(7, 'data')
            ->assertJsonMissingPath('meta');
    }

    public function test_dokter_dapat_mengambil_daftar_obat_tanpa_pagination(): void
    {
        Obat::factory()->count(12)->create();

        $this->getJson('/api/dokter/obat')
            ->assertOk()
            ->assertJsonCount(12, 'data');
    }

    // ===== Simpan pemeriksaan =====

    public function test_simpan_pemeriksaan_dengan_resep_obat_dan_biaya_dihitung_server(): void
    {
        $obat1 = Obat::factory()->create(['harga' => 10000]);
        $obat2 = Obat::factory()->create(['harga' => 5000]);

        $this->postJson('/api/dokter/periksa-pasien', [
            'id_daftar_poli' => $this->daftar->id,
            'catatan'        => 'Banyak istirahat',
            'obat'           => [$obat1->id, $obat2->id],
            'biaya_periksa'  => 1,   // dari klien, harus diabaikan
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.total_obat', 15000)
            ->assertJsonPath('data.jasa_dokter', 150000)
            ->assertJsonPath('data.biaya_periksa', 165000)
            ->assertJsonPath('data.status_bayar', 'Belum Dibayar');

        $this->assertDatabaseHas('periksa', [
            'id_daftar_poli' => $this->daftar->id,
            'biaya_periksa'  => 165000,
        ]);
        $this->assertDatabaseCount('detail_periksa', 2);
    }

    public function test_pemeriksaan_tanpa_obat_hanya_dikenai_jasa_dokter(): void
    {
        $this->postJson('/api/dokter/periksa-pasien', ['id_daftar_poli' => $this->daftar->id])
            ->assertStatus(201)
            ->assertJsonPath('data.biaya_periksa', 150000);

        $this->assertDatabaseCount('detail_periksa', 0);
    }

    public function test_obat_yang_sama_dua_kali_dihitung_sekali(): void
    {
        $obat = Obat::factory()->create(['harga' => 10000]);

        $this->postJson('/api/dokter/periksa-pasien', [
            'id_daftar_poli' => $this->daftar->id,
            'obat'           => [$obat->id, $obat->id],
        ])->assertStatus(201)->assertJsonPath('data.biaya_periksa', 160000);

        $this->assertDatabaseCount('detail_periksa', 1);
    }

    public function test_pasien_tidak_bisa_diperiksa_dua_kali(): void
    {
        $this->postJson('/api/dokter/periksa-pasien', ['id_daftar_poli' => $this->daftar->id])->assertStatus(201);

        $this->postJson('/api/dokter/periksa-pasien', ['id_daftar_poli' => $this->daftar->id])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pasien ini sudah diperiksa.');

        $this->assertDatabaseCount('periksa', 1);
    }

    public function test_dokter_tidak_bisa_memeriksa_pendaftaran_dokter_lain(): void
    {
        $milikLain = DaftarPoli::factory()->create();

        $this->postJson('/api/dokter/periksa-pasien', ['id_daftar_poli' => $milikLain->id])
            ->assertStatus(404);

        $this->assertDatabaseCount('periksa', 0);
    }

    public function test_validasi_pemeriksaan(): void
    {
        $this->postJson('/api/dokter/periksa-pasien', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_daftar_poli']);

        $this->postJson('/api/dokter/periksa-pasien', ['id_daftar_poli' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_daftar_poli']);

        $this->postJson('/api/dokter/periksa-pasien', ['id_daftar_poli' => $this->daftar->id, 'obat' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['obat.0']);

        $this->postJson('/api/dokter/periksa-pasien', ['id_daftar_poli' => $this->daftar->id, 'obat' => 'paracetamol'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['obat']);
    }

    // ===== Riwayat =====

    public function test_riwayat_memuat_antrian_keluhan_dan_biaya(): void
    {
        Periksa::factory()->create(['id_daftar_poli' => $this->daftar->id, 'biaya_periksa' => 165000]);

        $this->getJson('/api/dokter/riwayat-pasien')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.no_antrian', 1)
            ->assertJsonPath('data.0.keluhan', $this->daftar->keluhan)
            ->assertJsonPath('data.0.biaya_periksa', 165000);
    }

    public function test_detail_riwayat_memuat_obat(): void
    {
        $obat    = Obat::factory()->create(['nama_obat' => 'Paracetamol', 'harga' => 5000]);
        $periksa = Periksa::factory()->create(['id_daftar_poli' => $this->daftar->id, 'biaya_periksa' => 155000]);
        $periksa->detailPeriksas()->create(['id_obat' => $obat->id]);

        $this->getJson("/api/dokter/riwayat-pasien/{$periksa->id}")
            ->assertOk()
            ->assertJsonPath('data.obat.0.nama_obat', 'Paracetamol')
            ->assertJsonPath('data.total_obat', 5000);
    }

    public function test_riwayat_dokter_lain_tidak_bisa_dilihat(): void
    {
        $milikLain = Periksa::factory()->create();

        $this->getJson('/api/dokter/riwayat-pasien')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/dokter/riwayat-pasien/{$milikLain->id}")->assertStatus(404);
    }

    public function test_riwayat_dapat_dicari_dan_difilter_tanggal(): void
    {
        $siti = User::factory()->pasien()->create(['nama' => 'Siti Aminah']);
        $daftar2 = DaftarPoli::factory()->create([
            'id_jadwal'  => $this->daftar->id_jadwal,
            'id_pasien'  => $siti->id,
            'no_antrian' => 2,
        ]);

        Periksa::factory()->create(['id_daftar_poli' => $this->daftar->id, 'tgl_periksa' => '2026-11-01 10:00:00']);
        Periksa::factory()->create(['id_daftar_poli' => $daftar2->id, 'tgl_periksa' => '2026-12-01 10:00:00']);

        $this->getJson('/api/dokter/riwayat-pasien?dari=2026-11-15')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.no_antrian', 2);

        $this->getJson('/api/dokter/riwayat-pasien?sampai=2026-11-15')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.no_antrian', 1);

        $this->getJson('/api/dokter/riwayat-pasien?search=Siti')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.no_antrian', 2);
    }
}