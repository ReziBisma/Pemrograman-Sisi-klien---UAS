<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class ThrottleTest extends TestCase
{
    public function test_login_dibatasi_lima_percobaan_per_menit(): void
    {
        User::factory()->create(['email' => 'a@example.com']);
        $salah = ['email' => 'a@example.com', 'password' => 'salah'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', $salah)->assertStatus(401);
        }

        $this->postJson('/api/login', $salah)
            ->assertStatus(429)
            ->assertJsonPath('message', 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.');
    }

    public function test_api_dibatasi_60_request_per_menit_per_pengguna(): void
    {
        $this->masukSebagaiPasien();

        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/me')->assertOk();
        }

        $this->getJson('/api/me')
            ->assertStatus(429)
            ->assertJsonPath('message', 'Terlalu banyak permintaan. Coba lagi dalam beberapa saat.');
    }

    public function test_respons_membawa_header_sisa_kuota(): void
    {
        $this->masukSebagaiPasien();

        $this->getJson('/api/me')
            ->assertOk()
            ->assertHeader('X-RateLimit-Limit', '60')
            ->assertHeader('X-RateLimit-Remaining', '59');
    }
}