<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ObatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_obat' => fake()->unique()->bothify('Obat ????'),
            'kemasan'   => fake()->randomElement(['Strip', 'Botol', 'Tube']),
            'harga'     => fake()->numberBetween(1000, 50000),
        ];
    }
}