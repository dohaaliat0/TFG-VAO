<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Torneo;
use App\Models\Partido;
use App\Models\EquipoGrupo;
use App\Models\EquipoCategoria;
use App\Models\CategoriaPartido;
use App\Models\Grupo;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CategoriaController extends Controller
{
    public function index(Request $request, Torneo $torneo = null)
    {
        $query = Categoria::with(['torneo']);

        if ($torneo) {
            $query->where('torneo_id', $torneo->id);
            $torneoSeleccionado = $torneo->id;
        } elseif ($request->has('torneo_id') && $request->torneo_id) {
            $query->where('torneo_id', $request->torneo_id);
            $torneoSeleccionado = $request->torneo_id;
            $torneo = Torneo::find($request->torneo_id);
        } else {
            $torneoSeleccionado = null;
        }

        $categorias = $query->orderBy('nombre')->paginate(10);

        foreach ($categorias as $categoria) {
            $categoria->equipos_count = EquipoCategoria::where('categoria_id', $categoria->id)->count();
        }

        $torneos = Torneo::orderBy('nombre')->get();

        return view('categorias.index', compact('categorias', 'torneos', 'torneoSeleccionado', 'torneo'));
    }

    public function create(Request $request)
    {
        $torneos = Torneo::orderBy('nombre')->get();
        $torneoSeleccionado = $request->get('torneo_id');

        return view('categorias.create', compact('torneos', 'torneoSeleccionado'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias')->where(function ($query) use ($request) {
                    return $query->where('torneo_id', $request->torneo_id);
                })
            ],
            'descripcion' => 'nullable|string|max:1000',
            'torneo_id' => 'required|exists:torneos,id',
        ], [
            'nombre.unique' => 'Ya existe una categoría con ese nombre en este torneo.',
            'torneo_id.required' => 'Debe seleccionar un torneo.',
            'torneo_id.exists' => 'El torneo seleccionado no existe.',
        ]);

        try {
            $categoria = Categoria::create($validated);

            return redirect()
                ->route('categorias.show', $categoria)
                ->with('success', 'Categoría creada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error creando categoría: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear la categoría. Inténtelo de nuevo.');
        }
    }

    public function show(Categoria $categoria)
    {
        $categoria->load(['torneo']);
        $equiposCategoria = $this->obtenerEquiposCategoria($categoria);
        $partidosCategoria = $this->obtenerPartidosCategoria($categoria);
        $estadisticas = $this->calcularEstadisticasCategoria($categoria);
        return view('categorias.show', compact('categoria', 'equiposCategoria', 'partidosCategoria', 'estadisticas'));
    }

    public function edit(Categoria $categoria)
    {
        $torneos = Torneo::orderBy('nombre')->get();
        $tieneEquipos = EquipoCategoria::where('categoria_id', $categoria->id)->exists();
        $tienePartidos = $this->tienePartidosEliminatorios($categoria);
        return view('categorias.edit', compact('categoria', 'torneos', 'tieneEquipos', 'tienePartidos'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias')->where(function ($query) use ($request) {
                    return $query->where('torneo_id', $request->torneo_id);
                })->ignore($categoria->id)
            ],
            'descripcion' => 'nullable|string|max:1000',
            'torneo_id' => 'required|exists:torneos,id',
        ], [
            'nombre.unique' => 'Ya existe una categoría con ese nombre en este torneo.',
            'torneo_id.required' => 'Debe seleccionar un torneo.',
            'torneo_id.exists' => 'El torneo seleccionado no existe.',
        ]);

        try {
            $tieneEquipos = EquipoCategoria::where('categoria_id', $categoria->id)->exists();
            $tienePartidos = $this->tienePartidosEliminatorios($categoria);

            if (($tieneEquipos || $tienePartidos) && $categoria->torneo_id != $validated['torneo_id']) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'No se puede cambiar el torneo porque la categoría tiene equipos asignados o partidos generados.');
            }

            $categoria->update($validated);

            return redirect()
                ->route('categorias.show', $categoria)
                ->with('success', 'Categoría actualizada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error actualizando categoría: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar la categoría. Inténtelo de nuevo.');
        }
    }

    public function destroy(Categoria $categoria)
    {
        try {
            DB::beginTransaction();
            $tieneEquipos = EquipoCategoria::where('categoria_id', $categoria->id)->exists();
            $tienePartidos = $this->tienePartidosEliminatorios($categoria);

            if ($tieneEquipos || $tienePartidos) {
                return redirect()
                    ->route('categorias.index')
                    ->with('error', 'No se puede eliminar la categoría porque tiene equipos asignados o partidos generados.');
            }

            $categoria->delete();
            DB::commit();
            return redirect()
                ->route('categorias.index')
                ->with('success', 'Categoría eliminada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando categoría: ' . $e->getMessage());

            return redirect()
                ->route('categorias.index')
                ->with('error', 'Error al eliminar la categoría. Inténtelo de nuevo.');
        }
    }

    public function asignarCategoriasForm(Torneo $torneo)
    {
        try {
            $partidosPendientes = $this->verificarPartidosPendientes($torneo);

            if ($partidosPendientes > 0) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', "No se pueden asignar categorías hasta que todos los partidos de grupos estén finalizados. Quedan {$partidosPendientes} partidos pendientes.");
            }

            $categorias = Categoria::where('torneo_id', $torneo->id)
                ->orderBy('nombre')
                ->get();

            if ($categorias->count() < 1) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'El reparto automático requiere al menos 1 categoría. Actualmente hay ' . $categorias->count() . ' categorías.');
            }

            $grupos = $torneo->grupos()->with('equipos')->get();

            if ($grupos->isEmpty()) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'No hay grupos en este torneo.');
            }

            $infoNormalizacion = $this->analizarNormalizacionGrupos($grupos);
            $clasificaciones = $this->obtenerClasificacionesGrupos($grupos);

            return view('categorias.reparto-automatico', compact(
                'torneo',
                'categorias',
                'clasificaciones',
                'infoNormalizacion'
            ));

        } catch (\Exception $e) {
            Log::error('Error en formulario de reparto: ' . $e->getMessage());

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('error', 'Error al cargar el formulario de reparto automático.');
        }
    }

    public function repartirCategorias(Request $request, Torneo $torneo)
    {
        try {
            DB::beginTransaction();

            $debugInfo = [];
            $debugInfo[] = "=== INICIANDO REPARTO AUTOMÁTICO ===";
            $debugInfo[] = "Torneo: {$torneo->nombre} (ID: {$torneo->id})";
            $debugInfo[] = "Fecha: " . now()->format('Y-m-d H:i:s');
            $this->verificarTablasNecesarias();
            $categorias = Categoria::where('torneo_id', $torneo->id)
                ->orderBy('nombre')
                ->get();
            $debugInfo[] = "Categorías encontradas: " . $categorias->count();

            if ($categorias->count() < 1) {
                throw new \Exception('El reparto automático requiere al menos 1 categoría. Actualmente hay ' . $categorias->count() . ' categorías.');
            }

            $grupos = $torneo->grupos()->get();
            $debugInfo[] = "Grupos encontrados: " . $grupos->count();

            if ($grupos->isEmpty()) {
                throw new \Exception('No hay grupos en este torneo.');
            }

            $partidosPendientes = $this->verificarPartidosPendientes($torneo);
            if ($partidosPendientes > 0) {
                throw new \Exception("Hay {$partidosPendientes} partidos de grupos pendientes. Complete todos los partidos antes del reparto.");
            }

            $infoNormalizacion = $this->analizarNormalizacionGrupos($grupos);
            if ($infoNormalizacion['necesita_normalizacion']) {
                $debugInfo[] = "=== APLICANDO NORMALIZACIÓN ===";
                $this->normalizarGrupos($grupos, $infoNormalizacion, $debugInfo);
            }

            $equiposPorPosicion = $this->obtenerEquiposPorPosicion($grupos, $debugInfo);
            $totalEquipos = array_sum(array_map('count', $equiposPorPosicion));
            if ($totalEquipos == 0) {
                throw new \Exception('No se encontraron equipos clasificados en los grupos.');
            }

            $this->limpiarAsignacionesPrevias($categorias, $debugInfo);
            $asignaciones = $this->realizarRepartoSegunPosiciones($equiposPorPosicion, $categorias, $debugInfo);
            $totalAsignaciones = $this->guardarAsignaciones($asignaciones, $categorias, $debugInfo);
            $this->generarEliminatoriasAdaptativas($categorias, $torneo, $debugInfo);
            DB::commit();
            $equiposAsignados = array_sum(array_map('count', $asignaciones));
            $mensaje = "Reparto de categorías completado exitosamente. {$equiposAsignados} equipos asignados a categorías.";

            if ($infoNormalizacion['necesita_normalizacion']) {
                $mensaje .= ' Se aplicó normalización de grupos.';
            }

            $mensaje .= ' Se generaron las eliminatorias adaptadas al número de equipos.';

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('success', $mensaje)
                ->with('debug_info', implode("\n", $debugInfo));

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error en reparto automático", [
                'torneo_id' => $torneo->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Error al realizar el reparto: ' . $e->getMessage())
                ->with('debug_info', implode("\n", $debugInfo ?? []));
        }
    }

    public function limpiarAsignaciones(Torneo $torneo)
    {
        try {
            DB::beginTransaction();
            $categorias = Categoria::where('torneo_id', $torneo->id)->get();
            $this->eliminarPartidosEliminatorios($categorias);

            foreach ($categorias as $categoria) {
                EquipoCategoria::where('categoria_id', $categoria->id)->delete();
            }

            DB::commit();

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('success', 'Todas las asignaciones de categorías han sido eliminadas.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error limpiando asignaciones: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Error al limpiar las asignaciones.');
        }
    }

    public function generarSiguienteFase(Torneo $torneo)
    {
        try {
            DB::beginTransaction();
            $debugInfo = [];
            $debugInfo[] = "=== GENERANDO SIGUIENTE FASE MANUALMENTE ===";
            $debugInfo[] = "Torneo: {$torneo->nombre} (ID: {$torneo->id})";
            $debugInfo[] = "Fecha: " . now()->format('Y-m-d H:i:s');
            $this->verificarTablasNecesarias();
            $categorias = Categoria::where('torneo_id', $torneo->id)->get();
            $debugInfo[] = "Categorías encontradas: " . $categorias->count();
            $faseAGenerar = $this->determinarSiguienteFaseAGenerar($categorias, $debugInfo);

            if (!$faseAGenerar) {
                throw new \Exception('No hay una fase siguiente para generar o la fase actual no está completa.');
            }

            $debugInfo[] = "Fase a generar: {$faseAGenerar}";
            $fechaBase = Carbon::parse($torneo->fecha_fin)->addDay();

            foreach ($categorias as $categoria) {
                $this->generarFaseEspecifica($categoria, $faseAGenerar, $fechaBase, $debugInfo);
            }

            DB::commit();
            $nombreFase = $this->obtenerNombreFase($faseAGenerar);
            return redirect()->route('torneos.show', $torneo)
                ->with('success', "Se han generado correctamente los partidos de {$nombreFase}.")
                ->with('debug_info', implode("\n", $debugInfo));

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error generando siguiente fase", [
                'torneo_id' => $torneo->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Error al generar la siguiente fase: ' . $e->getMessage())
                ->with('debug_info', implode("\n", $debugInfo ?? []));
        }
    }

    public function generarSiguienteFaseCategoria(Request $request, Categoria $categoria)
    {
        try {
            DB::beginTransaction();

            $debugInfo = [];
            $debugInfo[] = "=== GENERANDO SIGUIENTE FASE PARA CATEGORÍA {$categoria->nombre} ===";
            $debugInfo[] = "Categoría: {$categoria->nombre} (ID: {$categoria->id})";
            $debugInfo[] = "Fecha: " . now()->format('Y-m-d H:i:s');
            $this->verificarTablasNecesarias();
            $faseAGenerar = $this->determinarSiguienteFaseAGenerarCategoria($categoria, $debugInfo);

            if (!$faseAGenerar) {
                throw new \Exception('No hay una fase siguiente para generar o la fase actual no está completa para esta categoría.');
            }

            $debugInfo[] = "Fase a generar: {$faseAGenerar}";
            $fechaBase = Carbon::parse($categoria->torneo->fecha_fin)->addDay();
            $this->generarFaseEspecifica($categoria, $faseAGenerar, $fechaBase, $debugInfo);
            DB::commit();
            $nombreFase = $this->obtenerNombreFase($faseAGenerar);
            $urlRetorno = $request->input('url_retorno', route('torneos.show', $categoria->torneo));
            return redirect($urlRetorno)
                ->with('success', "Se han generado correctamente los partidos de {$nombreFase} para la categoría {$categoria->nombre}.")
                ->with('debug_info', implode("\n", $debugInfo));

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error generando siguiente fase para categoría", [
                'categoria_id' => $categoria->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $urlRetorno = $request->input('url_retorno', route('torneos.show', $categoria->torneo));

            return redirect($urlRetorno)
                ->with('error', 'Error al generar la siguiente fase: ' . $e->getMessage())
                ->with('debug_info', implode("\n", $debugInfo ?? []));
        }
    }

    private function determinarSiguienteFaseAGenerarCategoria($categoria, &$debugInfo)
    {
        $debugInfo[] = "=== ANALIZANDO FASES PARA CATEGORÍA {$categoria->nombre} ===";
        $cuartos = CategoriaPartido::where('categoria_id', $categoria->id)
            ->where('fase', 'cuartos')
            ->with('partido')
            ->get();
        $cuartosExisten = $cuartos->count() > 0;
        $cuartosCompletos = true;
        $cuartosCompletados = 0;
        $totalCuartos = $cuartos->count();

        foreach ($cuartos as $cuarto) {
            if ($cuarto->partido->completado) {
                $cuartosCompletados++;
            } else {
                $cuartosCompletos = false;
            }
        }

        $debugInfo[] = "Cuartos existen: " . ($cuartosExisten ? 'Sí' : 'No');
        $debugInfo[] = "Total cuartos: $totalCuartos";
        $debugInfo[] = "Cuartos completados: $cuartosCompletados";
        $debugInfo[] = "Cuartos completos: " . ($cuartosCompletos ? 'Sí' : 'No');

        if (!$cuartosExisten) {
            $equiposCount = EquipoCategoria::where('categoria_id', $categoria->id)->count();
            if ($equiposCount >= 8) {
                $debugInfo[] = "RESULTADO: No existen cuartos - GENERAR CUARTOS";
                return 'cuartos';
            } else if ($equiposCount >= 4) {
                $debugInfo[] = "RESULTADO: No existen cuartos pero hay $equiposCount equipos - GENERAR SEMIFINALES DIRECTAS";
                return 'semifinal_directa';
            } else if ($equiposCount >= 2) {
                $debugInfo[] = "RESULTADO: No existen cuartos pero hay $equiposCount equipos - GENERAR FINAL DIRECTA";
                return 'final_directa';
            } else {
                $debugInfo[] = "RESULTADO: No hay suficientes equipos para generar eliminatorias";
                return null;
            }
        }

        if (!$cuartosCompletos) {
            $debugInfo[] = "RESULTADO: Cuartos no están completos - no se puede generar siguiente fase";
            return null;
        }

        $semifinales = CategoriaPartido::where('categoria_id', $categoria->id)
            ->where('fase', 'semifinal')
            ->with('partido')
            ->get();
        $semifinalesExisten = $semifinales->count() > 0;
        $semifinalesCompletas = true;
        $semifinalesCompletadas = 0;
        $totalSemifinales = $semifinales->count();

        foreach ($semifinales as $semifinal) {
            if ($semifinal->partido->completado) {
                $semifinalesCompletadas++;
            } else {
                $semifinalesCompletas = false;
            }
        }

        $debugInfo[] = "Semifinales existen: " . ($semifinalesExisten ? 'Sí' : 'No');
        $debugInfo[] = "Total semifinales: $totalSemifinales";
        $debugInfo[] = "Semifinales completadas: $semifinalesCompletadas";
        $debugInfo[] = "Semifinales completas: " . ($semifinalesCompletas ? 'Sí' : 'No');

        if ($cuartosCompletos && !$semifinalesExisten) {
            $debugInfo[] = "RESULTADO: Cuartos completos y no existen semifinales - GENERAR SEMIFINALES";
            return 'semifinal';
        }

        if ($semifinalesExisten && !$semifinalesCompletas) {
            $debugInfo[] = "RESULTADO: Semifinales existen pero no están completas - no se puede generar siguiente fase";
            return null;
        }

        $finalExiste = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'final')
                ->count() > 0;

        $debugInfo[] = "Final existe: " . ($finalExiste ? 'Sí' : 'No');

        if ($semifinalesExisten && $semifinalesCompletas && !$finalExiste) {
            $debugInfo[] = "RESULTADO: Semifinales completas y no existe final - GENERAR FINAL";
            return 'final';
        }

        $debugInfo[] = "RESULTADO: No hay fase siguiente para generar";
        return null;
    }

    public function generarCuartosCategoria(Request $request, Categoria $categoria)
    {
        try {
            DB::beginTransaction();

            $debugInfo = [];
            $debugInfo[] = "=== GENERANDO CUARTOS DE FINAL PARA CATEGORÍA {$categoria->nombre} ===";
            $debugInfo[] = "Fecha: " . now()->format('Y-m-d H:i:s');
            $this->verificarTablasNecesarias();
            $cuartosExistentes = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'cuartos')
                ->count();

            if ($cuartosExistentes > 0) {
                throw new \Exception('Ya existen partidos de cuartos de final para esta categoría.');
            }

            $equiposCategoria = EquipoCategoria::with(['equipoGrupo.equipo'])
                ->where('categoria_id', $categoria->id)
                ->orderBy('posicion')
                ->get();
            $cantidadEquipos = $equiposCategoria->count();
            $debugInfo[] = "Equipos en la categoría: $cantidadEquipos";

            if ($cantidadEquipos < 8) {
                throw new \Exception("Se necesitan al menos 8 equipos para generar cuartos de final. Actualmente hay $cantidadEquipos equipos.");
            }

            $equipos = $equiposCategoria->map(function($ec) {
                return [
                    'id' => $ec->equipoGrupo->equipo->id,
                    'nombre' => $ec->equipoGrupo->equipo->nombre,
                    'posicion' => $ec->posicion
                ];
            })->toArray();
            $fechaBase = Carbon::parse($categoria->torneo->fecha_fin)->addDay();
            $this->generarCuartosDeFinales($categoria, array_slice($equipos, 0, 8), $fechaBase, $debugInfo);
            DB::commit();
            $urlRetorno = $request->input('url_retorno', route('torneos.show', $categoria->torneo));
            return redirect($urlRetorno)
                ->with('success', "Se han generado correctamente los cuartos de final para la categoría {$categoria->nombre}.")
                ->with('debug_info', implode("\n", $debugInfo));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generando cuartos de final para categoría", [
                'categoria_id' => $categoria->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $urlRetorno = $request->input('url_retorno', route('torneos.show', $categoria->torneo));

            return redirect($urlRetorno)
                ->with('error', 'Error al generar cuartos de final: ' . $e->getMessage())
                ->with('debug_info', implode("\n", $debugInfo ?? []));
        }
    }

    public function generarSemifinalesDirectasCategoria(Request $request, Categoria $categoria)
    {
        try {
            DB::beginTransaction();
            $debugInfo = [];
            $debugInfo[] = "=== GENERANDO SEMIFINALES DIRECTAS PARA CATEGORÍA {$categoria->nombre} ===";
            $debugInfo[] = "Fecha: " . now()->format('Y-m-d H:i:s');
            $this->verificarTablasNecesarias();
            $semifinalesExistentes = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'semifinal')
                ->count();

            if ($semifinalesExistentes > 0) {
                throw new \Exception('Ya existen partidos de semifinales para esta categoría.');
            }

            $equiposCategoria = EquipoCategoria::with(['equipoGrupo.equipo'])
                ->where('categoria_id', $categoria->id)
                ->orderBy('posicion')
                ->get();

            $cantidadEquipos = $equiposCategoria->count();
            $debugInfo[] = "Equipos en la categoría: $cantidadEquipos";

            if ($cantidadEquipos < 4) {
                throw new \Exception("Se necesitan al menos 4 equipos para generar semifinales. Actualmente hay $cantidadEquipos equipos.");
            }

            $equipos = $equiposCategoria->map(function($ec) {
                return [
                    'id' => $ec->equipoGrupo->equipo->id,
                    'nombre' => $ec->equipoGrupo->equipo->nombre,
                    'posicion' => $ec->posicion
                ];
            })->toArray();
            $fechaBase = Carbon::parse($categoria->torneo->fecha_fin)->addDay();
            $this->generarSemifinalesDirectas($categoria, array_slice($equipos, 0, 4), $fechaBase, $debugInfo);
            DB::commit();
            $urlRetorno = $request->input('url_retorno', route('torneos.show', $categoria->torneo));
            return redirect($urlRetorno)
                ->with('success', "Se han generado correctamente las semifinales para la categoría {$categoria->nombre}.")
                ->with('debug_info', implode("\n", $debugInfo));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generando semifinales directas para categoría", [
                'categoria_id' => $categoria->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $urlRetorno = $request->input('url_retorno', route('torneos.show', $categoria->torneo));
            return redirect($urlRetorno)
                ->with('error', 'Error al generar semifinales: ' . $e->getMessage())
                ->with('debug_info', implode("\n", $debugInfo ?? []));
        }
    }

    public function generarFinalDirectaCategoria(Request $request, Categoria $categoria)
    {
        try {
            DB::beginTransaction();

            $debugInfo = [];
            $debugInfo[] = "=== GENERANDO FINAL DIRECTA PARA CATEGORÍA {$categoria->nombre} ===";
            $debugInfo[] = "Fecha: " . now()->format('Y-m-d H:i:s');
            $this->verificarTablasNecesarias();
            $finalExistente = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'final')
                ->count();

            if ($finalExistente > 0) {
                throw new \Exception('Ya existe un partido de final para esta categoría.');
            }

            $equiposCategoria = EquipoCategoria::with(['equipoGrupo.equipo'])
                ->where('categoria_id', $categoria->id)
                ->orderBy('posicion')
                ->get();
            $cantidadEquipos = $equiposCategoria->count();
            $debugInfo[] = "Equipos en la categoría: $cantidadEquipos";

            if ($cantidadEquipos < 2) {
                throw new \Exception("Se necesitan al menos 2 equipos para generar la final. Actualmente hay $cantidadEquipos equipos.");
            }

            $equipos = $equiposCategoria->map(function($ec) {
                return [
                    'id' => $ec->equipoGrupo->equipo->id,
                    'nombre' => $ec->equipoGrupo->equipo->nombre,
                    'posicion' => $ec->posicion
                ];
            })->toArray();
            $fechaBase = Carbon::parse($categoria->torneo->fecha_fin)->addDay();
            $this->generarFinalDirecta($categoria, array_slice($equipos, 0, 2), $fechaBase, $debugInfo);
            DB::commit();
            $urlRetorno = $request->input('url_retorno', route('torneos.show', $categoria->torneo));
            return redirect($urlRetorno)
                ->with('success', "Se ha generado correctamente la final para la categoría {$categoria->nombre}.")
                ->with('debug_info', implode("\n", $debugInfo));

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error generando final directa para categoría", [
                'categoria_id' => $categoria->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $urlRetorno = $request->input('url_retorno', route('torneos.show', $categoria->torneo));

            return redirect($urlRetorno)
                ->with('error', 'Error al generar la final: ' . $e->getMessage())
                ->with('debug_info', implode("\n", $debugInfo ?? []));
        }
    }

    private function generarEliminatoriasAdaptativas($categorias, $torneo, &$debugInfo): void
    {
        $debugInfo[] = "=== GENERANDO ELIMINATORIAS ADAPTATIVAS ===";

        $fechaBase = Carbon::parse($torneo->fecha_fin)->addDay();

        foreach ($categorias as $categoria) {
            $equiposCategoria = EquipoCategoria::with(['equipoGrupo.equipo'])
                ->where('categoria_id', $categoria->id)
                ->orderBy('posicion')
                ->get();

            $cantidadEquipos = $equiposCategoria->count();
            $debugInfo[] = "--- Categoría: {$categoria->nombre} - {$cantidadEquipos} equipos ---";

            if ($cantidadEquipos == 0) {
                $debugInfo[] = "ERROR: La categoría no tiene equipos asignados";
                continue;
            }

            $equipos = $equiposCategoria->map(function($ec) {
                return [
                    'id' => $ec->equipoGrupo->equipo->id,
                    'nombre' => $ec->equipoGrupo->equipo->nombre,
                    'posicion' => $ec->posicion
                ];
            })->toArray();

            if ($cantidadEquipos >= 8) {
                $debugInfo[] = "Generando cuartos de final para 8 equipos";
                $this->generarCuartosDeFinales($categoria, array_slice($equipos, 0, 8), $fechaBase, $debugInfo);
            }
            elseif ($cantidadEquipos >= 4) {
                $debugInfo[] = "Generando semifinales directamente para {$cantidadEquipos} equipos";
                $this->generarSemifinalesDirectas($categoria, array_slice($equipos, 0, 4), $fechaBase, $debugInfo);
            }
            elseif ($cantidadEquipos >= 2) {
                $debugInfo[] = "Generando final directamente para {$cantidadEquipos} equipos";
                $this->generarFinalDirecta($categoria, array_slice($equipos, 0, 2), $fechaBase, $debugInfo);
            }
            else {
                $debugInfo[] = "ERROR: Se necesitan al menos 2 equipos para generar eliminatorias";
            }
        }
    }

    private function generarSemifinalesDirectas($categoria, $equipos, $fechaBase, &$debugInfo): void
    {
        $debugInfo[] = "Generando semifinales directas...";

        $semifinales = [
            [
                'numero' => 'SF1',
                'local' => $equipos[0],
                'visitante' => $equipos[2],
                'hora' => 10,
                'dia_offset' => 0
            ],
            [
                'numero' => 'SF2',
                'local' => $equipos[1],
                'visitante' => $equipos[3],
                'hora' => 12,
                'dia_offset' => 0
            ]
        ];

        foreach ($semifinales as $semi) {
            $fechaPartido = $fechaBase->copy()->addDays($semi['dia_offset'])->setTime($semi['hora'], 0);

            $partido = Partido::create([
                'grupo_id' => null,
                'equipo_local_id' => $semi['local']['id'],
                'equipo_visitante_id' => $semi['visitante']['id'],
                'fecha' => $fechaPartido,
                'completado' => false,
                'resultado_local' => null,
                'resultado_visitante' => null,
                'puntos_local' => null,
                'puntos_visitante' => null,
            ]);

            CategoriaPartido::create([
                'categoria_id' => $categoria->id,
                'partido_id' => $partido->id,
                'fase' => 'semifinal',
                'numero_partido' => $semi['numero'],
                'dependencias' => null,
            ]);

            $debugInfo[] = "✓ {$semi['numero']}: {$semi['local']['nombre']} vs {$semi['visitante']['nombre']} - {$fechaPartido->format('d/m/Y H:i')}";
        }
    }

    private function generarFinalDirecta($categoria, $equipos, $fechaBase, &$debugInfo): void
    {
        $debugInfo[] = "Generando final directa...";

        $fechaFinal = $fechaBase->copy()->setTime(11, 0);

        $partido = Partido::create([
            'grupo_id' => null,
            'equipo_local_id' => $equipos[0]['id'],
            'equipo_visitante_id' => $equipos[1]['id'],
            'fecha' => $fechaFinal,
            'completado' => false,
            'resultado_local' => null,
            'resultado_visitante' => null,
            'puntos_local' => null,
            'puntos_visitante' => null,
        ]);

        CategoriaPartido::create([
            'categoria_id' => $categoria->id,
            'partido_id' => $partido->id,
            'fase' => 'final',
            'numero_partido' => 'F',
            'dependencias' => null,
        ]);

        $debugInfo[] = "✓ Final: {$equipos[0]['nombre']} vs {$equipos[1]['nombre']} - {$fechaFinal->format('d/m/Y H:i')}";
    }

    private function determinarSiguienteFaseAGenerar($categorias, &$debugInfo)
    {
        $debugInfo[] = "=== ANALIZANDO FASES PARA DETERMINAR SIGUIENTE ===";

        $cuartosExisten = false;
        $cuartosCompletos = true;
        $totalCuartos = 0;
        $cuartosCompletados = 0;

        foreach ($categorias as $categoria) {
            $cuartos = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'cuartos')
                ->with('partido')
                ->get();

            if ($cuartos->count() > 0) {
                $cuartosExisten = true;
                $totalCuartos += $cuartos->count();

                foreach ($cuartos as $cuarto) {
                    if ($cuarto->partido->completado) {
                        $cuartosCompletados++;
                    } else {
                        $cuartosCompletos = false;
                    }
                }
            } else {
                $cuartosCompletos = false;
                break;
            }
        }

        $debugInfo[] = "Cuartos existen: " . ($cuartosExisten ? 'Sí' : 'No');
        $debugInfo[] = "Total cuartos: $totalCuartos";
        $debugInfo[] = "Cuartos completados: $cuartosCompletados";
        $debugInfo[] = "Cuartos completos: " . ($cuartosCompletos ? 'Sí' : 'No');

        if (!$cuartosExisten) {
            $debugInfo[] = "RESULTADO: No existen cuartos - no hay nada que generar";
            return null;
        }

        if (!$cuartosCompletos) {
            $debugInfo[] = "RESULTADO: Cuartos no están completos - no se puede generar siguiente fase";
            return null;
        }

        $semifinalesExisten = false;
        $semifinalesCompletas = true;
        $totalSemifinales = 0;
        $semifinalesCompletadas = 0;

        foreach ($categorias as $categoria) {
            $semifinales = CategoriaPartido::where('categoria_id', $categoria->id)
                ->where('fase', 'semifinal')
                ->with('partido')
                ->get();

            if ($semifinales->count() > 0) {
                $semifinalesExisten = true;
                $totalSemifinales += $semifinales->count();

                foreach ($semifinales as $semifinal) {
                    if ($semifinal->partido->completado) {
                        $semifinalesCompletadas++;
                    } else {
                        $semifinalesCompletas = false;
                    }
                }
            }
        }

        $debugInfo[] = "Semifinales existen: " . ($semifinalesExisten ? 'Sí' : 'No');
        $debugInfo[] = "Total semifinales: $totalSemifinales";
        $debugInfo[] = "Semifinales completadas: $semifinalesCompletadas";
        $debugInfo[] = "Semifinales completas: " . ($semifinalesCompletas ? 'Sí' : 'No');

        if ($cuartosCompletos && !$semifinalesExisten) {
            $debugInfo[] = "RESULTADO: Cuartos completos y no existen semifinales - GENERAR SEMIFINALES";
            return 'semifinal';
        }

        if ($semifinalesExisten && !$semifinalesCompletas) {
            $debugInfo[] = "RESULTADO: Semifinales existen pero no están completas - no se puede generar siguiente fase";
            return null;
        }

        if ($semifinalesExisten && $semifinalesCompletas) {
            $finalExiste = false;

            foreach ($categorias as $categoria) {
                $final = CategoriaPartido::where('categoria_id', $categoria->id)
                    ->where('fase', 'final')
                    ->count();

                if ($final > 0) {
                    $finalExiste = true;
                    break;
                }
            }

            $debugInfo[] = "Final existe: " . ($finalExiste ? 'Sí' : 'No');

            if (!$finalExiste) {
                $debugInfo[] = "RESULTADO: Semifinales completas y no existe final - GENERAR FINAL";
                return 'final';
            }
        }

        $debugInfo[] = "RESULTADO: No hay fase siguiente para generar";
        return null;
    }

    private function generarFaseEspecifica($categoria, $fase, $fechaBase, &$debugInfo)
    {
        $debugInfo[] = "=== GENERANDO {$fase} PARA CATEGORÍA {$categoria->nombre} ===";

        if ($fase === 'semifinal') {
            $ganadores = $this->obtenerGanadoresFase($categoria, 'cuartos', $debugInfo);

            if (count($ganadores) < 4) {
                throw new \Exception("No hay suficientes ganadores de cuartos para generar semifinales en la categoría {$categoria->nombre}");
            }

            $this->crearSemifinales($categoria, $ganadores, $fechaBase, $debugInfo);

        } elseif ($fase === 'final') {
            $ganadores = $this->obtenerGanadoresFase($categoria, 'semifinal', $debugInfo);

            if (count($ganadores) < 2) {
                throw new \Exception("No hay suficientes ganadores de semifinales para generar la final en la categoría {$categoria->nombre}");
            }

            $this->crearFinal($categoria, $ganadores, $fechaBase, $debugInfo);
        }
    }

    private function crearSemifinales($categoria, $ganadores, $fechaBase, &$debugInfo)
    {
        $debugInfo[] = "Creando semifinales con equipos reales...";

        $ganadoresPorPartido = [];
        foreach ($ganadores as $ganador) {
            $ganadoresPorPartido[$ganador['numero_partido']] = $ganador;
        }

        $partidosRequeridos = ['QF1', 'QF2', 'QF3', 'QF4'];
        foreach ($partidosRequeridos as $partidoReq) {
            if (!isset($ganadoresPorPartido[$partidoReq])) {
                $debugInfo[] = "ADVERTENCIA: No se encontró el ganador del partido {$partidoReq}";
            }
        }

        $semifinales = [
            [
                'numero' => 'SF1',
                'local' => $ganadoresPorPartido['QF1']['equipo'],
                'visitante' => $ganadoresPorPartido['QF3']['equipo'],
                'hora' => 10,
                'dia_offset' => 1,
                'dependencias' => ['ganador_QF1', 'ganador_QF3']
            ],
            [
                'numero' => 'SF2',
                'local' => $ganadoresPorPartido['QF2']['equipo'],
                'visitante' => $ganadoresPorPartido['QF4']['equipo'],
                'hora' => 12,
                'dia_offset' => 1,
                'dependencias' => ['ganador_QF2', 'ganador_QF4']
            ]
        ];

        foreach ($semifinales as $semi) {
            $fechaPartido = $fechaBase->copy()->addDays($semi['dia_offset'])->setTime($semi['hora'], 0);

            $partido = Partido::create([
                'grupo_id' => null,
                'equipo_local_id' => $semi['local']->id,
                'equipo_visitante_id' => $semi['visitante']->id,
                'fecha' => $fechaPartido,
                'completado' => false,
                'resultado_local' => null,
                'resultado_visitante' => null,
                'puntos_local' => null,
                'puntos_visitante' => null,
            ]);

            CategoriaPartido::create([
                'categoria_id' => $categoria->id,
                'partido_id' => $partido->id,
                'fase' => 'semifinal',
                'numero_partido' => $semi['numero'],
                'dependencias' => json_encode($semi['dependencias']),
            ]);

            $debugInfo[] = "✓ {$semi['numero']}: {$semi['local']->nombre} vs {$semi['visitante']->nombre} - {$fechaPartido->format('d/m/Y H:i')}";
            $debugInfo[] = "  Dependencias: " . implode(", ", $semi['dependencias']);
        }
    }

    private function crearFinal($categoria, $ganadores, $fechaBase, &$debugInfo)
    {
        $debugInfo[] = "Creando final con equipos reales...";

        $fechaFinal = $fechaBase->copy()->addDays(2)->setTime(11, 0);

        $partido = Partido::create([
            'grupo_id' => null,
            'equipo_local_id' => $ganadores[0]['equipo']->id,
            'equipo_visitante_id' => $ganadores[1]['equipo']->id,
            'fecha' => $fechaFinal,
            'completado' => false,
            'resultado_local' => null,
            'resultado_visitante' => null,
            'puntos_local' => null,
            'puntos_visitante' => null,
        ]);

        CategoriaPartido::create([
            'categoria_id' => $categoria->id,
            'partido_id' => $partido->id,
            'fase' => 'final',
            'numero_partido' => 'F',
            'dependencias' => null,
        ]);

        $debugInfo[] = "✓ Final: {$ganadores[0]['equipo']->nombre} vs {$ganadores[1]['equipo']->nombre} - {$fechaFinal->format('d/m/Y H:i')}";
    }

    private function obtenerGanadoresFase($categoria, $fase, &$debugInfo)
    {
        $partidosFase = CategoriaPartido::where('categoria_id', $categoria->id)
            ->where('fase', $fase)
            ->with(['partido', 'partido.equipoLocal', 'partido.equipoVisitante'])
            ->orderBy('numero_partido')
            ->get();

        $ganadores = [];

        foreach ($partidosFase as $categoriaPartido) {
            $partido = $categoriaPartido->partido;

            if (!$partido->completado) {
                continue;
            }

            $ganador = null;
            if ($partido->resultado_local > $partido->resultado_visitante) {
                $ganador = $partido->equipoLocal;
            } elseif ($partido->resultado_visitante > $partido->resultado_local) {
                $ganador = $partido->equipoVisitante;
            }

            if ($ganador) {
                $ganadores[] = [
                    'equipo' => $ganador,
                    'numero_partido' => $categoriaPartido->numero_partido,
                    'partido_id' => $partido->id
                ];
            }
        }

        return $ganadores;
    }

    private function obtenerNombreFase($fase)
    {
        $nombres = [
            'cuartos' => 'Cuartos de Final',
            'semifinal' => 'Semifinales',
            'semifinal_directa' => 'Semifinales',
            'final' => 'Final',
            'final_directa' => 'Final'
        ];

        return $nombres[$fase] ?? $fase;
    }

    private function generarCuartosDeFinales($categoria, $equipos, $fechaBase, &$debugInfo): void
    {
        $debugInfo[] = "Generando cuartos de final...";

        $cruces = [
            ['local' => $equipos[0], 'visitante' => $equipos[7], 'numero' => 'QF1', 'hora' => 9],
            ['local' => $equipos[3], 'visitante' => $equipos[4], 'numero' => 'QF2', 'hora' => 11],
            ['local' => $equipos[1], 'visitante' => $equipos[6], 'numero' => 'QF3', 'hora' => 13],
            ['local' => $equipos[2], 'visitante' => $equipos[5], 'numero' => 'QF4', 'hora' => 15],
        ];

        foreach ($cruces as $cruce) {
            $fechaPartido = $fechaBase->copy()->setTime($cruce['hora'], 0);

            $partido = Partido::create([
                'grupo_id' => null,
                'equipo_local_id' => $cruce['local']['id'],
                'equipo_visitante_id' => $cruce['visitante']['id'],
                'fecha' => $fechaPartido,
                'completado' => false,
                'resultado_local' => null,
                'resultado_visitante' => null,
                'puntos_local' => null,
                'puntos_visitante' => null,
            ]);

            CategoriaPartido::create([
                'categoria_id' => $categoria->id,
                'partido_id' => $partido->id,
                'fase' => 'cuartos',
                'numero_partido' => $cruce['numero'],
                'dependencias' => null,
            ]);

            $debugInfo[] = "✓ {$cruce['numero']}: {$cruce['local']['nombre']} vs {$cruce['visitante']['nombre']} - {$fechaPartido->format('d/m/Y H:i')}";
        }
    }

    private function verificarTablasNecesarias(): void
    {
        if (!Schema::hasTable('equipo_categoria')) {
            throw new \Exception('La tabla equipo_categoria no existe. Ejecuta las migraciones primero.');
        }

        if (!Schema::hasTable('categoria_partido')) {
            throw new \Exception('La tabla categoria_partido no existe. Ejecuta la migración correspondiente.');
        }
    }

    private function verificarPartidosPendientes(Torneo $torneo): int
    {
        return Partido::whereHas('grupo', function($query) use ($torneo) {
            $query->where('torneo_id', $torneo->id);
        })
            ->where('completado', false)
            ->count();
    }

    private function analizarNormalizacionGrupos($grupos): array
    {
        $equiposPorGrupo = [];
        $minEquipos = PHP_INT_MAX;
        $maxEquipos = 0;

        foreach ($grupos as $grupo) {
            $cantidadEquipos = EquipoGrupo::where('grupo_id', $grupo->id)->count();
            $equiposPorGrupo[$grupo->id] = $cantidadEquipos;
            $minEquipos = min($minEquipos, $cantidadEquipos);
            $maxEquipos = max($maxEquipos, $cantidadEquipos);
        }

        return [
            'necesita_normalizacion' => $minEquipos != $maxEquipos,
            'equipos_por_grupo' => $equiposPorGrupo,
            'min_equipos' => $minEquipos,
            'max_equipos' => $maxEquipos,
            'grupos_a_normalizar' => array_filter($equiposPorGrupo, function($cantidad) use ($minEquipos) {
                return $cantidad > $minEquipos;
            })
        ];
    }

    private function normalizarGrupos($grupos, $infoNormalizacion, &$debugInfo): void
    {
        foreach ($infoNormalizacion['grupos_a_normalizar'] as $grupoId => $cantidadActual) {
            $grupo = $grupos->find($grupoId);
            $equiposAEliminar = $cantidadActual - $infoNormalizacion['min_equipos'];

            $debugInfo[] = "=== NORMALIZANDO GRUPO: {$grupo->nombre} ===";
            $debugInfo[] = "Equipos actuales: {$cantidadActual}, objetivo: {$infoNormalizacion['min_equipos']}";
            $debugInfo[] = "Equipos a eliminar: {$equiposAEliminar}";

            $equiposGrupo = EquipoGrupo::with('equipo')
                ->where('grupo_id', $grupoId)
                ->orderBy('posicion', 'desc')
                ->take($equiposAEliminar)
                ->get();

            foreach ($equiposGrupo as $equipoGrupo) {
                $debugInfo[] = "Eliminando: {$equipoGrupo->equipo->nombre}";

                Partido::where('grupo_id', $grupoId)
                    ->where(function($query) use ($equipoGrupo) {
                        $query->where('equipo_local_id', $equipoGrupo->equipo_id)
                            ->orWhere('equipo_visitante_id', $equipoGrupo->equipo_id);
                    })
                    ->delete();

                $equipoGrupo->delete();
            }

            $this->recalcularEstadisticasGrupo($grupoId, $debugInfo);
        }
    }

    private function recalcularEstadisticasGrupo(int $grupoId, &$debugInfo): void
    {
        $debugInfo[] = "Recalculando estadísticas del grupo...";

        $equiposGrupo = EquipoGrupo::where('grupo_id', $grupoId)->get();

        foreach ($equiposGrupo as $equipoGrupo) {
            $partidos = Partido::where('grupo_id', $grupoId)
                ->where('completado', true)
                ->where(function($query) use ($equipoGrupo) {
                    $query->where('equipo_local_id', $equipoGrupo->equipo_id)
                        ->orWhere('equipo_visitante_id', $equipoGrupo->equipo_id);
                })
                ->get();

            $estadisticas = $this->calcularEstadisticasEquipo($equipoGrupo->equipo_id, $partidos);

            $equipoGrupo->update($estadisticas);

            $debugInfo[] = "Estadísticas actualizadas para {$equipoGrupo->equipo->nombre}: {$estadisticas['puntos']}pts";
        }

        $this->recalcularPosicionesGrupo($grupoId, $debugInfo);
    }

    private function calcularEstadisticasEquipo(int $equipoId, $partidos): array
    {
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
            $esLocal = $partido->equipo_local_id == $equipoId;

            $setsEquipo = $esLocal ? $partido->resultado_local : $partido->resultado_visitante;
            $setsRival = $esLocal ? $partido->resultado_visitante : $partido->resultado_local;
            $puntosEquipo = $esLocal ? $partido->puntos_local : $partido->puntos_visitante;
            $puntosRival = $esLocal ? $partido->puntos_visitante : $partido->puntos_local;

            $estadisticas['partidos_jugados']++;
            $estadisticas['sets_favor'] += $setsEquipo;
            $estadisticas['sets_contra'] += $setsRival;
            $estadisticas['puntos_favor'] += $puntosEquipo ?? 0;
            $estadisticas['puntos_contra'] += $puntosRival ?? 0;

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

        return $estadisticas;
    }

    private function recalcularPosicionesGrupo(int $grupoId, &$debugInfo): void
    {
        $equiposOrdenados = EquipoGrupo::with('equipo')
            ->where('grupo_id', $grupoId)
            ->orderByRaw('puntos DESC, sets_favor DESC, sets_contra ASC, puntos_favor DESC, puntos_contra ASC, (puntos_favor - puntos_contra) DESC')
            ->get();

        foreach ($equiposOrdenados as $index => $equipoGrupo) {
            $nuevaPosicion = $index + 1;
            $equipoGrupo->update(['posicion' => $nuevaPosicion]);
            $debugInfo[] = "Nueva posición para {$equipoGrupo->equipo->nombre}: {$nuevaPosicion}º";
        }
    }

    private function obtenerClasificacionesGrupos($grupos): array
    {
        $clasificaciones = [];

        foreach ($grupos as $grupo) {
            $equiposGrupo = EquipoGrupo::with('equipo')
                ->where('grupo_id', $grupo->id)
                ->orderByRaw('puntos DESC, sets_favor DESC, sets_contra ASC, puntos_favor DESC, puntos_contra ASC, (puntos_favor - puntos_contra) DESC')
                ->get()
                ->map(function($equipoGrupo, $index) {
                    $equipoGrupo->posicion_calculada = $index + 1;
                    return $equipoGrupo;
                });

            $clasificaciones[$grupo->id] = [
                'grupo' => $grupo,
                'equipos' => $equiposGrupo
            ];
        }

        return $clasificaciones;
    }

    private function obtenerEquiposPorPosicion($grupos, &$debugInfo): array
    {
        $equiposPorPosicion = [];

        foreach ($grupos as $grupo) {
            $equiposGrupo = EquipoGrupo::with('equipo')
                ->where('grupo_id', $grupo->id)
                ->orderByRaw('puntos DESC, sets_favor DESC, sets_contra ASC, puntos_favor DESC, puntos_contra ASC, (puntos_favor - puntos_contra) DESC')
                ->get();

            if ($equiposGrupo->isEmpty()) {
                $debugInfo[] = "ADVERTENCIA: Grupo {$grupo->nombre} no tiene equipos";
                continue;
            }

            $debugInfo[] = "=== GRUPO: {$grupo->nombre} ===";

            foreach ($equiposGrupo as $index => $equipoGrupo) {
                $posicion = $index + 1;

                $equipoData = (object)[
                    'equipo_grupo_id' => $equipoGrupo->id,
                    'equipo_id' => $equipoGrupo->equipo->id,
                    'nombre' => $equipoGrupo->equipo->nombre,
                    'posicion' => $posicion,
                    'puntos' => $equipoGrupo->puntos,
                    'sets_favor' => $equipoGrupo->sets_favor,
                    'sets_contra' => $equipoGrupo->sets_contra,
                    'puntos_favor' => $equipoGrupo->puntos_favor,
                    'puntos_contra' => $equipoGrupo->puntos_contra,
                    'diferencia_sets' => $equipoGrupo->sets_favor - $equipoGrupo->sets_contra,
                    'diferencia_puntos' => $equipoGrupo->puntos_favor - $equipoGrupo->puntos_contra,
                    'grupo_nombre' => $grupo->nombre
                ];

                if (!isset($equiposPorPosicion[$posicion])) {
                    $equiposPorPosicion[$posicion] = [];
                }
                $equiposPorPosicion[$posicion][] = $equipoData;

                $debugInfo[] = "{$posicion}º {$equipoData->nombre} - {$equipoData->puntos}pts, SF:{$equipoData->sets_favor}, SC:{$equipoData->sets_contra}";
            }
        }

        foreach ($equiposPorPosicion as $posicion => $equipos) {
            usort($equiposPorPosicion[$posicion], function($a, $b) {
                if ($a->puntos != $b->puntos) return $b->puntos <=> $a->puntos;
                if ($a->sets_favor != $b->sets_favor) return $b->sets_favor <=> $a->sets_favor;
                if ($a->sets_contra != $b->sets_contra) return $a->sets_contra <=> $b->sets_contra;
                if ($a->puntos_favor != $b->puntos_favor) return $b->puntos_favor <=> $a->puntos_favor;
                if ($a->puntos_contra != $b->puntos_contra) return $a->puntos_contra <=> $b->puntos_contra;
                return $b->diferencia_puntos <=> $a->diferencia_puntos;
            });
        }

        return $equiposPorPosicion;
    }

    private function limpiarAsignacionesPrevias($categorias, &$debugInfo): void
    {
        $eliminados = EquipoCategoria::whereIn('categoria_id', $categorias->pluck('id'))->delete();
        $debugInfo[] = "Asignaciones previas eliminadas: " . $eliminados;

        $this->eliminarPartidosEliminatorios($categorias);
        $debugInfo[] = "Partidos eliminatorios limpiados";
    }

    private function realizarRepartoSegunPosiciones($equiposPorPosicion, $categorias, &$debugInfo): array
    {
        $numCategorias = $categorias->count();
        $asignaciones = array_fill(0, $numCategorias, []);

        $debugInfo[] = "=== INICIANDO REPARTO POR CATEGORÍAS ===";

        if ($numCategorias == 1) {
            $debugInfo[] = "Reparto para 1 categoría: todos los equipos";

            $todosEquipos = [];
            foreach ($equiposPorPosicion as $posicion => $equipos) {
                $todosEquipos = array_merge($todosEquipos, $equipos);
            }

            usort($todosEquipos, function($a, $b) {
                if ($a->posicion != $b->posicion) return $a->posicion <=> $b->posicion;
                return $b->puntos <=> $a->puntos;
            });

            $asignaciones[0] = $todosEquipos;

        } elseif ($numCategorias == 2) {
            $debugInfo[] = "Reparto para 2 categorías";

            $todosEquipos = [];
            foreach ($equiposPorPosicion as $posicion => $equipos) {
                foreach ($equipos as $equipo) {
                    $todosEquipos[] = $equipo;
                }
            }

            usort($todosEquipos, function($a, $b) {
                if ($a->posicion != $b->posicion) return $a->posicion <=> $b->posicion;
                return $b->puntos <=> $a->puntos;
            });

            $totalEquipos = count($todosEquipos);
            $mitad = ceil($totalEquipos / 2);

            $asignaciones[0] = array_slice($todosEquipos, 0, $mitad);
            $asignaciones[1] = array_slice($todosEquipos, $mitad);

        } elseif ($numCategorias == 3) {
            $debugInfo[] = "Reparto para 3 categorías";

            $todosEquipos = [];
            foreach ($equiposPorPosicion as $posicion => $equipos) {
                foreach ($equipos as $equipo) {
                    $todosEquipos[] = $equipo;
                }
            }

            usort($todosEquipos, function($a, $b) {
                if ($a->posicion != $b->posicion) return $a->posicion <=> $b->posicion;
                return $b->puntos <=> $a->puntos;
            });

            $totalEquipos = count($todosEquipos);
            $tercio = ceil($totalEquipos / 3);

            $asignaciones[0] = array_slice($todosEquipos, 0, $tercio);
            $asignaciones[1] = array_slice($todosEquipos, $tercio, $tercio);
            $asignaciones[2] = array_slice($todosEquipos, $tercio * 2);
        }

        foreach ($asignaciones as $catIndex => $equiposCategoria) {
            $categoria = $categorias[$catIndex];
            $debugInfo[] = "=== CATEGORÍA: {$categoria->nombre} ===";
            foreach ($equiposCategoria as $index => $equipo) {
                $debugInfo[] = ($index + 1) . ". {$equipo->nombre} ({$equipo->posicion}º de {$equipo->grupo_nombre}) - {$equipo->puntos}pts";
            }
        }

        return $asignaciones;
    }

    private function guardarAsignaciones($asignaciones, $categorias, &$debugInfo): int
    {
        $totalAsignaciones = 0;

        foreach ($asignaciones as $catIndex => $equiposCategoria) {
            $categoria = $categorias[$catIndex];
            $debugInfo[] = "Guardando asignaciones para categoría: " . $categoria->nombre;

            foreach ($equiposCategoria as $index => $equipoData) {
                try {
                    $equipoGrupoExiste = EquipoGrupo::find($equipoData->equipo_grupo_id);
                    if (!$equipoGrupoExiste) {
                        $debugInfo[] = "ERROR: EquipoGrupo ID {$equipoData->equipo_grupo_id} no existe";
                        continue;
                    }

                    $equipoCategoria = EquipoCategoria::create([
                        'equipo_grupo_id' => $equipoData->equipo_grupo_id,
                        'categoria_id' => $categoria->id,
                        'posicion' => $index + 1,
                    ]);

                    $totalAsignaciones++;
                    $debugInfo[] = "✓ Asignación creada: {$equipoData->nombre} -> {$categoria->nombre} (Pos: " . ($index + 1) . ")";

                } catch (\Exception $e) {
                    $debugInfo[] = "ERROR creando asignación: " . $e->getMessage();
                    throw $e;
                }
            }
        }

        return $totalAsignaciones;
    }

    private function obtenerEquiposCategoria($categoria)
    {
        return DB::table('equipo_categoria')
            ->join('equipo_grupo', 'equipo_categoria.equipo_grupo_id', '=', 'equipo_grupo.id')
            ->join('equipos', 'equipo_grupo.equipo_id', '=', 'equipos.id')
            ->join('grupos', 'equipo_grupo.grupo_id', '=', 'grupos.id')
            ->where('equipo_categoria.categoria_id', $categoria->id)
            ->select(
                'equipo_categoria.posicion as posicion_categoria',
                'equipos.id as equipo_id',
                'equipos.nombre as equipo_nombre',
                'grupos.nombre as grupo_nombre',
                'equipo_grupo.posicion as posicion_grupo',
                'equipo_grupo.puntos',
                'equipo_grupo.sets_favor',
                'equipo_grupo.sets_contra',
                'equipo_grupo.puntos_favor',
                'equipo_grupo.puntos_contra',
                'equipo_grupo.partidos_jugados'
            )
            ->orderBy('equipo_categoria.posicion')
            ->get();
    }

    private function obtenerPartidosCategoria($categoria)
    {
        if (!Schema::hasTable('categoria_partido')) {
            return collect();
        }

        return DB::table('categoria_partido')
            ->join('partidos', 'categoria_partido.partido_id', '=', 'partidos.id')
            ->leftJoin('equipos as local', 'partidos.equipo_local_id', '=', 'local.id')
            ->leftJoin('equipos as visitante', 'partidos.equipo_visitante_id', '=', 'visitante.id')
            ->where('categoria_partido.categoria_id', $categoria->id)
            ->select(
                'partidos.*',
                'categoria_partido.fase',
                'categoria_partido.numero_partido',
                'categoria_partido.dependencias',
                'local.nombre as equipo_local_nombre',
                'visitante.nombre as equipo_visitante_nombre'
            )
            ->orderBy('partidos.fecha')
            ->get()
            ->map(function($partido) {
                $partido->equipoLocal = $partido->equipo_local_nombre ? (object)['nombre' => $partido->equipo_local_nombre] : null;
                $partido->equipoVisitante = $partido->equipo_visitante_nombre ? (object)['nombre' => $partido->equipo_visitante_nombre] : null;
                return $partido;
            });
    }

    private function calcularEstadisticasCategoria($categoria)
    {
        $equiposCount = EquipoCategoria::where('categoria_id', $categoria->id)->count();
        $partidosCount = 0;
        $partidosCompletados = 0;

        if (Schema::hasTable('categoria_partido')) {
            $partidosCount = CategoriaPartido::where('categoria_id', $categoria->id)->count();
            $partidosCompletados = CategoriaPartido::where('categoria_id', $categoria->id)
                ->whereHas('partido', function($query) {
                    $query->where('completado', true);
                })
                ->count();
        }

        return [
            'equipos_asignados' => $equiposCount,
            'partidos_generados' => $partidosCount,
            'partidos_completados' => $partidosCompletados,
            'partidos_pendientes' => $partidosCount - $partidosCompletados,
        ];
    }

    private function tienePartidosEliminatorios($categoria): bool
    {
        if (!Schema::hasTable('categoria_partido')) {
            return false;
        }

        return CategoriaPartido::where('categoria_id', $categoria->id)->exists();
    }

    private function eliminarPartidosEliminatorios($categorias): void
    {
        if (!Schema::hasTable('categoria_partido')) {
            return;
        }

        $categoriaIds = $categorias->pluck('id');

        $partidoIds = CategoriaPartido::whereIn('categoria_id', $categoriaIds)
            ->pluck('partido_id');

        CategoriaPartido::whereIn('categoria_id', $categoriaIds)->delete();

        if ($partidoIds->isNotEmpty()) {
            Partido::whereIn('id', $partidoIds)->delete();
        }
    }
}
