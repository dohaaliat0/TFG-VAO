<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reparto Automático de Categorías - {{ $torneo->nombre }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('torneos.show', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
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
                        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                            <div class="font-bold">Información de depuración:</div>
                            <pre class="mt-2 text-xs overflow-x-auto">{{ session('debug_info') }}</pre>
                        </div>
                    @endif

                    <!-- Información sobre normalización -->
                    @if(isset($infoNormalizacion) && $infoNormalizacion['necesita_normalizacion'])
                        <div class="mb-6">
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            Normalización de Grupos Requerida
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>Los grupos tienen diferente número de equipos. Se aplicará normalización automática:</p>
                                            <ul class="mt-2 list-disc list-inside">
                                                @foreach($infoNormalizacion['equipos_por_grupo'] as $grupoId => $cantidad)
                                                    @php $grupo = $torneo->grupos->find($grupoId); @endphp
                                                    <li>
                                                        <strong>{{ $grupo->nombre }}:</strong> {{ $cantidad }} equipos
                                                        @if($cantidad > $infoNormalizacion['min_equipos'])
                                                            <span class="text-red-600">(se eliminarán {{ $cantidad - $infoNormalizacion['min_equipos'] }} equipos)</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <p class="mt-2">
                                                <strong>Objetivo:</strong> Todos los grupos tendrán {{ $infoNormalizacion['min_equipos'] }} equipos.
                                                Se eliminarán los resultados contra los equipos en última posición de cada grupo que tenga más equipos.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mb-6">
                            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">
                                            Grupos Equilibrados
                                        </h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p>Todos los grupos tienen el mismo número de equipos. No es necesaria la normalización.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Criterio de Ordenación</h3>
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        <strong>Orden de desempate:</strong><br>
                                        1º Más puntos → 2º Mayor Sets Favor → 3º Menor Sets Contra → 4º Mayor Puntos Favor → 5º Menor Puntos Contra → 6º Mayor diferencia de puntos
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Clasificaciones Actuales por Grupo</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            @foreach($clasificaciones as $grupoId => $data)
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-semibold mb-3">
                                        {{ $data['grupo']->nombre }}
                                        <span class="text-sm text-gray-500">({{ $data['equipos']->count() }} equipos)</span>
                                    </h4>
                                    <div class="space-y-2">
                                        @foreach($data['equipos'] as $equipo)
                                            <div class="text-sm">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-medium">{{ $equipo->posicion_calculada }}º {{ $equipo->equipo->nombre }}</span>
                                                    <span class="text-gray-500">{{ $equipo->puntos }} pts</span>
                                                </div>
                                                <div class="text-xs text-gray-400 ml-4">
                                                    SF: {{ $equipo->sets_favor }} | SC: {{ $equipo->sets_contra }} |
                                                    PF: {{ $equipo->puntos_favor }} | PC: {{ $equipo->puntos_contra }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- NUEVA SECCIÓN: Vista Previa del Reparto y Eliminatorias -->
                    @php
                        // Simular el reparto para mostrar la vista previa
                        $equiposPorPosicion = [];
                        foreach($clasificaciones as $grupoId => $data) {
                            foreach($data['equipos'] as $index => $equipo) {
                                $posicion = $index + 1;
                                if (!isset($equiposPorPosicion[$posicion])) {
                                    $equiposPorPosicion[$posicion] = [];
                                }
                                $equiposPorPosicion[$posicion][] = $equipo;
                            }
                        }

                        // Ordenar equipos dentro de cada posición
                        foreach ($equiposPorPosicion as $posicion => $equipos) {
                            usort($equiposPorPosicion[$posicion], function($a, $b) {
                                if ($a->puntos != $b->puntos) return $b->puntos <=> $a->puntos;
                                if ($a->sets_favor != $b->sets_favor) return $b->sets_favor <=> $a->sets_favor;
                                if ($a->sets_contra != $b->sets_contra) return $a->sets_contra <=> $b->sets_contra;
                                if ($a->puntos_favor != $b->puntos_favor) return $b->puntos_favor <=> $a->puntos_favor;
                                if ($a->puntos_contra != $b->puntos_contra) return $a->puntos_contra <=> $b->puntos_contra;
                                return ($b->puntos_favor - $b->puntos_contra) <=> ($a->puntos_favor - $a->puntos_contra);
                            });
                        }

                        // Calcular asignaciones
                        $numCategorias = $categorias->count();
                        $asignacionesPreview = array_fill(0, $numCategorias, []);

                        if ($numCategorias == 2) {
                            $primeros = $equiposPorPosicion[1] ?? [];
                            $segundos = $equiposPorPosicion[2] ?? [];
                            $terceros = $equiposPorPosicion[3] ?? [];

                            $asignacionesPreview[0] = array_merge(
                                array_slice($primeros, 0, 6),
                                array_slice($segundos, 0, 2)
                            );

                            $asignacionesPreview[1] = array_merge(
                                array_slice($segundos, 2, 6),
                                array_slice($terceros, 0, 2)
                            );
                        } elseif ($numCategorias == 3) {
                            $primeros = $equiposPorPosicion[1] ?? [];
                            $segundos = $equiposPorPosicion[2] ?? [];
                            $terceros = $equiposPorPosicion[3] ?? [];
                            $cuartos = $equiposPorPosicion[4] ?? [];

                            $asignacionesPreview[0] = array_merge(
                                array_slice($primeros, 0, 6),
                                array_slice($segundos, 0, 2)
                            );

                            $asignacionesPreview[1] = array_merge(
                                array_slice($segundos, 2, 6),
                                array_slice($terceros, 0, 2)
                            );

                            $asignacionesPreview[2] = array_merge(
                                array_slice($terceros, 2, 6),
                                array_slice($cuartos, 0, 2)
                            );
                        }
                    @endphp

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Vista Previa: Reparto y Eliminatorias</h3>

                        @foreach($asignacionesPreview as $catIndex => $equiposCategoria)
                            @if(count($equiposCategoria) >= 8)
                                @php $categoria = $categorias[$catIndex]; @endphp
                                <div class="mb-8 border rounded-lg p-6 bg-gray-50">
                                    <h4 class="text-xl font-bold mb-4 text-center text-blue-600">{{ $categoria->nombre }}</h4>

                                    <!-- Equipos asignados -->
                                    <div class="mb-6">
                                        <h5 class="font-semibold mb-3">Equipos Clasificados (8 equipos):</h5>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                            @foreach($equiposCategoria as $index => $equipo)
                                                <div class="bg-white p-2 rounded border text-sm">
                                                    <span class="font-medium">{{ $index + 1 }}.</span> {{ $equipo->equipo->nombre }}
                                                    <div class="text-xs text-gray-500">
                                                        {{ $equipo->posicion_calculada }}º {{ $equipo->grupo->nombre }} ({{ $equipo->puntos }}pts)
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Bracket de Eliminatorias -->
                                    <div class="space-y-6">
                                        <h5 class="font-semibold text-center">Bracket Eliminatorio</h5>

                                        <!-- Cuartos de Final -->
                                        <div>
                                            <h6 class="font-medium mb-3 text-center bg-yellow-100 py-2 rounded">Cuartos de Final</h6>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                @php
                                                    $cuartos = [
                                                        ['local' => $equiposCategoria[0] ?? null, 'visitante' => $equiposCategoria[7] ?? null, 'numero' => 'QF1'],
                                                        ['local' => $equiposCategoria[3] ?? null, 'visitante' => $equiposCategoria[4] ?? null, 'numero' => 'QF2'],
                                                        ['local' => $equiposCategoria[1] ?? null, 'visitante' => $equiposCategoria[6] ?? null, 'numero' => 'QF3'],
                                                        ['local' => $equiposCategoria[2] ?? null, 'visitante' => $equiposCategoria[5] ?? null, 'numero' => 'QF4'],
                                                    ];
                                                @endphp

                                                @foreach($cuartos as $cuarto)
                                                    <div class="bg-white border rounded-lg p-3">
                                                        <div class="text-center font-medium text-sm mb-2">{{ $cuarto['numero'] }}</div>
                                                        <div class="space-y-1">
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm">{{ $cuarto['local']->equipo->nombre ?? 'TBD' }}</span>
                                                                <span class="text-xs text-gray-500">{{ $cuarto['local'] ? $cuarto['local']->posicion_calculada . 'º' : '' }}</span>
                                                            </div>
                                                            <div class="text-center text-xs text-gray-400">vs</div>
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-sm">{{ $cuarto['visitante']->equipo->nombre ?? 'TBD' }}</span>
                                                                <span class="text-xs text-gray-500">{{ $cuarto['visitante'] ? $cuarto['visitante']->posicion_calculada . 'º' : '' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Semifinales -->
                                        <div>
                                            <h6 class="font-medium mb-3 text-center bg-orange-100 py-2 rounded">Semifinales</h6>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                                                <div class="bg-white border rounded-lg p-3">
                                                    <div class="text-center font-medium text-sm mb-2">SF1</div>
                                                    <div class="text-center text-sm">
                                                        <div>Ganador QF1</div>
                                                        <div class="text-xs text-gray-400">vs</div>
                                                        <div>Ganador QF2</div>
                                                    </div>
                                                </div>
                                                <div class="bg-white border rounded-lg p-3">
                                                    <div class="text-center font-medium text-sm mb-2">SF2</div>
                                                    <div class="text-center text-sm">
                                                        <div>Ganador QF3</div>
                                                        <div class="text-xs text-gray-400">vs</div>
                                                        <div>Ganador QF4</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Final -->
                                        <div>
                                            <h6 class="font-medium mb-3 text-center bg-yellow-200 py-2 rounded">🏆 FINAL</h6>
                                            <div class="max-w-sm mx-auto">
                                                <div class="bg-gradient-to-r from-yellow-100 to-yellow-200 border-2 border-yellow-400 rounded-lg p-4">
                                                    <div class="text-center font-bold text-lg mb-2">FINAL</div>
                                                    <div class="text-center text-sm">
                                                        <div>Ganador SF1</div>
                                                        <div class="text-xs text-gray-600 my-1">vs</div>
                                                        <div>Ganador SF2</div>
                                                    </div>
                                                    <div class="text-center text-xs text-gray-600 mt-2">
                                                        🥇 Campeón {{ $categoria->nombre }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Reparto por Categorías</h3>

                        @if($categorias->count() == 2)
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        <strong>Reparto para 2 categorías:</strong><br>
                                        • <strong>Categoría 1:</strong> 6 mejores primeros + 2 mejores segundos<br>
                                        • <strong>Categoría 2:</strong> 6 siguientes segundos + 2 mejores terceros
                                    </p>
                                </div>
                            </div>
                        @elseif($categorias->count() == 3)
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        <strong>Reparto para 3 categorías:</strong><br>
                                        • <strong>Categoría 1:</strong> 6 mejores primeros + 2 mejores segundos<br>
                                        • <strong>Categoría 2:</strong> 6 siguientes segundos + 2 mejores terceros<br>
                                        • <strong>Categoría 3:</strong> 6 siguientes terceros + 2 mejores cuartos
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-{{ $categorias->count() }} gap-4">
                            @foreach($categorias as $categoria)
                                <div class="border rounded-lg p-4 text-center">
                                    <h4 class="font-semibold">{{ $categoria->nombre }}</h4>
                                    <p class="text-sm text-gray-600">8 equipos</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <form action="{{ route('categorias.reparto', $torneo) }}" method="POST" onsubmit="return confirm('¿Estás seguro de realizar el reparto automático?{{ isset($infoNormalizacion) && $infoNormalizacion['necesita_normalizacion'] ? ' Se aplicará normalización de grupos eliminando equipos y sus resultados.' : '' }} Esta acción eliminará cualquier asignación previa.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Ejecutar Reparto Automático
                                @if(isset($infoNormalizacion) && $infoNormalizacion['necesita_normalizacion'])
                                    <span class="ml-1">(con Normalización)</span>
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
