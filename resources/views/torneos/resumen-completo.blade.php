<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🏆 Resumen Completo - {{ $torneo->nombre }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('torneos.show', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al Torneo
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Estadísticas Generales -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold mb-4 text-center">📊 Estadísticas Generales del Torneo</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ $estadisticasGenerales['total_equipos'] }}</div>
                            <div class="text-sm opacity-90">Equipos Participantes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ $estadisticasGenerales['total_partidos'] }}</div>
                            <div class="text-sm opacity-90">Partidos Totales</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ $estadisticasGenerales['total_sets'] }}</div>
                            <div class="text-sm opacity-90">Sets Jugados</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold">{{ $estadisticasGenerales['duracion_dias'] }}</div>
                            <div class="text-sm opacity-90">Días de Duración</div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-xl font-bold">{{ $estadisticasGenerales['total_grupos'] }}</div>
                            <div class="text-sm opacity-90">Grupos</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold">{{ $estadisticasGenerales['total_categorias'] }}</div>
                            <div class="text-sm opacity-90">Categorías</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold">{{ $estadisticasGenerales['total_partidos_eliminatorios'] }}</div>
                            <div class="text-sm opacity-90">Partidos Eliminatorios</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campeones por Categoría -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-6 text-center text-gray-800">🏆 Campeones por Categoría</h3>
                    <div class="grid grid-cols-1 md:grid-cols-{{ $torneo->categorias->count() }} gap-6">
                        @foreach($resultadosEliminatorias as $resultado)
                            <div class="text-center">
                                <div class="bg-gradient-to-b from-yellow-400 to-yellow-600 rounded-lg p-6 mb-4">
                                    <h4 class="text-xl font-bold text-white mb-2">{{ $resultado['categoria']->nombre }}</h4>
                                    @if($resultado['campeon'])
                                        <div class="bg-white rounded-lg p-4">
                                            <div class="text-2xl mb-2">🥇</div>
                                            <div class="font-bold text-gray-800">{{ $resultado['campeon']->nombre }}</div>
                                            <div class="text-sm text-gray-600">CAMPEÓN</div>
                                        </div>
                                    @else
                                        <div class="bg-white rounded-lg p-4">
                                            <div class="text-gray-500">Final no completada</div>
                                        </div>
                                    @endif
                                </div>
                                @if($resultado['subcampeon'])
                                    <div class="bg-gray-200 rounded-lg p-4">
                                        <div class="text-xl mb-1">🥈</div>
                                        <div class="font-medium text-gray-800">{{ $resultado['subcampeon']->nombre }}</div>
                                        <div class="text-sm text-gray-600">SUBCAMPEÓN</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Resultados de Eliminatorias -->
            @foreach($resultadosEliminatorias as $resultado)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-6 text-center text-gray-800">
                            🏅 Eliminatorias - {{ $resultado['categoria']->nombre }}
                        </h3>

                        <!-- Final -->
                        @if($resultado['final'])
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold mb-4 text-center bg-yellow-100 py-2 rounded">🏆 FINAL</h4>
                                <div class="max-w-md mx-auto">
                                    <div class="bg-gradient-to-r from-yellow-100 to-yellow-200 border-2 border-yellow-400 rounded-lg p-6">
                                        <div class="text-center">
                                            @if($resultado['final']->completado)
                                                <div class="grid grid-cols-3 gap-4 items-center">
                                                    <div class="text-right">
                                                        <div class="font-bold">{{ $resultado['final']->equipoLocal->nombre }}</div>
                                                        <div class="text-2xl font-bold {{ $resultado['final']->resultado_local > $resultado['final']->resultado_visitante ? 'text-green-600' : 'text-gray-500' }}">
                                                            {{ $resultado['final']->resultado_local }}
                                                        </div>
                                                    </div>
                                                    <div class="text-center text-gray-600">VS</div>
                                                    <div class="text-left">
                                                        <div class="font-bold">{{ $resultado['final']->equipoVisitante->nombre }}</div>
                                                        <div class="text-2xl font-bold {{ $resultado['final']->resultado_visitante > $resultado['final']->resultado_local ? 'text-green-600' : 'text-gray-500' }}">
                                                            {{ $resultado['final']->resultado_visitante }}
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($resultado['final']->puntos_local && $resultado['final']->puntos_visitante)
                                                    <div class="mt-2 text-sm text-gray-600">
                                                        Puntos: {{ $resultado['final']->puntos_local }} - {{ $resultado['final']->puntos_visitante }}
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-gray-500">Final pendiente</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Semifinales -->
                        @if($resultado['semifinales']->isNotEmpty())
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-4 text-center bg-orange-100 py-2 rounded">🥉 Semifinales</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
                                    @foreach($resultado['semifinales'] as $semifinal)
                                        <div class="bg-orange-50 border rounded-lg p-4">
                                            <div class="text-center font-medium text-sm mb-2">{{ $semifinal->numero_partido }}</div>
                                            @if($semifinal->completado && $semifinal->equipoLocal && $semifinal->equipoVisitante)
                                                <div class="grid grid-cols-3 gap-2 items-center text-sm">
                                                    <div class="text-right">
                                                        <div class="font-medium">{{ $semifinal->equipoLocal->nombre }}</div>
                                                        <div class="text-lg font-bold {{ $semifinal->resultado_local > $semifinal->resultado_visitante ? 'text-green-600' : 'text-gray-500' }}">
                                                            {{ $semifinal->resultado_local }}
                                                        </div>
                                                    </div>
                                                    <div class="text-center text-gray-500">vs</div>
                                                    <div class="text-left">
                                                        <div class="font-medium">{{ $semifinal->equipoVisitante->nombre }}</div>
                                                        <div class="text-lg font-bold {{ $semifinal->resultado_visitante > $semifinal->resultado_local ? 'text-green-600' : 'text-gray-500' }}">
                                                            {{ $semifinal->resultado_visitante }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center text-gray-500 text-sm">Pendiente</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Cuartos de Final -->
                        @if($resultado['cuartos']->isNotEmpty())
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-4 text-center bg-blue-100 py-2 rounded">⚡ Cuartos de Final</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                    @foreach($resultado['cuartos'] as $cuarto)
                                        <div class="bg-blue-50 border rounded-lg p-3">
                                            <div class="text-center font-medium text-xs mb-2">{{ $cuarto->numero_partido }}</div>
                                            @if($cuarto->completado)
                                                <div class="space-y-1 text-xs">
                                                    <div class="flex justify-between items-center">
                                                        <span class="truncate">{{ $cuarto->equipoLocal->nombre }}</span>
                                                        <span class="font-bold {{ $cuarto->resultado_local > $cuarto->resultado_visitante ? 'text-green-600' : 'text-gray-500' }}">
                                                            {{ $cuarto->resultado_local }}
                                                        </span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <span class="truncate">{{ $cuarto->equipoVisitante->nombre }}</span>
                                                        <span class="font-bold {{ $cuarto->resultado_visitante > $cuarto->resultado_local ? 'text-green-600' : 'text-gray-500' }}">
                                                            {{ $cuarto->resultado_visitante }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center text-gray-500 text-xs">Pendiente</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Clasificaciones Finales de Grupos -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-6 text-center text-gray-800">📋 Clasificaciones Finales por Grupo</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($clasificacionesGrupos as $clasificacion)
                            <div class="border rounded-lg overflow-hidden">
                                <div class="bg-gray-100 px-4 py-3 border-b">
                                    <h4 class="font-semibold text-gray-800">{{ $clasificacion['grupo']->nombre }}</h4>
                                </div>
                                <div class="p-4">
                                    @foreach($clasificacion['equipos'] as $equipo)
                                        <div class="flex justify-between items-center py-2 {{ $equipo->posicion == 1 ? 'bg-yellow-50 border-l-4 border-yellow-400 pl-2' : '' }}">
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full {{ $equipo->posicion == 1 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }} mr-2 text-sm font-medium">
                                                    {{ $equipo->posicion }}
                                                </span>
                                                <span class="font-medium {{ $equipo->posicion == 1 ? 'text-yellow-800' : 'text-gray-900' }}">
                                                    {{ $equipo->nombre }}
                                                </span>
                                            </div>
                                            <div class="text-right text-sm">
                                                <div class="font-bold">{{ $equipo->puntos }} pts</div>
                                                <div class="text-gray-500">{{ $equipo->sets_favor }}-{{ $equipo->sets_contra }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Estadísticas Destacadas -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-6 text-center text-gray-800">⭐ Estadísticas Destacadas</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Mejores Equipos -->
                        <div class="bg-green-50 rounded-lg p-4">
                            <h4 class="font-semibold text-green-800 mb-3">🏆 Mejores Equipos</h4>
                            <div class="space-y-2 text-sm">
                                @if($estadisticasEquipos['equipo_mas_puntos'])
                                    <div>
                                        <span class="font-medium">Más puntos:</span>
                                        <div class="text-green-700">{{ $estadisticasEquipos['equipo_mas_puntos']->nombre }} ({{ $estadisticasEquipos['equipo_mas_puntos']->puntos }} pts)</div>
                                    </div>
                                @endif
                                @if($estadisticasEquipos['equipo_mejor_diferencia_sets'])
                                    <div>
                                        <span class="font-medium">Mejor diferencia de sets:</span>
                                        <div class="text-green-700">{{ $estadisticasEquipos['equipo_mejor_diferencia_sets']->nombre }} (+{{ $estadisticasEquipos['equipo_mejor_diferencia_sets']->diferencia_sets }})</div>
                                    </div>
                                @endif
                                @if($estadisticasEquipos['equipo_mas_puntos_juego'])
                                    <div>
                                        <span class="font-medium">Más puntos de juego:</span>
                                        <div class="text-green-700">{{ $estadisticasEquipos['equipo_mas_puntos_juego']->nombre }} ({{ $estadisticasEquipos['equipo_mas_puntos_juego']->puntos_favor }})</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Partidos Destacados -->
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-800 mb-3">🔥 Partidos Destacados</h4>
                            <div class="space-y-2 text-sm">
                                @if($partidosDestacados['partido_mas_sets'])
                                    <div>
                                        <span class="font-medium">Partido más largo:</span>
                                        <div class="text-blue-700">
                                            {{ $partidosDestacados['partido_mas_sets']->equipoLocal->nombre }} vs {{ $partidosDestacados['partido_mas_sets']->equipoVisitante->nombre }}
                                            <div class="text-xs">({{ $partidosDestacados['partido_mas_sets']->sets_count }} sets)</div>
                                        </div>
                                    </div>
                                @endif
                                @if($partidosDestacados['partido_mas_puntos'])
                                    <div>
                                        <span class="font-medium">Más puntos totales:</span>
                                        <div class="text-blue-700">
                                            {{ $partidosDestacados['partido_mas_puntos']->equipoLocal->nombre }} vs {{ $partidosDestacados['partido_mas_puntos']->equipoVisitante->nombre }}
                                            <div class="text-xs">({{ $partidosDestacados['partido_mas_puntos']->puntos_local + $partidosDestacados['partido_mas_puntos']->puntos_visitante }} puntos)</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Información del Torneo -->
                        <div class="bg-purple-50 rounded-lg p-4">
                            <h4 class="font-semibold text-purple-800 mb-3">📅 Información del Torneo</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="font-medium">Fecha inicio:</span>
                                    <div class="text-purple-700">{{ $torneo->fecha_inicio->format('d/m/Y') }}</div>
                                </div>
                                <div>
                                    <span class="font-medium">Fecha fin:</span>
                                    <div class="text-purple-700">{{ $torneo->fecha_fin->format('d/m/Y') }}</div>
                                </div>
                                <div>
                                    <span class="font-medium">Estado:</span>
                                    <div class="text-purple-700">{{ ucfirst($torneo->estado) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer del Resumen -->
            <div class="bg-gray-100 rounded-lg p-6 text-center">
                <p class="text-gray-600">
                    Resumen generado el {{ now()->format('d/m/Y H:i') }} | 
                    Torneo: {{ $torneo->nombre }} | 
                    Estado: {{ ucfirst($torneo->estado) }}
                </p>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                font-size: 12px;
            }
            
            .break-page {
                page-break-before: always;
            }
        }
    </style>
</x-app-layout>
