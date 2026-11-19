<?php

namespace Tests\Feature\Api\Dokter;

use App\Models\JadwalPeriksa;
use App\Models\User;
use Tests\TestCase;

class JadwalPeriksaTest extends TestCase
{
    private User $dokter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dokter = $this->masukSebagaiDokter();
    }

    private function payload(array $timpa = []): array
    {
        return array_merge([
            'hari'        => 'Senin',
            'jam_mulai'   => '08:00',
            'jam_selesai' => '12:00',
            'status'      => 'Aktif',
        ], $timpa);
    }

    // ===== Create =====

    public function test_tambah_jadwal_memakai_id_dokter_dari_login(): void
    {
        $lain = User::factory()->dokter()->create();

        $this->postJson('/api/dokter/jadwal-periksa', $this->payload(['id_dokter' => $lain->id]))
            ->assertStatus(201)
            ->assertJsonPath('data.hari', 'Senin')
            ->assertJsonPath('data.jam_mulai', '08:00')
            ->assertJsonPath('data.status', 'Aktif');

        $this->assertDatabaseHas('jadwal_periksa', ['id_dokter' => $this->dokter->id, 'hari' => 'Senin']);
        $this->assertDatabaseMissing('jadwal_periksa', ['id_dokter' => $lain->id]);
    }

    public function test_status_bawaan_adalah_tidak_aktif(): void
    {
        $data = $this->payload();
        unset($data['status']);

        $this->postJson('/api/dokter/jadwal-periksa', $data)
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'Tidak Aktif');
    }

    public function test_validasi_jadwal(): void
    {
        $this->postJson('/api/dokter/jadwal-periksa', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['hari', 'jam_mulai', 'jam_selesai']);

        // jam selesai harus setelah jam mulai
        $this->postJson('/api/dokter/jadwal-periksa', $this->payload(['jam_mulai' => '12:00', 'jam_selesai' => '08:00']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['jam_selesai']);

        // hari harus salah satu dari Senin sampai Minggu
        $this->postJson('/api/dokter/jadwal-periksa', $this->payload(['hari' => 'Hari Libur']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['hari']);

        // format jam harus HH:MM
        $this->postJson('/api/dokter/jadwal-periksa', $this->payload(['jam_mulai' => '8 pagi']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['jam_mulai']);

        // status hanya Aktif atau Tidak Aktif
        $this->postJson('/api/dokter/jadwal-periksa', $this->payload(['status' => 'Entah']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ===== Read: milik sendiri, urutan, pagination, filter =====

    public function test_daftar_hanya_memuat_jadwal_milik_dokter_yang_login(): void
    {
        JadwalPeriksa::factory()->count(2)->create(['id_dokter' => $this->dokter->id]);
        JadwalPeriksa::factory()->count(3)->create();   // milik dokter lain

        $this->getJson('/api/dokter/jadwal-periksa')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_jadwal_diurutkan_dari_senin_sampai_minggu(): void
    {
        foreach (['Rabu', 'Senin', 'Minggu'] as $hari) {
            JadwalPeriksa::factory()->create(['id_dokter' => $this->dokter->id, 'hari' => $hari]);
        }

        $this->getJson('/api/dokter/jadwal-periksa')
            ->assertOk()
            ->assertJsonPath('data.0.hari', 'Senin')
            ->assertJsonPath('data.1.hari', 'Rabu')
            ->assertJsonPath('data.2.hari', 'Minggu');
    }

    public function test_jadwal_berhalaman(): void
    {
        JadwalPeriksa::factory()->count(7)->create(['id_dokter' => $this->dokter->id]);

        $this->getJson('/api/dokter/jadwal-periksa?per_page=3')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_filter_jadwal_berdasarkan_hari_dan_status(): void
    {
        JadwalPeriksa::factory()->create(['id_dokter' => $this->dokter->id, 'hari' => 'Senin', 'status' => 'Aktif']);
        JadwalPeriksa::factory()->create(['id_dokter' => $this->dokter->id, 'hari' => 'Senin', 'status' => 'Tidak Aktif']);
        JadwalPeriksa::factory()->create(['id_dokter' => $this->dokter->id, 'hari' => 'Selasa', 'status' => 'Aktif']);

        $this->getJson('/api/dokter/jadwal-periksa?hari=Senin')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/dokter/jadwal-periksa?status=Aktif')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/dokter/jadwal-periksa?hari=Senin&status=Aktif')->assertOk()->assertJsonCount(1, 'data');
    }

    // ===== Update dan Delete =====

    public function test_ubah_jadwal(): void
    {
        $jadwal = JadwalPeriksa::factory()->create(['id_dokter' => $this->dokter->id, 'status' => 'Tidak Aktif']);

        $this->putJson("/api/dokter/jadwal-periksa/{$jadwal->id}", $this->payload(['hari' => 'Jumat', 'status' => 'Aktif']))
            ->assertOk()
            ->assertJsonPath('data.hari', 'Jumat')
            ->assertJsonPath('data.status', 'Aktif');

        $this->assertDatabaseHas('jadwal_periksa', ['id' => $jadwal->id, 'hari' => 'Jumat', 'status' => 'Aktif']);
    }

    public function test_ubah_tanpa_status_tidak_mengganti_status(): void
    {
        $jadwal = JadwalPeriksa::factory()->create(['id_dokter' => $this->dokter->id, 'status' => 'Aktif']);

        $data = $this->payload(['hari' => 'Kamis']);
        unset($data['status']);

        $this->putJson("/api/dokter/jadwal-periksa/{$jadwal->id}", $data)->assertOk();

        $this->assertSame('Aktif', $jadwal->fresh()->status);
    }

    public function test_hapus_jadwal(): void
    {
        $jadwal = JadwalPeriksa::factory()->create(['id_dokter' => $this->dokter->id]);

        $this->deleteJson("/api/dokter/jadwal-periksa/{$jadwal->id}")->assertOk();

        $this->assertDatabaseMissing('jadwal_periksa', ['id' => $jadwal->id]);
    }

    // ===== Kepemilikan: dokter tidak boleh menyentuh jadwal dokter lain =====

    public function test_jadwal_dokter_lain_tidak_bisa_dilihat_diubah_atau_dihapus(): void
    {
        $milikLain = JadwalPeriksa::factory()->create(['hari' => 'Selasa']);

        $this->getJson("/api/dokter/jadwal-periksa/{$milikLain->id}")->assertStatus(404);
        $this->putJson("/api/dokter/jadwal-periksa/{$milikLain->id}", $this->payload(['hari' => 'Jumat']))->assertStatus(404);
        $this->deleteJson("/api/dokter/jadwal-periksa/{$milikLain->id}")->assertStatus(404);

        $this->assertDatabaseHas('jadwal_periksa', ['id' => $milikLain->id, 'hari' => 'Selasa']);
    }
}