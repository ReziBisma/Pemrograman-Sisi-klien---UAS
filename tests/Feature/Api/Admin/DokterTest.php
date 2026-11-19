<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Poli;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DokterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->masukSebagaiAdmin();
    }

    private function payload(Poli $poli, array $timpa = []): array
    {
        return array_merge([
            'nama'     => 'dr. Budi',
            'alamat'   => 'Semarang',
            'no_ktp'   => '3374000000000001',
            'no_hp'    => '081234567890',
            'id_poli'  => $poli->id,
            'email'    => 'budi@example.com',
            'password' => 'rahasia',
        ], $timpa);
    }

    private function payloadUbah(User $dokter, array $timpa = []): array
    {
        return array_merge([
            'nama'    => 'Nama Baru',
            'alamat'  => 'Alamat Baru',
            'no_ktp'  => $dokter->no_ktp,
            'no_hp'   => $dokter->no_hp,
            'id_poli' => $dokter->id_poli,
            'email'   => $dokter->email,
        ], $timpa);
    }

    // ===== Create =====

    public function test_tambah_dokter_memaksa_role_dokter_dan_menghash_password(): void
    {
        $poli = Poli::factory()->create();

        $this->postJson('/api/admin/dokter', $this->payload($poli, ['role' => 'admin']))
            ->assertStatus(201)
            ->assertJsonPath('data.role', 'dokter')
            ->assertJsonPath('data.poli.id', $poli->id)
            ->assertJsonMissingPath('data.password');

        $dokter = User::where('email', 'budi@example.com')->first();
        $this->assertSame('dokter', $dokter->role);
        $this->assertNotSame('rahasia', $dokter->password);
        $this->assertTrue(Hash::check('rahasia', $dokter->password));
    }

    public function test_validasi_tambah_dokter(): void
    {
        $poli = Poli::factory()->create();
        User::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/admin/dokter', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama', 'alamat', 'no_ktp', 'no_hp', 'id_poli', 'email', 'password']);

        $this->postJson('/api/admin/dokter', $this->payload($poli))   // email sudah dipakai
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/admin/dokter', $this->payload($poli, ['email' => 'baru@example.com', 'id_poli' => 99999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_poli']);
    }

    // ===== Read: pagination, pencarian, filter =====

    public function test_daftar_dokter_berhalaman(): void
    {
        User::factory()->dokter()->count(12)->create();

        $this->getJson('/api/admin/dokter?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 12);
    }

    public function test_pencarian_dokter_tidak_memuat_pasien(): void
    {
        User::factory()->dokter()->create(['nama' => 'Budi Dokter']);
        User::factory()->pasien()->create(['nama' => 'Budi Pasien']);

        $this->getJson('/api/admin/dokter?search=Budi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.role', 'dokter');
    }

    public function test_filter_dokter_berdasarkan_poli(): void
    {
        $a = User::factory()->dokter()->create();
        User::factory()->dokter()->create();

        $this->getJson('/api/admin/dokter?id_poli=' . $a->id_poli)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $a->id);
    }

    // ===== Update dan Delete =====

    public function test_ubah_dokter_tanpa_password_tidak_mengganti_password(): void
    {
        $dokter = User::factory()->dokter()->create();

        $this->putJson("/api/admin/dokter/{$dokter->id}", $this->payloadUbah($dokter))
            ->assertOk()
            ->assertJsonPath('data.nama', 'Nama Baru');

        $this->assertTrue(Hash::check('password', $dokter->fresh()->password));   // password lama tetap
    }

    public function test_ubah_dokter_dengan_password_baru(): void
    {
        $dokter = User::factory()->dokter()->create();

        $this->putJson("/api/admin/dokter/{$dokter->id}", $this->payloadUbah($dokter, ['password' => 'barubaru']))
            ->assertOk();

        $this->assertTrue(Hash::check('barubaru', $dokter->fresh()->password));
    }

    public function test_hapus_dokter(): void
    {
        $dokter = User::factory()->dokter()->create();

        $this->deleteJson("/api/admin/dokter/{$dokter->id}")->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $dokter->id]);
    }

    // ===== Pemisahan peran =====

    public function test_pasien_tidak_bisa_diakses_lewat_endpoint_dokter(): void
    {
        $pasien = User::factory()->pasien()->create();

        $this->getJson("/api/admin/dokter/{$pasien->id}")->assertStatus(404);
        $this->putJson("/api/admin/dokter/{$pasien->id}", ['nama' => 'x'])->assertStatus(404);
        $this->deleteJson("/api/admin/dokter/{$pasien->id}")->assertStatus(404);

        $this->assertDatabaseHas('users', ['id' => $pasien->id]);
    }
}