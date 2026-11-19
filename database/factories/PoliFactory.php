<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PoliFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_poli'  => fake()->unique()->bothify('Poli ???'),
            'keterangan' => fake()->sentence(),
        ];
    }
}