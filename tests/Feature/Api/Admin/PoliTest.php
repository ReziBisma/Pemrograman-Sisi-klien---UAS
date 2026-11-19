<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Poli;
use Tests\TestCase;

class PoliTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->masukSebagaiAdmin();
    }

    // ===== Pagination dan pencarian =====

    public function test_daftar_poli_berhalaman(): void
    {
        Poli::factory()->count(15)->create();

        $this->getJson('/api/admin/polis?per_page=5')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.last_page', 3);

        $this->getJson('/api/admin/polis?per_page=5&page=3')
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_per_page_dibatasi_maksimal_100(): void
    {
        Poli::factory()->count(3)->create();

        $this->getJson('/api/admin/polis?per_page=999')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100);
    }

    public function test_parameter_semua_mengembalikan_seluruh_data_tanpa_pagination(): void
    {
        Poli::factory()->count(12)->create();

        $this->getJson('/api/admin/polis?semua=1')
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonMissingPath('meta');
    }

    public function test_pencarian_poli_berdasarkan_nama_dan_keterangan(): void
    {
        Poli::factory()->create(['nama_poli' => 'Poli Gigi', 'keterangan' => 'Layanan gigi']);
        Poli::factory()->create(['nama_poli' => 'Poli Umum', 'keterangan' => 'Layanan dasar']);

        $this->getJson('/api/admin/polis?search=Gigi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_poli', 'Poli Gigi');

        $this->getJson('/api/admin/polis?search=dasar')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_poli', 'Poli Umum');

        $this->getJson('/api/admin/polis?search=tidak-ada')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    // ===== Create =====

    public function test_tambah_poli(): void
    {
        $this->postJson('/api/admin/polis', ['nama_poli' => 'Poli Mata', 'keterangan' => 'Layanan mata'])
            ->assertStatus(201)
            ->assertJsonPath('data.nama_poli', 'Poli Mata');

        $this->assertDatabaseHas('poli', ['nama_poli' => 'Poli Mata']);
    }

    public function test_nama_poli_wajib_diisi(): void
    {
        $this->postJson('/api/admin/polis', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama_poli']);
    }

    public function test_nama_poli_maksimal_25_karakter(): void
    {
        $this->postJson('/api/admin/polis', ['nama_poli' => str_repeat('a', 26)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama_poli']);
    }

    // ===== Read satu data =====

    public function test_lihat_satu_poli(): void
    {
        $poli = Poli::factory()->create();

        $this->getJson("/api/admin/polis/{$poli->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $poli->id);
    }

    public function test_poli_yang_tidak_ada_menghasilkan_404(): void
    {
        $this->getJson('/api/admin/polis/99999')->assertStatus(404);
    }

    // ===== Update dan Delete =====

    public function test_ubah_poli(): void
    {
        $poli = Poli::factory()->create(['nama_poli' => 'Poli Lama']);

        $this->putJson("/api/admin/polis/{$poli->id}", ['nama_poli' => 'Poli Baru', 'keterangan' => 'Baru'])
            ->assertOk()
            ->assertJsonPath('data.nama_poli', 'Poli Baru');

        $this->assertDatabaseHas('poli', ['id' => $poli->id, 'nama_poli' => 'Poli Baru']);
    }

    public function test_hapus_poli(): void
    {
        $poli = Poli::factory()->create();

        $this->deleteJson("/api/admin/polis/{$poli->id}")->assertOk();

        $this->assertDatabaseMissing('poli', ['id' => $poli->id]);
    }
}