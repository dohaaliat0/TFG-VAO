<?php

namespace Database\Factories;

use App\Models\EquipoCategoria;
use App\Models\EquipoGrupo;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipoCategoriaFactory extends Factory
{
    protected $model = EquipoCategoria::class;

    public function definition(): array
    {
        return [
            'equipo_grupo_id' => EquipoGrupo::factory(),
            'categoria_id' => Categoria::factory(),
            'posicion' => $this->faker->numberBetween(1, 8),
        ];
    }

    public function enPosicion(int $posicion): static
    {
        return $this->state(fn (array $attributes) => [
            'posicion' => $posicion,
        ]);
    }

    public function paraCategoria(Categoria $categoria): static
    {
        return $this->state(fn (array $attributes) => [
            'categoria_id' => $categoria->id,
        ]);
    }

    public function paraEquipoGrupo(EquipoGrupo $equipoGrupo): static
    {
        return $this->state(fn (array $attributes) => [
            'equipo_grupo_id' => $equipoGrupo->id,
        ]);
    }
}
