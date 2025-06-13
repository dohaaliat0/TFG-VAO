<?php
namespace App\Http\Controllers;

use App\Models\Torneo;
use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index(Torneo $torneo)
    {
        $horarios = $torneo->horarios()->orderBy('fecha')->get();
        $pistas = $horarios->pluck('pista')->unique();
        return view('horarios.index', compact('torneo', 'horarios', 'pistas'));
    }

    public function create(Torneo $torneo)
    {
        return view('horarios.create', compact('torneo'));
    }

    public function store(Request $request, Torneo $torneo)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'intervalo' => 'required|integer|min:15',
            'pista' => 'required|string|max:255',
        ]);
        $horaInicio = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['fecha'] . ' ' . $validated['hora_inicio']);
        $horaFin = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['fecha'] . ' ' . $validated['hora_fin']);
        $intervalo = (int) $validated['intervalo'];
        $horarioActual = clone $horaInicio;
        $horariosCreados = 0;

        while ($horarioActual < $horaFin) {
            $torneo->horarios()->create([
                'fecha' => $horarioActual->copy(),
                'pista' => $validated['pista']
            ]);

            $horarioActual->addMinutes($intervalo);
            $horariosCreados++;
        }

        return redirect()->route('horarios.index', $torneo)
            ->with('success', "Se crearon {$horariosCreados} horarios correctamente para la pista {$validated['pista']}");
    }

    public function destroy(Horario $horario)
    {
        $torneo = $horario->torneo;

        if ($horario->partidos()->exists()) {
            return redirect()->route('horarios.index', $torneo)
                ->with('error', 'No se puede eliminar el horario porque tiene partidos asignados');
        }

        $horario->delete();
        return redirect()->route('horarios.index', $torneo)
            ->with('success', 'Horario eliminado correctamente');
    }

    public function calendario(Torneo $torneo)
    {
        $horarios = $torneo->horarios()
            ->with(['partidos.equipoLocal', 'partidos.equipoVisitante', 'partidos.grupo'])
            ->orderBy('fecha')
            ->get();
        $pistas = $horarios->pluck('pista')->unique()->sort();
        $fechas = $horarios->pluck('fecha')->map(function ($fecha) {
            return $fecha->format('Y-m-d');
        })->unique()->sort();
        return view('horarios.calendario', compact('torneo', 'horarios', 'pistas', 'fechas'));
    }
}
