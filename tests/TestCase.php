<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    // Login sebagai user tertentu (autentikasi Sanctum), lalu kembalikan user itu
    protected function masukSebagai(User $user): User
    {
        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    protected function masukSebagaiAdmin(): User
    {
        return $this->masukSebagai(User::factory()->admin()->create());
    }

    protected function masukSebagaiDokter(): User
    {
        return $this->masukSebagai(User::factory()->dokter()->create());
    }

    protected function masukSebagaiPasien(): User
    {
        return $this->masukSebagai(User::factory()->pasien()->create());
    }
}