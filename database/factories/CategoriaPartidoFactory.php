<?php

namespace Database\Factories;

use App\Models\CategoriaPartido;
use App\Models\Categoria;
use App\Models\Partido;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaPartidoFactory extends Factory
{
    protected $model = CategoriaPartido::class;

    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'partido_id' => Partido::factory(),
            'fase' => $this->faker->randomElement(['cuartos', 'semifinal', 'final']),
            'numero_partido' => $this->faker->randomElement(['QF1', 'QF2', 'QF3', 'QF4', 'SF1', 'SF2', 'F']),
            'dependencias' => null,
        ];
    }

    public function cuartos(): static
    {
        return $this->state(fn (array $attributes) => [
            'fase' => 'cuartos',
            'numero_partido' => $this->faker->randomElement(['QF1', 'QF2', 'QF3', 'QF4']),
            'dependencias' => null,
        ]);
    }

    public function semifinal(): static
    {
        return $this->state(fn (array $attributes) => [
            'fase' => 'semifinal',
            'numero_partido' => $this->faker->randomElement(['SF1', 'SF2']),
            'dependencias' => $this->faker->randomElement([
                ['ganador_QF1', 'ganador_QF2'],
                ['ganador_QF3', 'ganador_QF4']
            ]),
        ]);
    }

    public function final(): static
    {
        return $this->state(fn (array $attributes) => [
            'fase' => 'final',
            'numero_partido' => 'F',
            'dependencias' => ['ganador_SF1', 'ganador_SF2'],
        ]);
    }

    public function conDependencias(array $dependencias): static
    {
        return $this->state(fn (array $attributes) => [
            'dependencias' => $dependencias,
        ]);
    }
}
