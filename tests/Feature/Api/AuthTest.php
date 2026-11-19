<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuthTest extends TestCase
{
    private function dataRegistrasi(array $timpa = []): array
    {
        return array_merge([
            'nama'     => 'Budi Santoso',
            'alamat'   => 'Semarang',
            'no_ktp'   => '3374000000000001',
            'no_hp'    => '081234567890',
            'email'    => 'budi@example.com',
            'password' => 'rahasia',
        ], $timpa);
    }

    // ===== Registrasi =====

    public function test_registrasi_membuat_pasien_dengan_token_dan_no_rm(): void
    {
        $response = $this->postJson('/api/register', $this->dataRegistrasi());

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'token', 'user' => ['id', 'nama', 'email', 'role', 'no_rm']])
            ->assertJsonPath('user.role', 'pasien');

        $this->assertMatchesRegularExpression('/^\d{6}-\d{3}$/', $response->json('user.no_rm'));
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com', 'role' => 'pasien']);
    }

    public function test_registrasi_tidak_bisa_memilih_role_admin(): void
    {
        $this->postJson('/api/register', $this->dataRegistrasi(['role' => 'admin']))
            ->assertStatus(201)
            ->assertJsonPath('user.role', 'pasien');
    }

    public function test_registrasi_menolak_email_yang_sudah_dipakai(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/register', $this->dataRegistrasi())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registrasi_menolak_data_tidak_lengkap_dan_password_pendek(): void
    {
        $this->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nama', 'alamat', 'no_ktp', 'no_hp', 'email', 'password']);

        $this->postJson('/api/register', $this->dataRegistrasi(['password' => '123']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ===== Login =====

    public function test_login_berhasil_mengembalikan_token_dan_peran(): void
    {
        User::factory()->dokter()->create(['email' => 'dokter@example.com']);

        $this->postJson('/api/login', ['email' => 'dokter@example.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['message', 'token', 'role', 'user'])
            ->assertJsonPath('role', 'dokter');
    }

    public function test_login_gagal_jika_password_salah(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'salah'])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Email atau password salah.');
    }

    public function test_login_gagal_jika_email_tidak_terdaftar(): void
    {
        $this->postJson('/api/login', ['email' => 'tidak.ada@example.com', 'password' => 'password'])
            ->assertStatus(401);
    }

    public function test_login_menolak_input_kosong(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // ===== Profil dan akses =====

    public function test_endpoint_terproteksi_menolak_request_tanpa_token(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
        $this->getJson('/api/admin/polis')->assertStatus(401);
    }

    public function test_me_mengembalikan_profil_user_yang_login(): void
    {
        $user = $this->masukSebagaiPasien();

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonMissingPath('data.password');
    }

    // ===== Token (logout) =====

    public function test_logout_mencabut_token_yang_dipakai(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('uji')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    // ===== Sisa Auth =====

    public function test_login_memberi_token_berlaku_24_jam(): void // (Sisa Auth)
    {
        User::factory()->create(['email' => 'a@example.com']);

        $response = $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['expires_at']);

        $this->assertTrue(Carbon::parse($response->json('expires_at'))->greaterThan(now()->addHours(23)));
    }

    public function test_login_ulang_mencabut_token_lama(): void // (Sisa Auth)
    {
        $user       = User::factory()->create(['email' => 'a@example.com']);
        $kredensial = ['email' => 'a@example.com', 'password' => 'password'];

        $this->postJson('/api/login', $kredensial)->assertOk();
        $this->postJson('/api/login', $kredensial)->assertOk();

        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_token_kedaluwarsa_ditolak(): void // (Sisa Auth)
    {
        $user  = User::factory()->create();
        $token = $user->createToken('uji', ['*'], now()->subMinute())->plainTextToken;

        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
    }

    public function test_logout_semua_mencabut_seluruh_token(): void // (Sisa Auth)
    {
        $user  = User::factory()->create();
        $user->createToken('perangkat-1');
        $user->createToken('perangkat-2');
        $token = $user->createToken('perangkat-3')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout-semua')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }
}