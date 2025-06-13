<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use App\Models\Grupo;
use App\Models\Horario;
use App\Models\Set;
use App\Models\Torneo;
use App\Models\Equipo;
use App\Models\EquipoGrupo;
use App\Models\CategoriaPartido;
use App\Http\Controllers\GrupoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartidoController extends Controller
{

    public function index(Request $request)
    {
        $query = Partido::with([
            'equipoLocal',
            'equipoVisitante',
            'grupo',
            'grupo.torneo',
            'categoriaPartido',
            'categoriaPartido.categoria',
            'categoriaPartido.categoria.torneo'
        ]);

        if ($request->has('torneo_id')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('grupo', function($subQ) use ($request) {
                    $subQ->where('torneo_id', $request->torneo_id);
                })->orWhereHas('categoriaPartido.categoria', function($subQ) use ($request) {
                    $subQ->where('torneo_id', $request->torneo_id);
                });
            });
        }

        if ($request->has('tipo')) {
            if ($request->tipo === 'grupo') {
                $query->whereNotNull('grupo_id');
            } elseif ($request->tipo === 'eliminatorio') {
                $query->whereNotNull('categoria_partido_id');
            }
        }

        $partidos = $query->orderBy('fecha', 'asc')->get();
        $torneos = Torneo::orderBy('nombre')->get();

        return view('partidos.index', compact('partidos', 'torneos', 'request'));
    }

    public function show(Partido $partido)
    {
        $partido->load([
            'equipoLocal',
            'equipoVisitante',
            'sets',
            'grupo',
            'grupo.torneo',
            'categoriaPartido',
            'categoriaPartido.categoria',
            'categoriaPartido.categoria.torneo'
        ]);

        return view('partidos.show', compact('partido'));
    }

    public function store(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'equipo_local_id' => 'required|exists:equipos,id',
            'equipo_visitante_id' => 'required|exists:equipos,id|different:equipo_local_id',
        ]);

        $equipoLocalEnGrupo = $grupo->equipos()->where('equipo_id', $validated['equipo_local_id'])->exists();
        $equipoVisitanteEnGrupo = $grupo->equipos()->where('equipo_id', $validated['equipo_visitante_id'])->exists();

        if (!$equipoLocalEnGrupo || !$equipoVisitanteEnGrupo) {
            return redirect()->back()->with('error', 'Ambos equipos deben pertenecer al grupo');
        }

        $partidoExiste = $grupo->partidos()
            ->where(function($query) use ($validated) {
                $query->where('equipo_local_id', $validated['equipo_local_id'])
                    ->where('equipo_visitante_id', $validated['equipo_visitante_id']);
            })
            ->orWhere(function($query) use ($validated) {
                $query->where('equipo_local_id', $validated['equipo_visitante_id'])
                    ->where('equipo_visitante_id', $validated['equipo_local_id']);
            })
            ->exists();

        if ($partidoExiste) {
            return redirect()->back()->with('error', 'Este partido ya existe');
        }

        $partido = $grupo->partidos()->create([
            'equipo_local_id' => $validated['equipo_local_id'],
            'equipo_visitante_id' => $validated['equipo_visitante_id'],
            'completado' => false
        ]);

        return redirect()->route('partidos.show', $partido)
            ->with('success', 'Partido creado correctamente');
    }

    public function update(Request $request, Partido $partido)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'resultado_local' => 'required|integer|min:0|max:2',
                'resultado_visitante' => 'required|integer|min:0|max:2',
                'completado' => 'boolean',
                'sets' => 'required|array',
                'sets.*.numero_set' => 'required|integer|min:1|max:3',
                'sets.*.puntos_local' => 'required|integer|min:0',
                'sets.*.puntos_visitante' => 'required|integer|min:0',
            ]);

            $setsGanadosLocal = 0;
            $setsGanadosVisitante = 0;
            $puntosLocalTotal = 0;
            $puntosVisitanteTotal = 0;

            foreach ($validated['sets'] as $setData) {
                $puntosLocal = $setData['puntos_local'];
                $puntosVisitante = $setData['puntos_visitante'];

                $puntosLocalTotal += $puntosLocal;
                $puntosVisitanteTotal += $puntosVisitante;

                if ($puntosLocal > $puntosVisitante && $puntosLocal > 0) {
                    $setsGanadosLocal++;
                } elseif ($puntosVisitante > $puntosLocal && $puntosVisitante > 0) {
                    $setsGanadosVisitante++;
                }
            }

            $partido->update([
                'resultado_local' => $setsGanadosLocal,
                'resultado_visitante' => $setsGanadosVisitante,
                'puntos_local' => $puntosLocalTotal,
                'puntos_visitante' => $puntosVisitanteTotal,
                'completado' => $request->has('completado')
            ]);

            foreach ($validated['sets'] as $setData) {
                Set::updateOrCreate(
                    [
                        'partido_id' => $partido->id,
                        'numero_set' => $setData['numero_set']
                    ],
                    [
                        'puntos_local' => $setData['puntos_local'],
                        'puntos_visitante' => $setData['puntos_visitante']
                    ]
                );
            }

            if ($request->has('completado')) {
                if ($partido->grupo_id) {
                    $this->actualizarEstadisticasGrupo($partido);
                }

                if ($partido->categoria_partido_id) {
                    $this->actualizarSiguientesPartidos($partido);
                }
            }

            DB::commit();
            return redirect()->route('partidos.show', $partido)
                ->with('success', 'Partido actualizado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar partido: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al actualizar el partido: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Partido $partido)
    {
        try {
            DB::beginTransaction();

            $grupoId = $partido->grupo_id;
            $categoriaId = null;

            if ($partido->categoria_partido_id) {
                $categoriaPartido = $partido->categoriaPartido;
                if ($categoriaPartido) {
                    $categoriaId = $categoriaPartido->categoria_id;
                }
            }

            $partido->sets()->delete();

            $partido->delete();

            DB::commit();

            if ($grupoId) {
                return redirect()->route('grupos.show', $grupoId)
                    ->with('success', 'Partido eliminado correctamente');
            } elseif ($categoriaId) {
                return redirect()->route('categorias.show', $categoriaId)
                    ->with('success', 'Partido eliminado correctamente');
            } else {
                return redirect()->route('partidos.index')
                    ->with('success', 'Partido eliminado correctamente');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar partido: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar el partido: ' . $e->getMessage());
        }
    }

    public function asignarHorario(Request $request, Partido $partido)
    {
        $validated = $request->validate([
            'horario_id' => 'required|exists:horarios,id',
        ]);

        $horario = Horario::findOrFail($validated['horario_id']);

        $torneoId = null;
        if ($partido->grupo_id) {
            $torneoId = $partido->grupo->torneo_id;
        } elseif ($partido->categoria_partido_id) {
            $torneoId = $partido->categoriaPartido->categoria->torneo_id;
        }

        if (!$torneoId || $horario->torneo_id != $torneoId) {
            return redirect()->back()->with('error', 'El horario debe pertenecer al mismo torneo');
        }

        $partidoExistente = Partido::where('fecha', $horario->fecha)
            ->where('id', '!=', $partido->id)
            ->first();

        if ($partidoExistente) {
            return redirect()->back()->with('error', 'Ya hay un partido asignado a este horario');
        }

        $partido->update(['fecha' => $horario->fecha]);

        return redirect()->route('partidos.show', $partido)
            ->with('success', 'Horario asignado correctamente');
    }

    public function registrarResultadoForm(Partido $partido)
    {
        if ($partido->completado) {
            return redirect()->route('partidos.show', $partido)
                ->with('error', 'Este partido ya fue completado. No se puede modificar su resultado.');
        }

        $partido->load([
            'equipoLocal',
            'equipoVisitante',
            'sets',
            'grupo',
            'grupo.torneo',
            'categoriaPartido',
            'categoriaPartido.categoria',
            'categoriaPartido.categoria.torneo'
        ]);

        return view('partidos.registrar-resultado', compact('partido'));
    }

    public function registrarResultado(Request $request, Partido $partido)
    {
        if ($partido->completado) {
            return redirect()->route('partidos.show', $partido)
                ->with('error', 'Este partido ya fue completado. No se puede modificar su resultado.');
        }

        try {
            DB::beginTransaction();

            if (!$partido->equipo_local_id || !$partido->equipo_visitante_id) {
                return redirect()->back()->with('error', 'No se puede registrar resultado porque faltan equipos asignados.');
            }

            $request->validate([
                'sets' => 'required|array',
                'sets.*.numero_set' => 'required|integer|min:1|max:3',
                'sets.*.puntos_local' => 'required|integer|min:0|max:99',
                'sets.*.puntos_visitante' => 'required|integer|min:0|max:99',
            ]);

            $sets = $request->sets;
            $setsGanadosLocal = 0;
            $setsGanadosVisitante = 0;
            $puntosLocalTotal = 0;
            $puntosVisitanteTotal = 0;

            $partido->sets()->delete();

            foreach ($sets as $setData) {
                $puntosLocal = (int)$setData['puntos_local'];
                $puntosVisitante = (int)$setData['puntos_visitante'];
                $numeroSet = (int)$setData['numero_set'];

                if ($puntosLocal > 0 && $puntosVisitante > 0 && $puntosLocal === $puntosVisitante) {
                    throw new \Exception('Los sets no pueden terminar en empate.');
                }

                Set::create([
                    'partido_id' => $partido->id,
                    'numero_set' => $numeroSet,
                    'puntos_local' => $puntosLocal,
                    'puntos_visitante' => $puntosVisitante,
                ]);

                $puntosLocalTotal += $puntosLocal;
                $puntosVisitanteTotal += $puntosVisitante;

                if ($puntosLocal > $puntosVisitante) {
                    $setsGanadosLocal++;
                } elseif ($puntosVisitante > $puntosLocal) {
                    $setsGanadosVisitante++;
                }
            }

            if ($setsGanadosLocal != 2 && $setsGanadosVisitante != 2) {
                throw new \Exception('Uno de los equipos debe ganar exactamente 2 sets.');
            }

            $partido->update([
                'resultado_local' => $setsGanadosLocal,
                'resultado_visitante' => $setsGanadosVisitante,
                'puntos_local' => $puntosLocalTotal,
                'puntos_visitante' => $puntosVisitanteTotal,
                'completado' => true
            ]);

            if ($partido->grupo_id) {
                $this->actualizarEstadisticasGrupo($partido);
            }

            if ($partido->categoria_partido_id) {
                $this->actualizarSiguientesPartidos($partido);
            }

            DB::commit();

            return redirect()->route('partidos.show', $partido)
                ->with('success', 'Resultado registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar resultado: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al registrar el resultado: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function actualizarEstadisticas(Partido $partido)
    {
        if (!$partido->grupo_id) {
            return;
        }

        $this->actualizarEstadisticasGrupo($partido);
    }

    private function actualizarEstadisticasGrupo(Partido $partido)
    {
        if (!$partido->grupo_id) {
            return;
        }

        $equipoLocalGrupo = EquipoGrupo::where('grupo_id', $partido->grupo_id)
            ->where('equipo_id', $partido->equipo_local_id)
            ->first();

        if ($equipoLocalGrupo) {
            $this->actualizarEstadisticasEquipo($equipoLocalGrupo, $partido->grupo_id);
        }

        $equipoVisitanteGrupo = EquipoGrupo::where('grupo_id', $partido->grupo_id)
            ->where('equipo_id', $partido->equipo_visitante_id)
            ->first();

        if ($equipoVisitanteGrupo) {
            $this->actualizarEstadisticasEquipo($equipoVisitanteGrupo, $partido->grupo_id);
        }

        $this->recalcularPosicionesGrupo($partido->grupo_id);
    }

    private function actualizarEstadisticasEquipo($equipoGrupo, $grupoId)
    {
        $partidos = Partido::where('grupo_id', $grupoId)
            ->where('completado', true)
            ->where(function($query) use ($equipoGrupo) {
                $query->where('equipo_local_id', $equipoGrupo->equipo_id)
                    ->orWhere('equipo_visitante_id', $equipoGrupo->equipo_id);
            })
            ->get();

        $estadisticas = [
            'puntos' => 0,
            'partidos_jugados' => 0,
            'partidos_ganados_2_0' => 0,
            'partidos_ganados_2_1' => 0,
            'partidos_perdidos_1_2' => 0,
            'partidos_perdidos_0_2' => 0,
            'no_presentados' => 0,
            'sets_favor' => 0,
            'sets_contra' => 0,
            'puntos_favor' => 0,
            'puntos_contra' => 0,
        ];

        foreach ($partidos as $partido) {
            $esLocal = $partido->equipo_local_id == $equipoGrupo->equipo_id;

            $setsEquipo = $esLocal ? $partido->resultado_local : $partido->resultado_visitante;
            $setsRival = $esLocal ? $partido->resultado_visitante : $partido->resultado_local;
            $puntosEquipo = $esLocal ? ($partido->puntos_local ?? 0) : ($partido->puntos_visitante ?? 0);
            $puntosRival = $esLocal ? ($partido->puntos_visitante ?? 0) : ($partido->puntos_local ?? 0);

            $estadisticas['partidos_jugados']++;
            $estadisticas['sets_favor'] += $setsEquipo;
            $estadisticas['sets_contra'] += $setsRival;
            $estadisticas['puntos_favor'] += $puntosEquipo;
            $estadisticas['puntos_contra'] += $puntosRival;

            if ($setsEquipo == 2 && $setsRival == 0) {
                $estadisticas['puntos'] += 3;
                $estadisticas['partidos_ganados_2_0']++;
            } elseif ($setsEquipo == 2 && $setsRival == 1) {
                $estadisticas['puntos'] += 2;
                $estadisticas['partidos_ganados_2_1']++;
            } elseif ($setsEquipo == 1 && $setsRival == 2) {
                $estadisticas['puntos'] += 1;
                $estadisticas['partidos_perdidos_1_2']++;
            } elseif ($setsEquipo == 0 && $setsRival == 2) {
                $estadisticas['puntos'] += 0;
                $estadisticas['partidos_perdidos_0_2']++;
            }
        }

        $equipoGrupo->update($estadisticas);
    }

    private function recalcularPosicionesGrupo($grupoId)
    {
        $equiposOrdenados = EquipoGrupo::where('grupo_id', $grupoId)
            ->orderByRaw('puntos DESC, (sets_favor - sets_contra) DESC, sets_favor DESC, (puntos_favor - puntos_contra) DESC, puntos_favor DESC')
            ->get();

        foreach ($equiposOrdenados as $index => $equipoGrupo) {
            $equipoGrupo->update(['posicion' => $index + 1]);
        }
    }

    private function actualizarSiguientesPartidos(Partido $partido)
    {
        if (!$partido->relationLoaded('categoriaPartido')) {
            $partido->load('categoriaPartido');
        }

        $categoriaPartido = $partido->categoriaPartido;
        if (!$categoriaPartido) {
            return;
        }

        $equipoGanador = null;
        if ($partido->resultado_local > $partido->resultado_visitante) {
            $equipoGanador = $partido->equipoLocal;
        } else {
            $equipoGanador = $partido->equipoVisitante;
        }

        if (!$equipoGanador) {
            return;
        }

        $partidosDependientes = CategoriaPartido::where('categoria_id', $categoriaPartido->categoria_id)
            ->where('dependencias', 'LIKE', '%ganador_' . $categoriaPartido->numero_partido . '%')
            ->get();

        foreach ($partidosDependientes as $partidoDependiente) {
            $partidoDependiente->load('partido');
            $siguientePartido = $partidoDependiente->partido;

            if (!$siguientePartido) {
                continue;
            }

            $dependencias = is_string($partidoDependiente->dependencias)
                ? json_decode($partidoDependiente->dependencias, true)
                : $partidoDependiente->dependencias;

            if (!is_array($dependencias)) {
                continue;
            }

            $claveGanador = 'ganador_' . $categoriaPartido->numero_partido;

            if (isset($dependencias[0]) && $dependencias[0] === $claveGanador) {
                $siguientePartido->update(['equipo_local_id' => $equipoGanador->id]);
                Log::info("Equipo {$equipoGanador->nombre} avanza como LOCAL al partido {$siguientePartido->id}");
            } elseif (isset($dependencias[1]) && $dependencias[1] === $claveGanador) {
                $siguientePartido->update(['equipo_visitante_id' => $equipoGanador->id]);
                Log::info("Equipo {$equipoGanador->nombre} avanza como VISITANTE al partido {$siguientePartido->id}");
            }
        }
    }

    private function actualizarClasificacionGrupo($grupo)
    {
        $controller = new GrupoController();
        $controller->actualizarClasificacion($grupo);
    }
}
