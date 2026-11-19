<?php

namespace Database\Factories;

use App\Models\Poli;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    // Bawaan: pasien. Password semua user factory = "password"
    public function definition(): array
    {
        return [
            'nama'           => fake()->name(),
            'alamat'         => fake()->address(),
            'no_ktp'         => fake()->unique()->numerify('################'),
            'no_hp'          => fake()->numerify('08##########'),
            'role'           => 'pasien',
            'email'          => fake()->unique()->safeEmail(),
            'password'       => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function dokter(): static
    {
        return $this->state(fn () => [
            'role'    => 'dokter',
            'id_poli' => Poli::factory(),
        ]);
    }

    public function pasien(): static
    {
        return $this->state(fn () => ['role' => 'pasien']);
    }
}