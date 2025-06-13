<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Torneo>
 */
class TorneoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaInicio = fake()->dateTimeBetween('now', '+2 months');
        $fechaFin = fake()->dateTimeBetween($fechaInicio, '+1 month');
        
        return [
            'nombre' => 'Torneo ' . fake()->words(2, true),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'num_grupos' => fake()->numberBetween(2, 6),
            'num_categorias' => fake()->numberBetween(1, 3),
            'estado' => fake()->randomElement(['preparacion', 'en_curso', 'finalizado']),
        ];
    }
    
    /**
     * Indicate that the tournament is in preparation.
     */
    public function enPreparacion(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'preparacion',
        ]);
    }
    
    /**
     * Indicate that the tournament is in progress.
     */
    public function enCurso(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'en_curso',
        ]);
    }
    
    /**
     * Indicate that the tournament is finished.
     */
    public function finalizado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'finalizado',
        ]);
    }
}