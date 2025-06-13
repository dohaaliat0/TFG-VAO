<?php
namespace App\View\Components;

use App\Models\Torneo;
use Illuminate\View\Component;

class TournamentStats extends Component
{
    public $torneo;
    public $stats;

    public function __construct(Torneo $torneo)
    {
        $this->torneo = $torneo;
        $this->stats = $this->calculateStats();
    }

    private function calculateStats()
    {
        $totalEquipos = 0;
        $totalPartidos = 0;
        $partidosCompletados = 0;
        $totalSets = 0;

        foreach ($this->torneo->grupos as $grupo) {
            $totalEquipos += $grupo->equipos->count();
            $totalPartidos += $grupo->partidos->count();
            $partidosCompletados += $grupo->partidos->where('completado', true)->count();

            foreach ($grupo->partidos as $partido) {
                $totalSets += $partido->sets->count();
            }
        }

        $porcentajeCompletado = $totalPartidos > 0
            ? round(($partidosCompletados / $totalPartidos) * 100)
            : 0;

        return [
            'equipos' => $totalEquipos,
            'partidos' => [
                'total' => $totalPartidos,
                'completados' => $partidosCompletados,
                'pendientes' => $totalPartidos - $partidosCompletados,
                'porcentaje_completado' => $porcentajeCompletado,
            ],
            'sets' => $totalSets,
            'grupos' => $this->torneo->grupos->count(),
            'categorias' => $this->torneo->categorias->count(),
        ];
    }

    public function render()
    {
        return view('components.tournament-stats');
    }
}
