<?php

namespace Tests\Feature\Api\Pasien;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Tests\TestCase;

class PendaftaranTest extends TestCase
{
    private User $pasien;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pasien = $this->masukSebagaiPasien();
    }

    public function test_data_form_pendaftaran_memuat_poli_dan_jadwal(): void
    {
        Poli::factory()->count(2)->create();
        JadwalPeriksa::factory()->create();

        $this->getJson('/api/pasien/poli')
            ->assertOk()
            ->assertJsonStructure(['polis', 'jadwals'])
            ->assertJsonPath('jadwals.0.dokter.role', 'dokter');
    }

    // ===== Daftar =====

    public function test_daftar_ke_jadwal_memakai_id_pasien_dari_login(): void
    {
        $jadwal = JadwalPeriksa::factory()->create();
        $lain   = User::factory()->pasien()->create();

        $this->postJson('/api/pasien/pendaftaran', [
            'id_jadwal' => $jadwal->id,
            'keluhan'   => 'Demam tinggi',
            'id_pasien' => $lain->id,   // harus diabaikan
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.no_antrian', 1)
            ->assertJsonPath('data.keluhan', 'Demam tinggi');

        $this->assertDatabaseHas('daftar_poli', ['id_pasien' => $this->pasien->id, 'id_jadwal' => $jadwal->id]);
        $this->assertDatabaseMissing('daftar_poli', ['id_pasien' => $lain->id]);
    }

    public function test_nomor_antrian_bertambah_untuk_pasien_berikutnya(): void
    {
        $jadwal = JadwalPeriksa::factory()->create();

        $this->postJson('/api/pasien/pendaftaran', ['id_jadwal' => $jadwal->id, 'keluhan' => 'Batuk'])
            ->assertStatus(201)->assertJsonPath('data.no_antrian', 1);

        $this->masukSebagaiPasien();   // pasien lain

        $this->postJson('/api/pasien/pendaftaran', ['id_jadwal' => $jadwal->id, 'keluhan' => 'Pilek'])
            ->assertStatus(201)->assertJsonPath('data.no_antrian', 2);
    }

    public function test_validasi_pendaftaran(): void
    {
        $this->postJson('/api/pasien/pendaftaran', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_jadwal', 'keluhan']);

        $this->postJson('/api/pasien/pendaftaran', ['id_jadwal' => 99999, 'keluhan' => 'Demam'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_jadwal']);
    }

    public function test_tidak_bisa_mendaftar_dua_kali_ke_jadwal_yang_sama_sebelum_diperiksa(): void
    {
        $jadwal = JadwalPeriksa::factory()->create();
        $data   = ['id_jadwal' => $jadwal->id, 'keluhan' => 'Demam'];

        $pertama = $this->postJson('/api/pasien/pendaftaran', $data)->assertStatus(201)->json('data.id');

        $this->postJson('/api/pasien/pendaftaran', $data)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Anda sudah terdaftar di jadwal ini dan belum diperiksa.');

        // Setelah diperiksa, pasien boleh mendaftar lagi
        Periksa::factory()->create(['id_daftar_poli' => $pertama]);

        $this->postJson('/api/pasien/pendaftaran', $data)
            ->assertStatus(201)
            ->assertJsonPath('data.no_antrian', 2);
    }

    // ===== Riwayat pendaftaran =====

    public function test_riwayat_hanya_memuat_pendaftaran_milik_sendiri(): void
    {
        DaftarPoli::factory()->count(2)->create(['id_pasien' => $this->pasien->id]);
        DaftarPoli::factory()->count(3)->create();   // milik pasien lain

        $this->getJson('/api/pasien/pendaftaran')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_riwayat_berhalaman_dan_filter_status(): void
    {
        $sudah   = DaftarPoli::factory()->create(['id_pasien' => $this->pasien->id]);
        $menunggu = DaftarPoli::factory()->create(['id_pasien' => $this->pasien->id]);
        Periksa::factory()->create(['id_daftar_poli' => $sudah->id]);

        $this->getJson('/api/pasien/pendaftaran?status=menunggu')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $menunggu->id)
            ->assertJsonPath('data.0.sudah_diperiksa', false);

        $this->getJson('/api/pasien/pendaftaran?status=sudah')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $sudah->id);

        $this->getJson('/api/pasien/pendaftaran?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.last_page', 2);
    }
}