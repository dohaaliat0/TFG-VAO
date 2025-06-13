<?php

namespace App\Http\Controllers;

use App\Models\Torneo;
use App\Models\Grupo;
use App\Models\Equipo;
use App\Models\Partido;
use App\Models\EquipoGrupo;
use App\Models\Categoria;
use App\Models\CategoriaPartido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class TorneoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $torneos = Torneo::orderBy('created_at', 'desc')->paginate(10);

        // Obtener estadísticas para cada torneo
        foreach ($torneos as $torneo) {
            $torneo->stats = $this->obtenerEstadisticasTorneo($torneo);
        }

        return view('torneos.index', compact('torneos'));
    }

    public function create()
    {
        return view('torneos.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'num_grupos' => 'required|integer|min:1|max:20',
            'num_categorias' => 'required|integer|min:1|max:10',
        ], [
            'nombre.required' => 'El nombre del torneo es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'num_grupos.required' => 'El número de grupos es obligatorio.',
            'num_grupos.integer' => 'El número de grupos debe ser un número entero.',
            'num_grupos.min' => 'Debe haber al menos 1 grupo.',
            'num_grupos.max' => 'No puede haber más de 20 grupos.',
            'num_categorias.required' => 'El número de categorías es obligatorio.',
            'num_categorias.integer' => 'El número de categorías debe ser un número entero.',
            'num_categorias.min' => 'Debe haber al menos 1 categoría.',
            'num_categorias.max' => 'No puede haber más de 10 categorías.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $torneo = Torneo::create([
                'nombre' => $request->nombre,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'num_grupos' => $request->num_grupos,
                'num_categorias' => $request->num_categorias,
                'estado' => 'preparacion',
            ]);

            for ($i = 1; $i <= $request->num_categorias; $i++) {
                Categoria::create([
                    'nombre' => "Categoría {$i} - {$request->nombre}",
                    'torneo_id' => $torneo->id,
                    'descripcion' => "Categoría {$i} del torneo {$request->nombre}",
                ]);
            }

            for ($i = 0; $i < $request->num_grupos; $i++) {
                $letraGrupo = chr(65 + $i);
                $torneo->grupos()->create([
                    'nombre' => "Grupo {$letraGrupo}",
                ]);
            }

            DB::commit();

            return redirect()->route('torneos.show', $torneo)
                ->with('success', "Torneo creado correctamente con {$request->num_categorias} categorías y {$request->num_grupos} grupos.");
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('error', 'Error al crear el torneo: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Torneo $torneo)
    {
        $torneo->load(['grupos.equipos', 'categorias']);
        $stats = $this->obtenerEstadisticasTorneo($torneo);

        return view('torneos.show', compact('torneo', 'stats'));
    }

    public function edit(Torneo $torneo)
    {
        return view('torneos.edit', compact('torneo'));
    }

    public function update(Request $request, Torneo $torneo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:torneos,nombre,' . $torneo->id,
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'num_grupos' => 'required|integer|min:1|max:10',
            'equipos_por_grupo' => 'nullable|integer|min:2|max:20',
        ]);

        try {
            if ($torneo->grupos()->count() > $validated['num_grupos']) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'No se puede reducir el número de grupos porque ya hay grupos creados.');
            }

            $torneo->update($validated);

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('success', 'Torneo actualizado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error actualizando torneo: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el torneo. Inténtelo de nuevo.');
        }
    }

    public function destroy(Torneo $torneo)
    {
        try {
            DB::beginTransaction();

            if ($torneo->estado !== 'preparacion') {
                return redirect()
                    ->route('torneos.index')
                    ->with('error', 'Solo se pueden eliminar torneos en estado de preparación.');
            }

            foreach ($torneo->grupos as $grupo) {
                Partido::where('grupo_id', $grupo->id)->delete();

                EquipoGrupo::where('grupo_id', $grupo->id)->delete();
            }

            Grupo::where('torneo_id', $torneo->id)->delete();

            foreach ($torneo->categorias as $categoria) {
                if (Schema::hasTable('categoria_partido')) {
                    $partidosIds = CategoriaPartido::where('categoria_id', $categoria->id)->pluck('partido_id');
                    CategoriaPartido::where('categoria_id', $categoria->id)->delete();
                    Partido::whereIn('id', $partidosIds)->delete();
                }
            }

            Categoria::where('torneo_id', $torneo->id)->delete();

            $torneo->delete();

            DB::commit();

            return redirect()
                ->route('torneos.index')
                ->with('success', 'Torneo eliminado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error eliminando torneo: ' . $e->getMessage());

            return redirect()
                ->route('torneos.index')
                ->with('error', 'Error al eliminar el torneo. Inténtelo de nuevo.');
        }
    }

    public function iniciar(Torneo $torneo)
    {
        try {
            if ($torneo->estado !== 'preparacion') {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'El torneo ya ha sido iniciado.');
            }

            if ($torneo->grupos()->count() === 0) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'El torneo debe tener al menos un grupo para poder iniciarse.');
            }

            if ($torneo->categorias()->count() === 0) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'El torneo debe tener al menos una categoría para poder iniciarse.');
            }

            $gruposSinEquipos = [];
            foreach ($torneo->grupos as $grupo) {
                $equiposCount = $grupo->equipos()->count();
                if ($equiposCount < 2) {
                    $gruposSinEquipos[] = $grupo->nombre;
                }
            }

            if (!empty($gruposSinEquipos)) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'Los siguientes grupos necesitan al menos 2 equipos: ' . implode(', ', $gruposSinEquipos));
            }

            $gruposSinPartidos = [];
            foreach ($torneo->grupos as $grupo) {
                $partidosCount = $grupo->partidos()->count();
                if ($partidosCount === 0) {
                    $gruposSinPartidos[] = $grupo->nombre;
                }
            }

            if (!empty($gruposSinPartidos)) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'Los siguientes grupos no tienen partidos generados: ' . implode(', ', $gruposSinPartidos));
            }

            $torneo->update(['estado' => 'en_curso']);

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('success', 'El torneo ha sido iniciado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error iniciando torneo: ' . $e->getMessage());

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('error', 'Error al iniciar el torneo. Inténtelo de nuevo.');
        }
    }

    public function finalizar(Torneo $torneo)
    {
        try {
            if ($torneo->estado !== 'en_curso') {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'El torneo no está en curso.');
            }

            $torneo->update(['estado' => 'finalizado']);

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('success', 'El torneo ha sido finalizado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error finalizando torneo: ' . $e->getMessage());

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('error', 'Error al finalizar el torneo. Inténtelo de nuevo.');
        }
    }

    public function eliminatorias(Torneo $torneo)
    {
        try {
            if ($torneo->estado === 'preparacion') {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'El torneo debe estar en curso o finalizado para ver las eliminatorias.');
            }

            if (!Schema::hasTable('categoria_partido')) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'La tabla categoria_partido no existe. Ejecuta las migraciones primero.');
            }

            $datosEliminatorias = [];
            $estadisticasGenerales = [
                'total_partidos' => 0,
                'partidos_completados' => 0,
                'porcentaje_completado' => 0,
                'categorias_con_campeon' => 0
            ];

            foreach ($torneo->categorias as $categoria) {
                $partidosCategoria = CategoriaPartido::where('categoria_id', $categoria->id)
                    ->with(['partido.equipoLocal', 'partido.equipoVisitante'])
                    ->get();

                if ($partidosCategoria->count() > 0) {
                    $partidosPorFase = $partidosCategoria->groupBy('fase');

                    $totalPartidos = $partidosCategoria->count();
                    $partidosCompletados = $partidosCategoria->filter(function($cp) {
                        return $cp->partido->completado;
                    })->count();

                    $tieneCampeon = $partidosPorFase->has('final') &&
                        $partidosPorFase['final']->first()->partido->completado;

                    if ($tieneCampeon) {
                        $estadisticasGenerales['categorias_con_campeon']++;
                    }

                    $datosEliminatorias[$categoria->id] = [
                        'categoria' => $categoria,
                        'partidos_por_fase' => $partidosPorFase,
                        'total_partidos' => $totalPartidos,
                        'partidos_completados' => $partidosCompletados,
                        'porcentaje_completado' => $totalPartidos > 0 ? round(($partidosCompletados / $totalPartidos) * 100) : 0,
                        'tiene_campeon' => $tieneCampeon
                    ];

                    $estadisticasGenerales['total_partidos'] += $totalPartidos;
                    $estadisticasGenerales['partidos_completados'] += $partidosCompletados;
                }
            }

            if ($estadisticasGenerales['total_partidos'] > 0) {
                $estadisticasGenerales['porcentaje_completado'] = round(
                    ($estadisticasGenerales['partidos_completados'] / $estadisticasGenerales['total_partidos']) * 100
                );
            }

            $estadisticasGrupos = [
                'total_partidos' => 0,
                'partidos_completados' => 0,
                'porcentaje_completado' => 0
            ];

            foreach ($torneo->grupos as $grupo) {
                $partidosGrupo = $grupo->partidos;
                $estadisticasGrupos['total_partidos'] += $partidosGrupo->count();
                $estadisticasGrupos['partidos_completados'] += $partidosGrupo->where('completado', true)->count();
            }

            if ($estadisticasGrupos['total_partidos'] > 0) {
                $estadisticasGrupos['porcentaje_completado'] = round(
                    ($estadisticasGrupos['partidos_completados'] / $estadisticasGrupos['total_partidos']) * 100
                );
            }

            return view('torneos.eliminatorias', compact(
                'torneo',
                'datosEliminatorias',
                'estadisticasGenerales',
                'estadisticasGrupos'
            ));

        } catch (\Exception $e) {
            Log::error('Error cargando eliminatorias: ' . $e->getMessage());

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('error', 'Error al cargar las eliminatorias: ' . $e->getMessage());
        }
    }

    public function resumenCompleto(Torneo $torneo)
    {
        try {
            if ($torneo->estado !== 'finalizado') {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', 'El torneo debe estar finalizado para ver el resumen completo.');
            }

            $partidosPendientes = Partido::whereHas('grupo', function($query) use ($torneo) {
                $query->where('torneo_id', $torneo->id);
            })->where('completado', false)->count();

            if (Schema::hasTable('categoria_partido')) {
                $partidosPendientes += Partido::whereHas('categoriaPartido.categoria', function($query) use ($torneo) {
                    $query->where('torneo_id', $torneo->id);
                })->where('completado', false)->count();
            }

            if ($partidosPendientes > 0) {
                return redirect()
                    ->route('torneos.show', $torneo)
                    ->with('error', "Hay {$partidosPendientes} partidos pendientes. Complete todos los partidos antes de ver el resumen.");
            }

            $resumenGrupos = [];
            foreach ($torneo->grupos as $grupo) {
                $equiposGrupo = EquipoGrupo::with('equipo')
                    ->where('grupo_id', $grupo->id)
                    ->orderBy('posicion')
                    ->get();

                $resumenGrupos[$grupo->id] = [
                    'grupo' => $grupo,
                    'equipos' => $equiposGrupo
                ];
            }

            $campeones = [];
            if (Schema::hasTable('categoria_partido')) {
                foreach ($torneo->categorias as $categoria) {
                    $finalPartido = CategoriaPartido::where('categoria_id', $categoria->id)
                        ->where('fase', 'final')
                        ->with(['partido.equipoLocal', 'partido.equipoVisitante'])
                        ->first();

                    if ($finalPartido && $finalPartido->partido->completado) {
                        $partido = $finalPartido->partido;
                        $campeon = null;
                        $subcampeon = null;

                        if ($partido->resultado_local > $partido->resultado_visitante) {
                            $campeon = $partido->equipoLocal;
                            $subcampeon = $partido->equipoVisitante;
                        } else {
                            $campeon = $partido->equipoVisitante;
                            $subcampeon = $partido->equipoLocal;
                        }

                        $campeones[$categoria->id] = [
                            'categoria' => $categoria,
                            'campeon' => $campeon,
                            'subcampeon' => $subcampeon,
                            'resultado' => "{$partido->resultado_local}-{$partido->resultado_visitante}"
                        ];
                    }
                }
            }

            $estadisticas = [
                'total_equipos' => Equipo::whereHas('grupos', function($query) use ($torneo) {
                    $query->where('torneo_id', $torneo->id);
                })->count(),
                'total_partidos' => Partido::whereHas('grupo', function($query) use ($torneo) {
                    $query->where('torneo_id', $torneo->id);
                })->count(),
                'total_categorias' => $torneo->categorias->count(),
                'total_campeones' => count($campeones)
            ];

            if (Schema::hasTable('categoria_partido')) {
                $estadisticas['total_partidos'] += Partido::whereHas('categoriaPartido.categoria', function($query) use ($torneo) {
                    $query->where('torneo_id', $torneo->id);
                })->count();
            }

            return view('torneos.resumen-completo', compact(
                'torneo',
                'resumenGrupos',
                'campeones',
                'estadisticas'
            ));

        } catch (\Exception $e) {
            Log::error('Error cargando resumen completo: ' . $e->getMessage());

            return redirect()
                ->route('torneos.show', $torneo)
                ->with('error', 'Error al cargar el resumen completo: ' . $e->getMessage());
        }
    }

    private function obtenerEstadisticasTorneo(Torneo $torneo): array
    {
        $stats = [
            'grupos' => $torneo->grupos()->count(),
            'equipos' => 0,
            'partidos' => 0,
            'partidos_completados' => 0,
        ];

        $stats['equipos'] = Equipo::whereHas('grupos', function($query) use ($torneo) {
            $query->where('torneo_id', $torneo->id);
        })->count();

        $partidosGrupos = Partido::whereHas('grupo', function($query) use ($torneo) {
            $query->where('torneo_id', $torneo->id);
        })->get();

        $stats['partidos'] += $partidosGrupos->count();
        $stats['partidos_completados'] += $partidosGrupos->where('completado', true)->count();

        if (Schema::hasTable('categoria_partido')) {
            $partidosEliminatorias = Partido::whereHas('categoriaPartido.categoria', function($query) use ($torneo) {
                $query->where('torneo_id', $torneo->id);
            })->get();

            $stats['partidos'] += $partidosEliminatorias->count();
            $stats['partidos_completados'] += $partidosEliminatorias->where('completado', true)->count();
        }

        return $stats;
    }
}
