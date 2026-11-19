<?php

namespace Tests\Feature\Api\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasienTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->masukSebagaiAdmin();
    }

    private function payload(array $timpa = []): array
    {
        return array_merge([
            'nama'     => 'Siti Aminah',
            'alamat'   => 'Semarang',
            'no_ktp'   => '3374000000000002',
            'no_hp'    => '081234567891',
            'email'    => 'siti@example.com',
            'password' => 'rahasia',
        ], $timpa);
    }

    public function test_tambah_pasien_mendapat_role_pasien_dan_no_rm(): void
    {
        $response = $this->postJson('/api/admin/pasien', $this->payload(['role' => 'admin']))
            ->assertStatus(201)
            ->assertJsonPath('data.role', 'pasien');

        $this->assertMatchesRegularExpression('/^\d{6}-\d{3}$/', $response->json('data.no_rm'));
        $this->assertTrue(Hash::check('rahasia', User::where('email', 'siti@example.com')->first()->password));
    }

    public function test_validasi_tambah_pasien(): void
    {
        User::factory()->create(['no_ktp' => '3374000000000002']);

        $this->postJson('/api/admin/pasien', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama', 'alamat', 'no_ktp', 'no_hp', 'email', 'password']);

        $this->postJson('/api/admin/pasien', $this->payload())   // no_ktp sudah dipakai
            ->assertStatus(422)
            ->assertJsonValidationErrors(['no_ktp']);
    }

    public function test_daftar_pasien_berhalaman_dan_hanya_memuat_pasien(): void
    {
        User::factory()->pasien()->count(7)->create();
        User::factory()->dokter()->count(3)->create();

        $this->getJson('/api/admin/pasien?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 7);
    }

    public function test_pencarian_pasien_berdasarkan_no_rm(): void
    {
        User::factory()->pasien()->create(['no_rm' => '202611-001']);
        User::factory()->pasien()->create(['no_rm' => '202611-002']);

        $this->getJson('/api/admin/pasien?search=202611-001')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.no_rm', '202611-001');
    }

    public function test_ubah_pasien_tanpa_password_tidak_mengganti_password(): void
    {
        $pasien = User::factory()->pasien()->create();

        $this->putJson("/api/admin/pasien/{$pasien->id}", [
            'nama'   => 'Nama Baru',
            'alamat' => 'Alamat Baru',
            'no_ktp' => $pasien->no_ktp,
            'no_hp'  => $pasien->no_hp,
            'email'  => $pasien->email,
        ])->assertOk()->assertJsonPath('data.nama', 'Nama Baru');

        $this->assertTrue(Hash::check('password', $pasien->fresh()->password));
    }

    public function test_hapus_pasien(): void
    {
        $pasien = User::factory()->pasien()->create();

        $this->deleteJson("/api/admin/pasien/{$pasien->id}")->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $pasien->id]);
    }

    public function test_dokter_tidak_bisa_diakses_lewat_endpoint_pasien(): void
    {
        $dokter = User::factory()->dokter()->create();

        $this->getJson("/api/admin/pasien/{$dokter->id}")->assertStatus(404);
        $this->deleteJson("/api/admin/pasien/{$dokter->id}")->assertStatus(404);

        $this->assertDatabaseHas('users', ['id' => $dokter->id]);
    }
}