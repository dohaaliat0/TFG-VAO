<?php
namespace App\Http\Controllers;

use App\Models\Torneo;
use App\Models\Grupo;
use App\Models\Equipo;
use App\Models\Partido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class GrupoController extends Controller
{
    public function store(Request $request, Torneo $torneo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $grupoExistente = $torneo->grupos()->where('nombre', $validated['nombre'])->first();

        if ($grupoExistente) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un grupo con ese nombre en este torneo'
                ], 422);
            }
            return redirect()->back()->with('error', 'Ya existe un grupo con ese nombre en este torneo');
        }

        $grupo = $torneo->grupos()->create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'grupo' => $grupo,
                'message' => 'Grupo creado correctamente'
            ]);
        }

        return redirect()->route('torneos.show', $torneo)
            ->with('success', 'Grupo creado correctamente');
    }

    public function show(Grupo $grupo)
    {
        $grupo->load(['equipos', 'partidos.sets', 'partidos.equipoLocal', 'partidos.equipoVisitante']);
        $this->actualizarClasificacionAutomatica($grupo);
        $equiposDisponibles = Equipo::where('torneo_id', $grupo->torneo_id)
            ->whereDoesntHave('grupos', function($query) use ($grupo) {
                $query->whereHas('torneo', function($q) use ($grupo) {
                    $q->where('id', $grupo->torneo_id);
                });
            })
            ->orderBy('nombre')
            ->get();
        return view('grupos.show', compact('grupo', 'equiposDisponibles'));
    }

    public function update(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $grupo->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Grupo actualizado correctamente'
            ]);
        }

        return redirect()->route('grupos.show', $grupo)
            ->with('success', 'Grupo actualizado correctamente');
    }

    public function destroy(Grupo $grupo)
    {
        try {
            DB::beginTransaction();
            $torneo = $grupo->torneo;
            $nombreGrupo = $grupo->nombre;

            if ($torneo->estado !== 'preparacion') {
                return redirect()->back()->with('error', 'Solo se pueden eliminar grupos en torneos en fase de preparación.');
            }

            $cantidadEquipos = $grupo->equipos()->count();
            $cantidadPartidos = $grupo->partidos()->count();
            Log::info("Iniciando eliminación del grupo: {$nombreGrupo}", [
                'grupo_id' => $grupo->id,
                'torneo_id' => $torneo->id,
                'equipos_count' => $cantidadEquipos,
                'partidos_count' => $cantidadPartidos
            ]);

            foreach ($grupo->partidos as $partido) {
                $partido->sets()->delete();
                Log::info("Sets eliminados para partido ID: {$partido->id}");
            }

            $grupo->partidos()->delete();
            Log::info("Partidos eliminados del grupo: {$cantidadPartidos}");
            $grupo->equipos()->detach();
            Log::info("Equipos desasociados del grupo: {$cantidadEquipos}");
            $grupo->delete();
            Log::info("Grupo eliminado: {$nombreGrupo}");
            DB::commit();
            $mensaje = "Grupo '$nombreGrupo' eliminado correctamente";

            if ($cantidadEquipos > 0 || $cantidadPartidos > 0) {
                $mensaje .= " junto con $cantidadEquipos equipos y $cantidadPartidos partidos asociados";
            }

            return redirect()->route('torneos.show', $torneo->id)
                ->with('success', $mensaje . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar grupo: ' . $e->getMessage(), [
                'grupo_id' => $grupo->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error al eliminar grupo: ' . $e->getMessage());
        }
    }

    public function agregarEquipo(Request $request, Grupo $grupo)
    {
        try {
            Log::info('Datos recibidos para agregar equipo:', [
                'request_data' => $request->all(),
                'grupo_id' => $grupo->id,
                'has_equipo_ids' => $request->has('equipo_ids'),
                'equipo_ids_value' => $request->input('equipo_ids')
            ]);
            DB::beginTransaction();

            try {
                $validated = $request->validate([
                    'equipo_ids' => 'required|array|min:1',
                    'equipo_ids.*' => 'exists:equipos,id',
                    'es_cabeza_grupo' => 'nullable|boolean',
                ], [
                    'equipo_ids.required' => 'Debe seleccionar al menos un equipo.',
                    'equipo_ids.array' => 'Los datos del equipo no son válidos.',
                    'equipo_ids.min' => 'Debe seleccionar al menos un equipo.',
                    'equipo_ids.*.exists' => 'Uno o más equipos seleccionados no existen.',
                ]);
            } catch (ValidationException $e) {
                Log::error('Error de validación al agregar equipo:', [
                    'errors' => $e->errors(),
                    'request_data' => $request->all()
                ]);

                return redirect()->back()
                    ->withErrors($e->errors())
                    ->withInput()
                    ->with('error', 'Error de validación: ' . implode(', ', array_flatten($e->errors())));
            }

            $torneo = $grupo->torneo;

            if ($torneo->estado !== 'preparacion') {
                return redirect()->back()->with('error', 'Solo se pueden agregar equipos en torneos en fase de preparación.');
            }

            $esCabezaGrupo = $request->has('es_cabeza_grupo') && $request->input('es_cabeza_grupo');
            $equiposActuales = $grupo->equipos()->count();
            $equiposNuevos = count($validated['equipo_ids']);

            Log::info('Verificando límites de equipos:', [
                'equipos_actuales' => $equiposActuales,
                'equipos_nuevos' => $equiposNuevos,
                'limite_torneo' => $torneo->equipos_por_grupo ?? 'sin límite'
            ]);

            if (isset($torneo->equipos_por_grupo) && ($equiposActuales + $equiposNuevos > $torneo->equipos_por_grupo)) {
                return redirect()->back()->with('error',
                    "No se pueden agregar todos los equipos seleccionados. El grupo puede tener máximo {$torneo->equipos_por_grupo} equipos y actualmente tiene {$equiposActuales}.");
            }

            $equiposAgregados = [];
            $equiposYaExistentes = [];

            foreach ($validated['equipo_ids'] as $equipoId) {
                Log::info("Procesando equipo ID: {$equipoId}");

                if ($grupo->equipos()->where('equipo_id', $equipoId)->exists()) {
                    $equipo = Equipo::find($equipoId);
                    $equiposYaExistentes[] = $equipo->nombre;
                    Log::info("Equipo {$equipo->nombre} ya existe en el grupo");
                    continue;
                }

                $equipoEnOtroGrupo = $torneo->grupos()
                    ->whereHas('equipos', function($query) use ($equipoId) {
                        $query->where('equipo_id', $equipoId);
                    })
                    ->where('id', '!=', $grupo->id)
                    ->first();

                if ($equipoEnOtroGrupo) {
                    $equipo = Equipo::find($equipoId);
                    Log::warning("Equipo {$equipo->nombre} ya está en otro grupo: {$equipoEnOtroGrupo->nombre}");
                    return redirect()->back()->with('error',
                        "El equipo '{$equipo->nombre}' ya está asignado al grupo '{$equipoEnOtroGrupo->nombre}' en este torneo.");
                }

                $grupo->equipos()->attach($equipoId, [
                    'posicion' => null,
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'partidos_ganados_2_0' => 0,
                    'partidos_ganados_2_1' => 0,
                    'partidos_perdidos_0_2' => 0,
                    'partidos_perdidos_1_2' => 0,
                    'no_presentados' => 0,
                    'sets_favor' => 0,
                    'sets_contra' => 0,
                    'puntos_favor' => 0,
                    'puntos_contra' => 0,
                ]);

                $equipo = Equipo::find($equipoId);
                $equiposAgregados[] = $equipo->nombre;
                Log::info("Equipo {$equipo->nombre} agregado correctamente al grupo");
            }

            if ($esCabezaGrupo && count($equiposAgregados) === 1) {
                $equipoCabezaActual = $grupo->equipos()->wherePivot('posicion', 1)->first();
                if ($equipoCabezaActual) {
                    $grupo->equipos()->updateExistingPivot($equipoCabezaActual->id, ['posicion' => null]);
                    Log::info("Removida cabeza de grupo anterior: {$equipoCabezaActual->nombre}");
                }

                $equipoId = $validated['equipo_ids'][0];
                $grupo->equipos()->updateExistingPivot($equipoId, ['posicion' => 1]);
                Log::info("Nuevo equipo marcado como cabeza de grupo: ID {$equipoId}");
            }

            DB::commit();
            $message = '';

            if (!empty($equiposAgregados)) {
                $message .= 'Equipos agregados correctamente: ' . implode(', ', $equiposAgregados);
                if ($esCabezaGrupo && count($equiposAgregados) === 1) {
                    $message .= ' (marcado como cabeza de grupo)';
                }
            }

            if (!empty($equiposYaExistentes)) {
                if (!empty($message)) $message .= '. ';
                $message .= 'Los siguientes equipos ya estaban en el grupo: ' . implode(', ', $equiposYaExistentes);
            }

            if (empty($equiposAgregados) && !empty($equiposYaExistentes)) {
                $message = 'Todos los equipos seleccionados ya están en este grupo.';
            }

            Log::info('Equipos agregados exitosamente:', [
                'equipos_agregados' => $equiposAgregados,
                'equipos_existentes' => $equiposYaExistentes,
                'message' => $message
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'equipos_agregados' => $equiposAgregados,
                    'equipos_existentes' => $equiposYaExistentes,
                    'es_cabeza_grupo' => $esCabezaGrupo && count($equiposAgregados) === 1,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al agregar equipos:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'grupo_id' => $grupo->id
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al agregar equipos: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al agregar equipos: ' . $e->getMessage());
        }
    }

    public function quitarEquipo(Request $request, Grupo $grupo, Equipo $equipo)
    {
        try {
            $torneo = $grupo->torneo;

            if ($torneo->estado !== 'preparacion') {
                return redirect()->back()->with('error', 'Solo se pueden quitar equipos en torneos en fase de preparación.');
            }

            if ($grupo->partidos()->count() > 0) {
                return redirect()->back()->with('error',
                    'No se puede quitar el equipo porque el grupo ya tiene partidos generados. Elimina primero todos los partidos.');
            }

            $grupo->equipos()->detach($equipo->id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Equipo eliminado del grupo correctamente'
                ]);
            }

            $this->actualizarClasificacionAutomatica($grupo);
            return redirect()->back()->with('success', 'Equipo eliminado del grupo correctamente');

        } catch (\Exception $e) {
            Log::error('Error al quitar equipo: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al quitar equipo: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al quitar equipo: ' . $e->getMessage());
        }
    }

    public function marcarCabezaGrupo(Request $request, Grupo $grupo, Equipo $equipo)
    {
        try {
            $torneo = $grupo->torneo;

            if ($torneo->estado !== 'preparacion') {
                return redirect()->back()->with('error', 'Solo se pueden modificar cabezas de grupo en torneos en fase de preparación.');
            }

            if (!$grupo->equipos()->where('equipo_id', $equipo->id)->exists()) {
                return redirect()->back()->with('error', 'El equipo no pertenece a este grupo.');
            }

            $equipoCabezaActual = $grupo->equipos()->wherePivot('posicion', 1)->first();

            if ($equipoCabezaActual) {
                $grupo->equipos()->updateExistingPivot($equipoCabezaActual->id, ['posicion' => null]);
            }

            $grupo->equipos()->updateExistingPivot($equipo->id, ['posicion' => 1]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Equipo marcado como cabeza de grupo correctamente'
                ]);
            }

            return redirect()->back()->with('success', 'Equipo marcado como cabeza de grupo correctamente');

        } catch (\Exception $e) {
            Log::error('Error al marcar cabeza de grupo: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al marcar cabeza de grupo: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al marcar cabeza de grupo: ' . $e->getMessage());
        }
    }

    public function generarPartidos(Request $request, Grupo $grupo)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validate([
                'tipo_partidos' => 'required|in:unico,ida_vuelta',
                'ida_vuelta' => 'nullable|string',
            ]);
            $torneo = $grupo->torneo;
            $idaVuelta = $validated['tipo_partidos'] === 'ida_vuelta' ||
                ($request->filled('ida_vuelta') && $request->input('ida_vuelta') === '1');

            if ($torneo->estado !== 'preparacion') {
                return redirect()->back()->with('error', 'Solo se pueden generar partidos para torneos en fase de preparación.');
            }

            $equipos = $grupo->equipos;

            if ($equipos->count() < 2) {
                return redirect()->back()->with('error', 'El grupo debe tener al menos 2 equipos para generar partidos.');
            }

            if ($grupo->partidos()->count() > 0) {
                return redirect()->back()->with('error', 'Ya hay partidos generados para este grupo. Elimínalos primero si deseas regenerarlos.');
            }

            $fechaInicio = $torneo->fecha_inicio ?? now();
            $partidosGenerados = $this->generarCalendarioPartidos($grupo, $equipos, $fechaInicio, $idaVuelta);
            DB::commit();
            $this->actualizarClasificacionAutomatica($grupo);
            $tipoPartidos = $idaVuelta ? 'ida y vuelta' : 'enfrentamiento único';
            return redirect()->route('grupos.show', $grupo)
                ->with('success', "Se han generado $partidosGenerados partidos ($tipoPartidos) para el grupo {$grupo->nombre}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al generar partidos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar partidos: ' . $e->getMessage());
        }
    }

    public function actualizarClasificacion(Grupo $grupo)
    {
        try {
            DB::beginTransaction();

            foreach ($grupo->equipos as $equipo) {
                $grupo->equipos()->updateExistingPivot($equipo->id, [
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'partidos_ganados_2_0' => 0,
                    'partidos_ganados_2_1' => 0,
                    'partidos_perdidos_0_2' => 0,
                    'partidos_perdidos_1_2' => 0,
                    'sets_favor' => 0,
                    'sets_contra' => 0,
                    'puntos_favor' => 0,
                    'puntos_contra' => 0,
                ]);
            }

            foreach ($grupo->partidos()->where('completado', true)->get() as $partido) {
                $this->actualizarEstadisticasPartido($grupo, $partido);
            }

            $this->ordenarClasificacion($grupo);
            DB::commit();
            return redirect()->route('grupos.show', $grupo)
                ->with('success', 'Clasificación actualizada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar clasificación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar clasificación: ' . $e->getMessage());
        }
    }

    private function actualizarEstadisticasPartido(Grupo $grupo, Partido $partido)
    {
        $localId = $partido->equipo_local_id;
        $visitanteId = $partido->equipo_visitante_id;
        $grupo->equipos()->updateExistingPivot($localId, [
            'partidos_jugados' => \DB::raw('partidos_jugados + 1')
        ]);
        $grupo->equipos()->updateExistingPivot($visitanteId, [
            'partidos_jugados' => \DB::raw('partidos_jugados + 1')
        ]);

        if ($partido->resultado_local > $partido->resultado_visitante) {
            if ($partido->resultado_local == 2 && $partido->resultado_visitante == 0) {
                $grupo->equipos()->updateExistingPivot($localId, [
                    'partidos_ganados_2_0' => \DB::raw('partidos_ganados_2_0 + 1'),
                    'puntos' => \DB::raw('puntos + 3')
                ]);
                $grupo->equipos()->updateExistingPivot($visitanteId, [
                    'partidos_perdidos_0_2' => \DB::raw('partidos_perdidos_0_2 + 1')
                ]);
            } else {
                $grupo->equipos()->updateExistingPivot($localId, [
                    'partidos_ganados_2_1' => \DB::raw('partidos_ganados_2_1 + 1'),
                    'puntos' => \DB::raw('puntos + 2')
                ]);
                $grupo->equipos()->updateExistingPivot($visitanteId, [
                    'partidos_perdidos_1_2' => \DB::raw('partidos_perdidos_1_2 + 1'),
                    'puntos' => \DB::raw('puntos + 1')
                ]);
            }
        } else {
            if ($partido->resultado_visitante == 2 && $partido->resultado_local == 0) {
                $grupo->equipos()->updateExistingPivot($visitanteId, [
                    'partidos_ganados_2_0' => \DB::raw('partidos_ganados_2_0 + 1'),
                    'puntos' => \DB::raw('puntos + 3')
                ]);
                $grupo->equipos()->updateExistingPivot($localId, [
                    'partidos_perdidos_0_2' => \DB::raw('partidos_perdidos_0_2 + 1')
                ]);
            } else {
                $grupo->equipos()->updateExistingPivot($visitanteId, [
                    'partidos_ganados_2_1' => \DB::raw('partidos_ganados_2_1 + 1'),
                    'puntos' => \DB::raw('puntos + 2')
                ]);
                $grupo->equipos()->updateExistingPivot($localId, [
                    'partidos_perdidos_1_2' => \DB::raw('partidos_perdidos_1_2 + 1'),
                    'puntos' => \DB::raw('puntos + 1')
                ]);
            }
        }

        $grupo->equipos()->updateExistingPivot($localId, [
            'sets_favor' => \DB::raw('sets_favor + ' . $partido->resultado_local),
            'sets_contra' => \DB::raw('sets_contra + ' . $partido->resultado_visitante)
        ]);
        $grupo->equipos()->updateExistingPivot($visitanteId, [
            'sets_favor' => \DB::raw('sets_favor + ' . $partido->resultado_visitante),
            'sets_contra' => \DB::raw('sets_contra + ' . $partido->resultado_local)
        ]);

        foreach ($partido->sets as $set) {
            $grupo->equipos()->updateExistingPivot($localId, [
                'puntos_favor' => \DB::raw('puntos_favor + ' . $set->puntos_local),
                'puntos_contra' => \DB::raw('puntos_contra + ' . $set->puntos_visitante)
            ]);
            $grupo->equipos()->updateExistingPivot($visitanteId, [
                'puntos_favor' => \DB::raw('puntos_favor + ' . $set->puntos_visitante),
                'puntos_contra' => \DB::raw('puntos_contra + ' . $set->puntos_local)
            ]);
        }
    }

    private function ordenarClasificacion(Grupo $grupo)
    {
        $equiposOrdenados = $grupo->equipos()
            ->withPivot([
                'puntos', 'sets_favor', 'sets_contra', 'puntos_favor', 'puntos_contra'
            ])
            ->get()
            ->sortByDesc(function ($equipo) {
                $puntos = $equipo->pivot->puntos;
                $difSets = $equipo->pivot->sets_favor - $equipo->pivot->sets_contra;
                $difPuntos = $equipo->pivot->puntos_favor - $equipo->pivot->puntos_contra;
                return sprintf('%010d%010d%010d', $puntos, $difSets, $difPuntos);
            });

        $posicion = 1;
        foreach ($equiposOrdenados as $equipo) {
            if ($posicion === 1) {
                $grupo->equipos()->updateExistingPivot($equipo->id, ['posicion' => 1]);
            } else {
                $grupo->equipos()->updateExistingPivot($equipo->id, ['posicion' => $posicion]);
            }
            $posicion++;
        }
    }

    private function generarCalendarioPartidos(Grupo $grupo, $equipos, Carbon $fechaInicio, $idaVuelta = false)
    {
        $equiposArray = $equipos->pluck('id')->toArray();
        $numEquipos = count($equiposArray);
        $partidosGenerados = 0;
        $fechaActual = clone $fechaInicio;

        if (!$idaVuelta) {
            for ($i = 0; $i < $numEquipos; $i++) {
                for ($j = $i + 1; $j < $numEquipos; $j++) {
                    Partido::create([
                        'grupo_id' => $grupo->id,
                        'equipo_local_id' => $equiposArray[$i],
                        'equipo_visitante_id' => $equiposArray[$j],
                        'fecha' => (clone $fechaActual)->setTime(18, 0, 0),
                        'completado' => false,
                        'resultado_local' => 0,
                        'resultado_visitante' => 0,
                    ]);
                    $partidosGenerados++;
                    $fechaActual->addDays(7);
                }
            }
        } else {
            $esImpar = $numEquipos % 2 !== 0;

            if ($esImpar) {
                $numEquipos++;
                $equiposArray[] = null;
            }

            $numJornadas = $numEquipos - 1;
            $partidosPorJornada = $numEquipos / 2;

            for ($jornada = 0; $jornada < $numJornadas; $jornada++) {
                $fechaJornada = (clone $fechaInicio)->addDays($jornada * 7);

                for ($partido = 0; $partido < $partidosPorJornada; $partido++) {
                    $local = ($jornada + $partido) % ($numEquipos - 1);
                    $visitante = ($numEquipos - 1 - $partido + $jornada) % ($numEquipos - 1);

                    if ($partido == 0) {
                        $visitante = $numEquipos - 1;
                    }

                    $equipoLocalId = $equiposArray[$local];
                    $equipoVisitanteId = $equiposArray[$visitante];

                    if ($equipoLocalId !== null && $equipoVisitanteId !== null) {
                        Partido::create([
                            'grupo_id' => $grupo->id,
                            'equipo_local_id' => $equipoLocalId,
                            'equipo_visitante_id' => $equipoVisitanteId,
                            'fecha' => (clone $fechaJornada)->setTime(18, 0, 0)->addHours($partido),
                            'completado' => false,
                            'resultado_local' => 0,
                            'resultado_visitante' => 0,
                        ]);
                        $partidosGenerados++;
                    }
                }
            }

            for ($jornada = 0; $jornada < $numJornadas; $jornada++) {
                $fechaJornada = (clone $fechaInicio)->addDays(($jornada + $numJornadas) * 7);

                for ($partido = 0; $partido < $partidosPorJornada; $partido++) {
                    $visitante = ($jornada + $partido) % ($numEquipos - 1);
                    $local = ($numEquipos - 1 - $partido + $jornada) % ($numEquipos - 1);

                    if ($partido == 0) {
                        $local = $numEquipos - 1;
                    }

                    $equipoLocalId = $equiposArray[$local];
                    $equipoVisitanteId = $equiposArray[$visitante];

                    if ($equipoLocalId !== null && $equipoVisitanteId !== null) {
                        Partido::create([
                            'grupo_id' => $grupo->id,
                            'equipo_local_id' => $equipoLocalId,
                            'equipo_visitante_id' => $equipoVisitanteId,
                            'fecha' => (clone $fechaJornada)->setTime(18, 0, 0)->addHours($partido),
                            'completado' => false,
                            'resultado_local' => 0,
                            'resultado_visitante' => 0,
                        ]);
                        $partidosGenerados++;
                    }
                }
            }
        }

        return $partidosGenerados;
    }

    public function eliminarTodosPartidos(Request $request, Grupo $grupo)
    {
        try {
            DB::beginTransaction();
            $torneo = $grupo->torneo;

            if ($torneo->estado !== 'preparacion') {
                return redirect()->back()->with('error', 'Solo se pueden eliminar partidos en torneos en fase de preparación.');
            }

            $cantidadPartidos = $grupo->partidos()->count();

            if ($cantidadPartidos === 0) {
                return redirect()->back()->with('error', 'No hay partidos para eliminar en este grupo.');
            }

            $grupo->partidos()->delete();

            foreach ($grupo->equipos as $equipo) {
                $grupo->equipos()->updateExistingPivot($equipo->id, [
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'partidos_ganados_2_0' => 0,
                    'partidos_ganados_2_1' => 0,
                    'partidos_perdidos_0_2' => 0,
                    'partidos_perdidos_1_2' => 0,
                    'sets_favor' => 0,
                    'sets_contra' => 0,
                    'puntos_favor' => 0,
                    'puntos_contra' => 0,
                ]);
            }

            DB::commit();
            $this->actualizarClasificacionAutomatica($grupo);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Se han eliminado $cantidadPartidos partidos del grupo {$grupo->nombre}."
                ]);
            }

            return redirect()->route('grupos.show', $grupo)
                ->with('success', "Se han eliminado $cantidadPartidos partidos del grupo {$grupo->nombre}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar partidos: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar partidos: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al eliminar partidos: ' . $e->getMessage());
        }
    }

    private function actualizarClasificacionAutomatica(Grupo $grupo)
    {
        try {
            DB::beginTransaction();

            foreach ($grupo->equipos as $equipo) {
                $grupo->equipos()->updateExistingPivot($equipo->id, [
                    'puntos' => 0,
                    'partidos_jugados' => 0,
                    'partidos_ganados_2_0' => 0,
                    'partidos_ganados_2_1' => 0,
                    'partidos_perdidos_0_2' => 0,
                    'partidos_perdidos_1_2' => 0,
                    'sets_favor' => 0,
                    'sets_contra' => 0,
                    'puntos_favor' => 0,
                    'puntos_contra' => 0,
                ]);
            }

            foreach ($grupo->partidos()->where('completado', true)->get() as $partido) {
                $this->actualizarEstadisticasPartido($grupo, $partido);
            }

            $this->ordenarClasificacion($grupo);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar clasificación automática: ' . $e->getMessage());
        }
    }

    public function getEquiposDisponibles(Grupo $grupo)
    {
        $equiposDisponibles = Equipo::where('torneo_id', $grupo->torneo_id)
            ->whereDoesntHave('grupos', function($query) use ($grupo) {
                $query->where('torneo_id', $grupo->torneo_id);
            })
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'equipos' => $equiposDisponibles
        ]);
    }
}
