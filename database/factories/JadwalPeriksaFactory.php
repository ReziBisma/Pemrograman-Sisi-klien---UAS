<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JadwalPeriksaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_dokter'   => User::factory()->dokter(),
            'hari'        => fake()->randomElement(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']),
            'jam_mulai'   => '08:00',
            'jam_selesai' => '12:00',
            'status'      => 'Aktif',
        ];
    }
}