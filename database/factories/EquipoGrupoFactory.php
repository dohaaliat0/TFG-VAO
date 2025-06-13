<?php

namespace Database\Factories;

use App\Models\Equipo;
use App\Models\Grupo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EquipoGrupo>
 */
class EquipoGrupoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'equipo_id' => Equipo::factory(),
            'grupo_id' => Grupo::factory(),
            'puntos' => fake()->numberBetween(0, 20),
            'partidos_jugados' => fake()->numberBetween(0, 10),
            'partidos_ganados_2_0' => fake()->numberBetween(0, 5),
            'partidos_ganados_2_1' => fake()->numberBetween(0, 5),
            'partidos_perdidos_0_2' => fake()->numberBetween(0, 5),
            'partidos_perdidos_1_2' => fake()->numberBetween(0, 5),
            'no_presentados' => fake()->numberBetween(0, 2),
            'sets_favor' => fake()->numberBetween(0, 20),
            'sets_contra' => fake()->numberBetween(0, 20),
            'puntos_favor' => fake()->numberBetween(0, 200),
            'puntos_contra' => fake()->numberBetween(0, 200),
            'posicion' => fake()->numberBetween(1, 10),
            'eliminado' => fake()->boolean(10), // 10% chance of being eliminated
        ];
    }
}