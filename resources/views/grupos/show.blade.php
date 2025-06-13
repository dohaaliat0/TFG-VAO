<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Grupo: {{ $grupo->nombre }}
            </h2>
            <div class="flex space-x-2">
                @if($grupo->torneo->estado === 'preparacion')
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="document.getElementById('modal-eliminar-grupo').classList.remove('hidden')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar Grupo
                    </button>
                @endif
                <a href="{{ route('torneos.show', $grupo->torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver al Torneo
                </a>
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

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong>Errores de validación:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Información del grupo -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Información del Grupo</h3>
                            <p><span class="font-medium">Torneo:</span> {{ $grupo->torneo->nombre }}</p>
                            <p><span class="font-medium">Equipos:</span> {{ $grupo->equipos->count() }}</p>
                            <p><span class="font-medium">Partidos:</span> {{ $grupo->partidos->count() }}</p>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Acciones</h3>
                            <div class="flex flex-col space-y-2">
                                @if($grupo->torneo->estado === 'preparacion')
                                    <button type="button" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="document.getElementById('modal-agregar-equipo').classList.remove('hidden')">
                                        Agregar Equipo
                                    </button>

                                    @if($grupo->equipos->count() >= 2 && $grupo->partidos->count() === 0)
                                        <button type="button" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="document.getElementById('modal-generar-partidos').classList.remove('hidden')">
                                            Generar Partidos
                                        </button>
                                    @endif

                                    @if($grupo->partidos->count() > 0)
                                        <form action="{{ route('grupos.partidos.eliminar-todos', $grupo) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar TODOS los partidos de este grupo? Esta acción no se puede deshacer y también reseteará las estadísticas de los equipos.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Eliminar Todos los Partidos
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipos del grupo -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Equipos del Grupo</h3>

                    @if($grupo->equipos->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No hay equipos asignados a este grupo. Agrega equipos para poder generar partidos.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Pos</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Equipo</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">PJ</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">PG</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">PP</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">SF</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">SC</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">PF</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">PC</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">+/-</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Pts</th>
                                    @if($grupo->torneo->estado === 'preparacion')
                                        <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $equiposOrdenados = $grupo->equipos->sortBy(function($equipo) {
                                        return $equipo->pivot->posicion ?? 999;
                                    });
                                @endphp
                                @foreach($equiposOrdenados as $equipo)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-3 px-4">
                                            @if($equipo->pivot->posicion == 1)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z" />
                                                    </svg>
                                                    {{ $equipo->pivot->posicion ?? '-' }}
                                                </span>
                                            @else
                                                {{ $equipo->pivot->posicion ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">{{ $equipo->nombre }}</td>
                                        <td class="py-3 px-4 text-center">{{ $equipo->pivot->partidos_jugados }}</td>
                                        <td class="py-3 px-4 text-center">{{ $equipo->pivot->partidos_ganados_2_0 + $equipo->pivot->partidos_ganados_2_1 }}</td>
                                        <td class="py-3 px-4 text-center">{{ $equipo->pivot->partidos_perdidos_0_2 + $equipo->pivot->partidos_perdidos_1_2 }}</td>
                                        <td class="py-3 px-4 text-center">{{ $equipo->pivot->sets_favor }}</td>
                                        <td class="py-3 px-4 text-center">{{ $equipo->pivot->sets_contra }}</td>
                                        <td class="py-3 px-4 text-center">{{ $equipo->pivot->puntos_favor }}</td>
                                        <td class="py-3 px-4 text-center">{{ $equipo->pivot->puntos_contra }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @php
                                                $diferencia = $equipo->pivot->puntos_favor - $equipo->pivot->puntos_contra;
                                            @endphp
                                            <span class="@if($diferencia > 0) text-green-600 font-medium @elseif($diferencia < 0) text-red-600 font-medium @endif">
                                                @if($diferencia > 0)+@endif{{ $diferencia }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold">{{ $equipo->pivot->puntos }}</td>
                                        @if($grupo->torneo->estado === 'preparacion')
                                            <td class="py-3 px-4 text-center">
                                                <div class="flex justify-center space-x-2">
                                                    @if($equipo->pivot->posicion != 1)
                                                        <form action="{{ route('grupos.equipos.cabeza', ['grupo' => $grupo, 'equipo' => $equipo]) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-yellow-600 hover:text-yellow-900 text-xs" title="Marcar como cabeza de grupo">Cabeza</button>
                                                        </form>
                                                    @endif

                                                    <form action="{{ route('grupos.equipos.quitar', ['grupo' => $grupo, 'equipo' => $equipo]) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este equipo del grupo?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar del grupo">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Partidos del grupo -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Partidos del Grupo</h3>

                    @if($grupo->partidos->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No hay partidos generados para este grupo. Genera partidos para comenzar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100 border-b">
                                <tr>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Fecha</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Local</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Resultado</th>
                                    <th class="text-right py-3 px-4 uppercase font-semibold text-sm">Visitante</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Estado</th>
                                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($grupo->partidos->sortBy('fecha') as $partido)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-3 px-4">{{ $partido->fecha ? $partido->fecha->format('d/m/Y H:i') : 'Sin asignar' }}</td>
                                        <td class="py-3 px-4">{{ $partido->equipoLocal->nombre }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @if($partido->completado)
                                                <span class="font-bold">{{ $partido->resultado_local }} - {{ $partido->resultado_visitante }}</span>
                                            @else
                                                <span class="text-gray-500">vs</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">{{ $partido->equipoVisitante->nombre }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @if($partido->completado)
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Completado</span>
                                            @else
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex justify-center space-x-2">
                                                <a href="{{ route('partidos.show', $partido) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>

                                                @if($grupo->torneo->estado === 'preparacion' && !$partido->completado)
                                                    <form action="{{ route('partidos.destroy', $partido) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este partido?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar partido">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para eliminar grupo -->
    <div id="modal-eliminar-grupo" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Eliminar Grupo
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">
                                ¿Estás seguro de que deseas eliminar el grupo "<strong>{{ $grupo->nombre }}</strong>"?
                            </p>
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-yellow-800 mb-1">
                                            Esta acción no se puede deshacer y eliminará:
                                        </h4>
                                        <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
                                            <li>{{ $grupo->equipos->count() }} equipos del grupo</li>
                                            <li>{{ $grupo->partidos->count() }} partidos asociados</li>
                                            <li>Todas las estadísticas del grupo</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <form id="form-eliminar-grupo" action="{{ route('grupos.destroy', $grupo) }}" method="POST">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('form-eliminar-grupo').submit()">
                    Eliminar Grupo
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('modal-eliminar-grupo').classList.add('hidden')">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para agregar equipo -->
    <div id="modal-agregar-equipo" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Agregar Equipo al Grupo
                        </h3>
                        <div class="mt-2">
                            <form id="form-agregar-equipo" action="{{ route('grupos.equipos.agregar', $grupo) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="equipo_id" class="block text-sm font-medium text-gray-700">Seleccionar Equipo</label>
                                    <select name="equipo_ids[]" id="equipo_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="">Seleccionar...</option>
                                        @php
                                            $equiposDisponibles = App\Models\Equipo::where('torneo_id', $grupo->torneo_id)
                                                ->whereDoesntHave('grupos', function ($query) use ($grupo) {
                                                    $query->where('grupos.id', $grupo->id);
                                                })
                                                ->orderBy('nombre')->get();
                                        @endphp
                                        @foreach($equiposDisponibles as $equipo)
                                            <option value="{{ $equipo->id }}">{{ $equipo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="es_cabeza_grupo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Marcar como cabeza de grupo</span>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="submitAgregarEquipo()">
                    Agregar
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('modal-agregar-equipo').classList.add('hidden')">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para generar partidos -->
    <div id="modal-generar-partidos" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Generar Partidos
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">
                                Selecciona el tipo de competición que deseas generar:
                            </p>
                            <form id="form-generar-partidos" action="{{ route('grupos.partidos.generar', $grupo) }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input id="enfrentamiento-unico" name="tipo_partidos" type="radio" value="unico" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" checked>
                                        <label for="enfrentamiento-unico" class="ml-3 block text-sm font-medium text-gray-700">
                                            <span class="font-semibold">Enfrentamiento único</span>
                                            <span class="block text-xs text-gray-500">Cada equipo juega una vez contra cada otro (ideal para torneos)</span>
                                            <span class="block text-xs text-gray-400">Ejemplo con 4 equipos: 6 partidos total</span>
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="ida-vuelta" name="tipo_partidos" type="radio" value="ida_vuelta" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="ida-vuelta" class="ml-3 block text-sm font-medium text-gray-700">
                                            <span class="font-semibold">Ida y vuelta</span>
                                            <span class="block text-xs text-gray-500">Cada equipo juega dos veces contra cada otro (ideal para ligas)</span>
                                            <span class="block text-xs text-gray-400">Ejemplo con 4 equipos: 12 partidos total</span>
                                        </label>
                                    </div>
                                </div>
                                <input type="hidden" name="ida_vuelta" id="ida_vuelta_hidden" value="">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="generarPartidos()">
                    Generar Partidos
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('modal-generar-partidos').classList.add('hidden')">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        function generarPartidos() {
            const tipoPartidos = document.querySelector('input[name="tipo_partidos"]:checked').value;
            const idaVueltaHidden = document.getElementById('ida_vuelta_hidden');

            if (tipoPartidos === 'ida_vuelta') {
                idaVueltaHidden.value = '1';
            } else {
                idaVueltaHidden.value = '';
            }

            document.getElementById('form-generar-partidos').submit();
        }

        function submitAgregarEquipo() {
            const select = document.getElementById('equipo_id');
            if (select.value) {
                document.getElementById('form-agregar-equipo').submit();
            }
        }
    </script>
</x-app-layout>
