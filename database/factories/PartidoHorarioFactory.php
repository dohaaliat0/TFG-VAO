<?php

namespace Database\Factories;

use App\Models\Horario;
use App\Models\Partido;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartidoHorario>
 */
class PartidoHorarioFactory extends Factory
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
            'horario_id' => Horario::factory(),
        ];
    }
}