<?php

namespace Database\Factories;

use App\Models\Partido;
use App\Models\Grupo;
use App\Models\Equipo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PartidoFactory extends Factory
{
    protected $model = Partido::class;

    public function definition(): array
    {
        return [
            'grupo_id' => Grupo::factory(),
            'equipo_local_id' => Equipo::factory(),
            'equipo_visitante_id' => Equipo::factory(),
            'resultado_local' => null,
            'resultado_visitante' => null,
            'puntos_local' => null,
            'puntos_visitante' => null,
            'fecha' => $this->faker->dateTimeBetween('now', '+1 month'),
            'completado' => false,
        ];
    }

    public function completado(): static
    {
        return $this->state(fn (array $attributes) => [
            'completado' => true,
            'resultado_local' => $this->faker->numberBetween(0, 2),
            'resultado_visitante' => $this->faker->numberBetween(0, 2),
            'puntos_local' => $this->faker->numberBetween(40, 75),
            'puntos_visitante' => $this->faker->numberBetween(40, 75),
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'completado' => false,
            'resultado_local' => null,
            'resultado_visitante' => null,
            'puntos_local' => null,
            'puntos_visitante' => null,
        ]);
    }

    public function eliminatorio(): static
    {
        return $this->state(fn (array $attributes) => [
            'grupo_id' => null,
        ]);
    }

    public function deGrupo(): static
    {
        return $this->state(fn (array $attributes) => [
            'grupo_id' => Grupo::factory(),
        ]);
    }

    public function conFecha(Carbon $fecha): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha' => $fecha,
        ]);
    }

    public function sinEquipos(): static
    {
        return $this->state(fn (array $attributes) => [
            'equipo_local_id' => null,
            'equipo_visitante_id' => null,
        ]);
    }

    public function conResultado(int $local, int $visitante): static
    {
        return $this->state(fn (array $attributes) => [
            'completado' => true,
            'resultado_local' => $local,
            'resultado_visitante' => $visitante,
            'puntos_local' => $this->faker->numberBetween(40, 75),
            'puntos_visitante' => $this->faker->numberBetween(40, 75),
        ]);
    }
}
