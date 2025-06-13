<?php

namespace Database\Factories;

use App\Models\Torneo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Horario>
 */
class HorarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'torneo_id' => Torneo::factory(),
            'fecha' => fake()->dateTimeBetween('now', '+1 month'),
            'pista' => 'Pista ' . fake()->numberBetween(1, 5),
        ];
    }
}