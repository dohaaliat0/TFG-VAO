<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $torneo->nombre }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('torneos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-300">
                    ← Volver
                </a>
                @if($torneo->estado === 'preparacion')
                    <form action="{{ route('torneos.iniciar', $torneo) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Iniciar Torneo
                        </button>
                    </form>
                @elseif($torneo->estado === 'en_curso')
                    <form action="{{ route('torneos.finalizar', $torneo) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Finalizar Torneo
                        </button>
                    </form>

                    @php
                        // Verificar si todos los partidos de grupos están completados
                        $partidosPendientes = App\Models\Partido::whereHas('grupo', function($query) use ($torneo) {
                            $query->where('torneo_id', $torneo->id);
                        })->where('completado', false)->count();

                        // Verificar si ya hay partidos eliminatorios generados
                        $tieneEliminatorias = false;
                        if (Schema::hasTable('categoria_partido')) {
                            $tieneEliminatorias = App\Models\CategoriaPartido::whereHas('categoria', function($query) use ($torneo) {
                                $query->where('torneo_id', $torneo->id);
                            })->exists();
                        }
                    @endphp

                    @if($torneo->estado === 'en_curso' && $torneo->categorias->count() >= 2)
                        @if($partidosPendientes == 0 && !$tieneEliminatorias)
                            <!-- Botón destacado para reparto cuando todos los partidos están completos -->
                            <a href="{{ route('categorias.reparto.form', $torneo) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-widest hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg transform hover:scale-105 transition-all duration-200 animate-pulse">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                </svg>
                                🏆 Generar Cuartos de Final
                            </a>
                        @else
                            <!-- Botón normal para reparto en otros casos -->
                            <a href="{{ route('categorias.reparto.form', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                Reparto Categorías
                            </a>
                        @endif
                    @endif
                @elseif($torneo->estado === 'finalizado')
                    @php
                        // Verificar si todos los partidos están completados
                        $partidosGruposPendientes = App\Models\Partido::whereHas('grupo', function($query) use ($torneo) {
                            $query->where('torneo_id', $torneo->id);
                        })->where('completado', false)->count();

                        $partidosEliminatoriosPendientes = 0;
                        if (Schema::hasTable('categoria_partido')) {
                            $partidosEliminatoriosPendientes = App\Models\Partido::whereHas('categoriaPartido.categoria', function($query) use ($torneo) {
                                $query->where('torneo_id', $torneo->id);
                            })->where('completado', false)->count();
                        }

                        $todosPartidosCompletados = ($partidosGruposPendientes + $partidosEliminatoriosPendientes) === 0;
                    @endphp

                    @if($todosPartidosCompletados)
                        <a href="{{ route('torneos.resumen-completo', $torneo) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-widest hover:from-yellow-500 hover:via-yellow-600 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 shadow-lg transform hover:scale-105 transition-all duration-200 animate-pulse">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            🏆 Resumen Completo del Torneo
                        </a>
                    @else
                        <div class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Partidos Pendientes: {{ $partidosGruposPendientes + $partidosEliminatoriosPendientes }}
                        </div>
                    @endif
                @endif

                <a href="{{ route('torneos.edit', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                </a>

                @if($torneo->estado === 'preparacion')
                    <form action="{{ route('torneos.destroy', $torneo) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este torneo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Eliminar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if (session('debug_info'))
                <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative">
                    <details>
                        <summary class="font-bold cursor-pointer">Ver información de debug</summary>
                        <pre class="mt-2 text-xs overflow-x-auto whitespace-pre-wrap">{{ session('debug_info') }}</pre>
                    </details>
                </div>
            @endif

            <!-- Información del torneo -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Información General</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Estado:</dt>
                                        <dd class="text-sm text-gray-900">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $torneo->estado === 'preparacion' ? 'bg-yellow-100 text-yellow-800' :
                                                   ($torneo->estado === 'en_curso' ? 'bg-green-100 text-green-800' :
                                                   ($torneo->estado === 'finalizado' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $torneo->estado)) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Fecha inicio:</dt>
                                        <dd class="text-sm text-gray-900">{{ $torneo->fecha_inicio->format('d/m/Y') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Fecha fin:</dt>
                                        <dd class="text-sm text-gray-900">{{ $torneo->fecha_fin->format('d/m/Y') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Ubicación:</dt>
                                        <dd class="text-sm text-gray-900">{{ $torneo->ubicacion ?? 'No especificada' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Categorías:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @foreach($torneo->categorias as $categoria)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                                                    {{ $categoria->nombre }}
                                                </span>
                                            @endforeach
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Estructura</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Grupos:</dt>
                                        <dd class="text-sm text-gray-900">{{ $stats['grupos'] }} / {{ $torneo->num_grupos }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Equipos por grupo:</dt>
                                        <dd class="text-sm text-gray-900">{{ $torneo->equipos_por_grupo ?? 'Variable' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Total equipos:</dt>
                                        <dd class="text-sm text-gray-900">{{ $stats['equipos'] }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Total partidos:</dt>
                                        <dd class="text-sm text-gray-900">{{ $stats['partidos'] }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Partidos completados:</dt>
                                        <dd class="text-sm text-gray-900">
                                            <span class="font-medium {{ $stats['partidos_completados'] === $stats['partidos'] && $stats['partidos'] > 0 ? 'text-green-600' : 'text-gray-900' }}">
                                                {{ $stats['partidos_completados'] }} / {{ $stats['partidos'] }}
                                            </span>
                                            @if($stats['partidos'] > 0)
                                                <span class="text-xs text-gray-500 ml-1">
                                                    ({{ round(($stats['partidos_completados'] / $stats['partidos']) * 100) }}%)
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Descripción</h3>
                            <div class="bg-gray-50 rounded-lg p-4 h-full">
                                <p class="text-sm text-gray-700">
                                    {{ $torneo->descripcion ?? 'No hay descripción disponible.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerta para indicar el siguiente paso cuando todos los partidos de grupos están completos -->
            @if($torneo->estado === 'en_curso' && $partidosPendientes == 0 && !$tieneEliminatorias && $torneo->categorias->count() >= 2)
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                <strong>¡Todos los partidos de grupos están completos!</strong> Ahora puedes generar los cuartos de final haciendo clic en el botón "🏆 Generar Cuartos de Final" en la parte superior.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ✅ SECCIÓN DE ELIMINATORIAS POR CATEGORÍA -->
            @php
                // Obtener categorías con sus partidos eliminatorios
                $categoriasConEliminatorias = [];

                if (Schema::hasTable('categoria_partido')) {
                    foreach ($torneo->categorias as $categoria) {
                        $partidosCategoria = App\Models\CategoriaPartido::where('categoria_id', $categoria->id)
                            ->with(['partido.equipoLocal', 'partido.equipoVisitante'])
                            ->orderBy('fase')
                            ->orderBy('numero_partido')
                            ->get();

                        if ($partidosCategoria->count() > 0) {
                            // Agrupar por fase
                            $partidosPorFase = $partidosCategoria->groupBy('fase');

                            // Determinar estado actual de la categoría
                            $cuartosExisten = $partidosPorFase->has('cuartos');
                            $cuartosCompletos = $cuartosExisten && $partidosPorFase['cuartos']->every(function($cp) {
                                return $cp->partido->completado;
                            });

                            $semifinalesExisten = $partidosPorFase->has('semifinal');
                            $semifinalesCompletas = $semifinalesExisten && $partidosPorFase['semifinal']->every(function($cp) {
                                return $cp->partido->completado;
                            });

                            $finalExiste = $partidosPorFase->has('final');
                            $finalCompleta = $finalExiste && $partidosPorFase['final']->every(function($cp) {
                                return $cp->partido->completado;
                            });

                            // Determinar siguiente fase a generar
                            $siguienteFase = null;
                            if (!$cuartosExisten) {
                                // Verificar si hay suficientes equipos para cuartos
                                $equiposCount = App\Models\EquipoCategoria::where('categoria_id', $categoria->id)->count();
                                if ($equiposCount >= 8) {
                                    $siguienteFase = 'cuartos';
                                } elseif ($equiposCount >= 4) {
                                    $siguienteFase = 'semifinal_directa';
                                } elseif ($equiposCount >= 2) {
                                    $siguienteFase = 'final_directa';
                                }
                            } elseif ($cuartosExisten && $cuartosCompletos && !$semifinalesExisten) {
                                $siguienteFase = 'semifinal';
                            } elseif ($semifinalesExisten && $semifinalesCompletas && !$finalExiste) {
                                $siguienteFase = 'final';
                            }

                            $categoriasConEliminatorias[] = [
                                'categoria' => $categoria,
                                'partidos_por_fase' => $partidosPorFase,
                                'cuartos_existen' => $cuartosExisten,
                                'cuartos_completos' => $cuartosCompletos,
                                'semifinales_existen' => $semifinalesExisten,
                                'semifinales_completas' => $semifinalesCompletas,
                                'final_existe' => $finalExiste,
                                'final_completa' => $finalCompleta,
                                'siguiente_fase' => $siguienteFase
                            ];
                        } else {
                            // Categoría sin partidos eliminatorios
                            $equiposCount = App\Models\EquipoCategoria::where('categoria_id', $categoria->id)->count();
                            $siguienteFase = null;

                            if ($equiposCount >= 8) {
                                $siguienteFase = 'cuartos';
                            } elseif ($equiposCount >= 4) {
                                $siguienteFase = 'semifinal_directa';
                            } elseif ($equiposCount >= 2) {
                                $siguienteFase = 'final_directa';
                            }

                            if ($siguienteFase) {
                                $categoriasConEliminatorias[] = [
                                    'categoria' => $categoria,
                                    'partidos_por_fase' => collect(),
                                    'cuartos_existen' => false,
                                    'cuartos_completos' => false,
                                    'semifinales_existen' => false,
                                    'semifinales_completas' => false,
                                    'final_existe' => false,
                                    'final_completa' => false,
                                    'siguiente_fase' => $siguienteFase,
                                    'equipos_count' => $equiposCount
                                ];
                            }
                        }
                    }
                }

                // URL actual para volver después de la acción
                $urlActual = url()->current();

                // Nombres de fases para mostrar
                $nombresFases = [
                    'cuartos' => 'Cuartos de Final',
                    'semifinal' => 'Semifinales',
                    'semifinal_directa' => 'Semifinales Directas',
                    'final' => 'Final',
                    'final_directa' => 'Final Directa'
                ];
            @endphp

            @if(count($categoriasConEliminatorias) > 0)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Fases Eliminatorias por Categoría</h3>

                        <div class="space-y-6">
                            @foreach($categoriasConEliminatorias as $infoCategoria)
                                <div class="border rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b flex justify-between items-center">
                                        <h4 class="font-medium text-blue-800">{{ $infoCategoria['categoria']->nombre }}</h4>

                                        <div class="flex space-x-2">
                                            <a href="{{ route('categorias.show', $infoCategoria['categoria']) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Ver detalles →
                                            </a>

                                            @if($torneo->estado === 'en_curso' && $infoCategoria['siguiente_fase'])
                                                @php
                                                    $fase = $infoCategoria['siguiente_fase'];
                                                    $colorBoton = $fase === 'cuartos' ? 'bg-blue-600 hover:bg-blue-700' :
                                                                ($fase === 'semifinal' || $fase === 'semifinal_directa' ? 'bg-purple-600 hover:bg-purple-700' :
                                                                'bg-yellow-600 hover:bg-yellow-700');
                                                    $nombreFase = $nombresFases[$fase] ?? ucfirst($fase);

                                                    if ($fase === 'cuartos') {
                                                        $rutaGenerar = route('categorias.generar-cuartos', $infoCategoria['categoria']);
                                                    } elseif ($fase === 'semifinal') {
                                                        $rutaGenerar = route('categorias.generar-siguiente-fase-categoria', $infoCategoria['categoria']);
                                                    } elseif ($fase === 'semifinal_directa') {
                                                        $rutaGenerar = route('categorias.generar-semifinales-directas', $infoCategoria['categoria']);
                                                    } elseif ($fase === 'final') {
                                                        $rutaGenerar = route('categorias.generar-siguiente-fase-categoria', $infoCategoria['categoria']);
                                                    } elseif ($fase === 'final_directa') {
                                                        $rutaGenerar = route('categorias.generar-final-directa', $infoCategoria['categoria']);
                                                    }
                                                @endphp

                                                <form action="{{ $rutaGenerar }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="url_retorno" value="{{ $urlActual }}">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 {{ $colorBoton }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        Generar {{ $nombreFase }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="p-4">
                                        @if($infoCategoria['partidos_por_fase']->count() > 0)
                                            <div class="space-y-4">
                                                @foreach($infoCategoria['partidos_por_fase'] as $fase => $partidos)
                                                    <div class="border rounded-lg p-3 {{ $fase === 'cuartos' ? 'bg-blue-50' : ($fase === 'semifinal' ? 'bg-purple-50' : 'bg-yellow-50') }}">
                                                        <h5 class="font-medium {{ $fase === 'cuartos' ? 'text-blue-800' : ($fase === 'semifinal' ? 'text-purple-800' : 'text-yellow-800') }} mb-2">
                                                            {{ $nombresFases[$fase] ?? ucfirst($fase) }}
                                                            <span class="text-sm font-normal text-gray-500">
                                                                ({{ $partidos->where('partido.completado', true)->count() }}/{{ $partidos->count() }} completados)
                                                            </span>
                                                        </h5>

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                            @foreach($partidos as $categoriaPartido)
                                                                @php
                                                                    $partido = $categoriaPartido->partido;
                                                                @endphp
                                                                <div class="border rounded-lg p-2 {{ $partido->completado ? 'bg-green-50' : 'bg-white' }}">
                                                                    <div class="flex justify-between items-center text-xs text-gray-500 mb-1">
                                                                        <span>{{ $partido->fecha ? date('d/m/Y H:i', strtotime($partido->fecha)) : 'Fecha por definir' }}</span>
                                                                        <span class="px-2 py-0.5 rounded-full {{ $partido->completado ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                                            {{ $categoriaPartido->numero_partido }}
                                                                        </span>
                                                                    </div>

                                                                    <div class="flex justify-between items-center">
                                                                        <div class="text-center flex-1">
                                                                            <div class="font-medium text-sm">{{ $partido->equipoLocal ? $partido->equipoLocal->nombre : 'Por definir' }}</div>
                                                                            @if($partido->completado)
                                                                                <div class="text-lg font-bold {{ $partido->resultado_local > $partido->resultado_visitante ? 'text-green-600' : 'text-gray-600' }}">
                                                                                    {{ $partido->resultado_local }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="text-gray-500 font-bold mx-2">vs</div>
                                                                        <div class="text-center flex-1">
                                                                            <div class="font-medium text-sm">{{ $partido->equipoVisitante ? $partido->equipoVisitante->nombre : 'Por definir' }}</div>
                                                                            @if($partido->completado)
                                                                                <div class="text-lg font-bold {{ $partido->resultado_visitante > $partido->resultado_local ? 'text-green-600' : 'text-gray-600' }}">
                                                                                    {{ $partido->resultado_visitante }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    <div class="mt-1 flex justify-end">
                                                                        <a href="{{ route('partidos.show', $partido->id) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                                            {{ $partido->completado ? 'Ver resultado' : 'Gestionar partido' }} →
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm text-yellow-700">
                                                            No hay partidos eliminatorios generados para esta categoría.
                                                            @if(isset($infoCategoria['equipos_count']))
                                                                @if($infoCategoria['equipos_count'] >= 8)
                                                                    Hay {{ $infoCategoria['equipos_count'] }} equipos asignados. Puedes generar cuartos de final.
                                                                @elseif($infoCategoria['equipos_count'] >= 4)
                                                                    Hay {{ $infoCategoria['equipos_count'] }} equipos asignados. Puedes generar semifinales directamente.
                                                                @elseif($infoCategoria['equipos_count'] >= 2)
                                                                    Hay {{ $infoCategoria['equipos_count'] }} equipos asignados. Puedes generar la final directamente.
                                                                @else
                                                                    Se necesitan al menos 2 equipos asignados para generar eliminatorias.
                                                                @endif
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Gestión de grupos y equipos -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Grupos y Equipos</h3>

                        @if($torneo->estado === 'preparacion' && $torneo->grupos->count() < $torneo->num_grupos)
                            <button type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="document.getElementById('modal-crear-grupo').classList.remove('hidden')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Crear Grupo
                            </button>
                        @endif
                    </div>

                    @if($torneo->grupos->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($torneo->grupos as $grupo)
                                <div class="border rounded-lg overflow-hidden">
                                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b flex justify-between items-center">
                                        <h4 class="font-medium text-gray-800">{{ $grupo->nombre }}</h4>
                                        <a href="{{ route('grupos.show', $grupo) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Ver detalles →
                                        </a>
                                    </div>

                                    <div class="p-4">
                                        @php
                                            $equiposGrupo = $grupo->equipos()->get();
                                            $partidosGrupo = $grupo->partidos;
                                            $partidosCompletados = $partidosGrupo->where('completado', true)->count();
                                            $totalPartidos = $partidosGrupo->count();
                                            $porcentajeCompletado = $totalPartidos > 0 ? round(($partidosCompletados / $totalPartidos) * 100) : 0;
                                        @endphp

                                        <div class="mb-3">
                                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                                <span>Progreso de partidos</span>
                                                <span>{{ $partidosCompletados }}/{{ $totalPartidos }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $porcentajeCompletado }}%"></div>
                                            </div>
                                        </div>

                                        @if($equiposGrupo->count() > 0)
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pos</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipo</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pts</th>
                                                        <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PJ</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($equiposGrupo as $equipoGrupo)
                                                        <tr>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $equipoGrupo->pivot->posicion ?? '-' }}</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ $equipoGrupo->nombre }}</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $equipoGrupo->pivot->puntos ?? 0 }}</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                                                {{ App\Models\Partido::where(function($query) use ($equipoGrupo) {
                                                                    $query->where('equipo_local_id', $equipoGrupo->id)
                                                                          ->orWhere('equipo_visitante_id', $equipoGrupo->id);
                                                                })->where('grupo_id', $grupo->id)->count() }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4 text-gray-500 text-sm">
                                                No hay equipos asignados a este grupo.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No hay grupos creados para este torneo. Crea al menos un grupo para comenzar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

    <!-- Modal para crear grup -->
    <div id="modal-crear-grupo" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Crear Nuevo Grupo
                        </h3>
                        <div class="mt-2">
                            <form id="form-crear-grupo" action="{{ route('grupos.store', $torneo) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Grupo</label>
                                    <input type="text" name="nombre" id="nombre" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('form-crear-grupo').submit()">
                    Crear
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('modal-crear-grupo').classList.add('hidden')">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
