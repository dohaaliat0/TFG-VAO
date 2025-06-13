<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Torneo;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        return [
            'torneo_id' => Torneo::factory(),
            'nombre' => 'Categoría ' . $this->faker->randomElement(['A', 'B', 'C', '1', '2', '3']),
            'descripcion' => $this->faker->optional()->sentence(),
        ];
    }

    public function conNombre(string $nombre): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => $nombre,
        ]);
    }

    public function conDescripcion(string $descripcion): static
    {
        return $this->state(fn (array $attributes) => [
            'descripcion' => $descripcion,
        ]);
    }

    public function paraTorneo(Torneo $torneo): static
    {
        return $this->state(fn (array $attributes) => [
            'torneo_id' => $torneo->id,
        ]);
    }
}
