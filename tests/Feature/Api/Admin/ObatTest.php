<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Obat;
use Tests\TestCase;

class ObatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->masukSebagaiAdmin();
    }

    public function test_daftar_obat_berhalaman_dan_dapat_dicari(): void
    {
        Obat::factory()->count(8)->create();
        Obat::factory()->create(['nama_obat' => 'Paracetamol', 'kemasan' => 'Strip']);

        $this->getJson('/api/admin/obat?per_page=4')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('meta.total', 9);

        $this->getJson('/api/admin/obat?search=parace')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_obat', 'Paracetamol');
    }

    public function test_filter_rentang_harga(): void
    {
        Obat::factory()->create(['nama_obat' => 'Murah', 'harga' => 2000]);
        Obat::factory()->create(['nama_obat' => 'Sedang', 'harga' => 8000]);
        Obat::factory()->create(['nama_obat' => 'Mahal', 'harga' => 30000]);

        $this->getJson('/api/admin/obat?harga_min=5000&harga_max=10000')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama_obat', 'Sedang');

        $this->getJson('/api/admin/obat?harga_min=5000')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_tambah_obat(): void
    {
        $this->postJson('/api/admin/obat', ['nama_obat' => 'Amoxicillin', 'kemasan' => 'Strip', 'harga' => 12000])
            ->assertStatus(201)
            ->assertJsonPath('data.nama_obat', 'Amoxicillin')
            ->assertJsonPath('data.harga', 12000);

        $this->assertDatabaseHas('obat', ['nama_obat' => 'Amoxicillin', 'harga' => 12000]);
    }

    public function test_validasi_obat(): void
    {
        $this->postJson('/api/admin/obat', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama_obat', 'harga']);

        $this->postJson('/api/admin/obat', ['nama_obat' => 'X', 'harga' => 'murah'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['harga']);

        $this->postJson('/api/admin/obat', ['nama_obat' => 'X', 'harga' => -1])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['harga']);

        $this->postJson('/api/admin/obat', ['nama_obat' => 'X', 'harga' => 1000, 'kemasan' => str_repeat('a', 36)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['kemasan']);
    }

    public function test_ubah_obat(): void
    {
        $obat = Obat::factory()->create(['harga' => 1000]);

        $this->putJson("/api/admin/obat/{$obat->id}", ['nama_obat' => $obat->nama_obat, 'harga' => 2500])
            ->assertOk()
            ->assertJsonPath('data.harga', 2500);

        $this->assertDatabaseHas('obat', ['id' => $obat->id, 'harga' => 2500]);
    }

    public function test_hapus_obat_dan_obat_tidak_ditemukan(): void
    {
        $obat = Obat::factory()->create();

        $this->deleteJson("/api/admin/obat/{$obat->id}")->assertOk();
        $this->assertDatabaseMissing('obat', ['id' => $obat->id]);

        $this->getJson("/api/admin/obat/{$obat->id}")->assertStatus(404);
    }
}