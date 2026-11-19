<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    public function test_peran_yang_salah_mendapat_403(): void
    {
        $kasus = [
            // [peran yang login, endpoint yang BUKAN haknya]
            ['pasien', '/api/admin/polis'],
            ['dokter', '/api/admin/polis'],
            ['admin',  '/api/dokter/jadwal-periksa'],
            ['pasien', '/api/dokter/jadwal-periksa'],
            ['admin',  '/api/pasien/transaksi'],
            ['dokter', '/api/pasien/transaksi'],
        ];

        foreach ($kasus as [$peran, $url]) {
            $this->masukSebagai(User::factory()->state(['role' => $peran])->create());

            $this->getJson($url)->assertStatus(403);
        }
    }

    public function test_peran_yang_benar_mendapat_200(): void
    {
        $this->masukSebagaiAdmin();
        $this->getJson('/api/admin/polis')->assertOk();

        $this->masukSebagaiDokter();
        $this->getJson('/api/dokter/jadwal-periksa')->assertOk();

        $this->masukSebagaiPasien();
        $this->getJson('/api/pasien/transaksi')->assertOk();
    }

    public function test_semua_grup_menolak_request_tanpa_login(): void
    {
        foreach (['/api/admin/polis', '/api/dokter/jadwal-periksa', '/api/pasien/transaksi'] as $url) {
            $this->getJson($url)->assertStatus(401);
        }
    }
}