<?php

namespace Database\Factories;

use App\Models\Partido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Set>
 */
class SetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partido_id' => Partido::factory(),
            'numero_set' => fake()->numberBetween(1, 3),
            'puntos_local' => fake()->numberBetween(15, 25),
            'puntos_visitante' => fake()->numberBetween(0, 25),
        ];
    }
}