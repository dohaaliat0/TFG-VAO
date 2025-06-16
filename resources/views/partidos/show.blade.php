<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Partido: {{ $partido->equipoLocal->nombre }} vs {{ $partido->equipoVisitante->nombre }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('torneos.show', $partido->equipoLocal->torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-300">
                    ← Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 text-gray-900">
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Información del Partido</h3>
                    @if($partido->grupo)
                        <p><span class="font-medium">Grupo:</span> {{ $partido->grupo->nombre }}</p>
                        <p><span class="font-medium">Torneo:</span> {{ $partido->grupo->torneo->nombre }}</p>
                    @elseif($partido->categoriaPartido)
                        <p><span class="font-medium">Categoría:</span> {{ $partido->categoriaPartido->categoria->nombre }}</p>
                        <p><span class="font-medium">Fase:</span>
                            @if($partido->categoriaPartido->fase == 'cuartos')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Cuartos de Final</span>
                            @elseif($partido->categoriaPartido->fase == 'semifinal')
                                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs">Semifinal</span>
                            @elseif($partido->categoriaPartido->fase == 'final')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Final</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">{{ ucfirst($partido->categoriaPartido->fase) }}</span>
                            @endif
                        </p>
                        <p><span class="font-medium">Torneo:</span> {{ $partido->categoriaPartido->categoria->torneo->nombre }}</p>
                    @endif
                    <p><span class="font-medium">Estado:</span>
                        @if($partido->completado)
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Completado</span>
                        @else
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pendiente</span>
                        @endif
                    </p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Equipos</h3>
                    <p><span class="font-medium">Local:</span> {{ $partido->equipoLocal->nombre }}</p>
                    <p><span class="font-medium">Visitante:</span> {{ $partido->equipoVisitante->nombre }}</p>
                    @if($partido->completado)
                        <div class="mt-2">
                            <p><span class="font-medium">Resultado:</span>
                                <span class="text-lg font-bold">{{ $partido->resultado_local }} - {{ $partido->resultado_visitante }}</span>
                            </p>
                            @if($partido->puntos_local && $partido->puntos_visitante)
                                <p><span class="font-medium">Puntos totales:</span>
                                    {{ $partido->puntos_local }} - {{ $partido->puntos_visitante }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Horario</h3>
                    @if($partido->fecha)
                        <p><span class="font-medium">Fecha:</span> {{ $partido->fecha->format('d/m/Y') }}</p>
                        <p><span class="font-medium">Hora:</span> {{ $partido->fecha->format('H:i') }}</p>
                    @else
                        <p class="text-gray-500">Horario no asignado</p>
                    @endif

                    @php
                        $horarioAsignado = null;
                        if ($partido->fecha) {
                            $torneo = $partido->grupo ? $partido->grupo->torneo : ($partido->categoriaPartido ? $partido->categoriaPartido->categoria->torneo : null);
                            if ($torneo) {
                                $horarioAsignado = App\Models\Horario::where('torneo_id', $torneo->id)
                                    ->where('fecha', $partido->fecha)
                                    ->first();
                            }
                        }
                    @endphp

                    @if($horarioAsignado)
                        <p><span class="font-medium">Pista:</span> {{ $horarioAsignado->pista }}</p>
                    @else
                        <p class="text-gray-500">Pista no asignada</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ activeTab: 'resultado' }">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex">
                    <button @click="activeTab = 'resultado'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'resultado', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'resultado' }" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Resultado
                    </button>
                    <button @click="activeTab = 'horario'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'horario', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'horario' }" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Asignar Horario
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <div x-show="activeTab === 'resultado'">
                    <h3 class="text-lg font-semibold mb-4">Resultado del Partido</h3>

                    @if(!$partido->completado)
                        <div class="mb-6 flex justify-center">
                            <a href="{{ route('partidos.registrar-resultado.form', $partido) }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Registrar Resultado
                            </a>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="text-center p-4 border rounded-lg">
                            <p class="text-sm font-medium text-gray-700">{{ $partido->equipoLocal->nombre }}</p>
                            <p class="text-3xl font-bold text-blue-600">{{ $partido->resultado_local ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Sets ganados</p>
                        </div>

                        <div class="flex items-center justify-center">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-400">VS</p>
                                @if($partido->completado)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Finalizado</span>
                                @else
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pendiente</span>
                                @endif
                            </div>
                        </div>

                        <div class="text-center p-4 border rounded-lg">
                            <p class="text-sm font-medium text-gray-700">{{ $partido->equipoVisitante->nombre }}</p>
                            <p class="text-3xl font-bold text-red-600">{{ $partido->resultado_visitante ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Sets ganados</p>
                        </div>
                    </div>

                    @if($partido->sets->isNotEmpty())
                        <h4 class="font-medium mb-4">Detalle por Sets</h4>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Set</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $partido->equipoLocal->nombre }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $partido->equipoVisitante->nombre }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ganador</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($partido->sets->sortBy('numero_set') as $set)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Set {{ $set->numero_set }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm {{ $set->puntos_local > $set->puntos_visitante ? 'font-bold text-green-600' : 'text-gray-900' }}">
                                            {{ $set->puntos_local }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm {{ $set->puntos_visitante > $set->puntos_local ? 'font-bold text-green-600' : 'text-gray-900' }}">
                                            {{ $set->puntos_visitante }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                            @if($set->puntos_local > $set->puntos_visitante)
                                                <span class="text-green-600 font-medium">{{ $partido->equipoLocal->nombre }}</span>
                                            @elseif($set->puntos_visitante > $set->puntos_local)
                                                <span class="text-green-600 font-medium">{{ $partido->equipoVisitante->nombre }}</span>
                                            @else
                                                <span class="text-gray-400">Empate</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($partido->puntos_local && $partido->puntos_visitante)
                            <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                <h5 class="font-medium mb-2">Puntos Totales del Partido</h5>
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <p class="text-sm text-gray-600">{{ $partido->equipoLocal->nombre }}</p>
                                        <p class="text-2xl font-bold text-blue-600">{{ $partido->puntos_local }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">{{ $partido->equipoVisitante->nombre }}</p>
                                        <p class="text-2xl font-bold text-red-600">{{ $partido->puntos_visitante }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No hay sets registrados para este partido.</p>
                        </div>
                    @endif

                    @if($partido->completado && $partido->categoriaPartido)
                        <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                            <h5 class="font-medium mb-2">Información de Eliminatorias</h5>
                            <p class="text-sm text-blue-700">
                                @if($partido->resultado_local > $partido->resultado_visitante)
                                    <span class="font-medium">{{ $partido->equipoLocal->nombre }}</span> avanza a la siguiente fase.
                                @else
                                    <span class="font-medium">{{ $partido->equipoVisitante->nombre }}</span> avanza a la siguiente fase.
                                @endif
                            </p>

                            @php
                                $siguientesPartidos = \App\Models\CategoriaPartido::whereJsonContains('dependencias', 'ganador_' . $partido->categoriaPartido->numero_partido)
                                    ->with('partido')
                                    ->get();
                            @endphp

                            @if($siguientesPartidos->isNotEmpty())
                                <div class="mt-2">
                                    <p class="text-sm text-blue-700">Próximo partido:</p>
                                    <ul class="list-disc list-inside text-sm text-blue-600 mt-1">
                                        @foreach($siguientesPartidos as $siguientePartido)
                                            <li>
                                                @if($siguientePartido->fase == 'semifinal')
                                                    Semifinal
                                                @elseif($siguientePartido->fase == 'final')
                                                    Final
                                                @else
                                                    {{ ucfirst($siguientePartido->fase) }}
                                                @endif
                                                - {{ $siguientePartido->numero_partido }}
                                                @if($siguientePartido->partido->fecha)
                                                    ({{ $siguientePartido->partido->fecha->format('d/m/Y H:i') }})
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Pestaña de Horario -->
                <div x-show="activeTab === 'horario'">
                    <h3 class="text-lg font-semibold mb-4">Asignar Horario y Pista</h3>

                    @php
                        $torneo = null;
                        if ($partido->grupo) {
                            $torneo = $partido->grupo->torneo;
                        } elseif ($partido->categoriaPartido) {
                            $torneo = $partido->categoriaPartido->categoria->torneo;
                        }

                        // Obtener todos los horarios del torneo
                        $horarios = [];
                        if ($torneo) {
                            $horarios = App\Models\Horario::where('torneo_id', $torneo->id)
                                ->orderBy('fecha')
                                ->get();

                            // Obtener fechas de partidos existentes (excepto el actual)
                            $fechasOcupadas = DB::table('partidos')
                                ->where('id', '!=', $partido->id)
                                ->whereNotNull('fecha')
                                ->pluck('fecha')
                                ->toArray();

                            // Filtrar horarios disponibles
                            $horarios = $horarios->filter(function($horario) use ($fechasOcupadas) {
                                return !in_array($horario->fecha, $fechasOcupadas);
                            });
                        }
                    @endphp

                    @if(empty($horarios))
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                            <p>No hay horarios disponibles para este torneo.</p>
                            @if($torneo)
                                <p class="mt-2">
                                    <a href="{{ route('torneos.show', $torneo) }}" class="font-medium underline">Ver horarios del torneo</a>
                                </p>
                            @endif
                        </div>
                    @else
                        <form action="{{ route('partidos.horario.asignar', $partido) }}" method="POST">
                            @csrf

                            <div class="mb-6">
                                <label for="horario_id" class="block text-sm font-medium text-gray-700">Seleccionar Horario y Pista</label>
                                <select name="horario_id" id="horario_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($horarios as $horario)
                                        <option value="{{ $horario->id }}" {{ $partido->fecha && $partido->fecha->equalTo($horario->fecha) ? 'selected' : '' }}>
                                            {{ $horario->fecha->format('d/m/Y H:i') }} - {{ $horario->pista }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Asignar Horario
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
