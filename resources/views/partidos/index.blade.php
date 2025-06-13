<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Listado de Partidos
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Listado de partidos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($partidos->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500">No se encontraron partidos.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipos</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupo/Categoría</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resultado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($partidos as $partido)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($partido->fecha)
                                                {{ $partido->fecha->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-yellow-600">Sin programar</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                @if($partido->equipoLocal && $partido->equipoVisitante)
                                                    {{ $partido->equipoLocal->nombre }} vs {{ $partido->equipoVisitante->nombre }}
                                                @elseif($partido->equipoLocal)
                                                    {{ $partido->equipoLocal->nombre }} vs TBD
                                                @elseif($partido->equipoVisitante)
                                                    TBD vs {{ $partido->equipoVisitante->nombre }}
                                                @else
                                                    <span class="text-yellow-600">Equipos por definir</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($partido->grupo)
                                                <div class="text-sm text-gray-900">
                                                    {{ $partido->grupo->nombre }}
                                                </div>
                                                @if($partido->grupo->torneo)
                                                    <div class="text-xs text-gray-500">
                                                        {{ $partido->grupo->torneo->nombre }}
                                                    </div>
                                                @endif
                                            @elseif($partido->categoriaPartido && $partido->categoriaPartido->categoria)
                                                <div class="text-sm text-gray-900">
                                                    {{ $partido->categoriaPartido->categoria->nombre }}
                                                    <span class="text-xs text-blue-600 ml-1">
                                                        ({{ ucfirst($partido->categoriaPartido->fase ?? 'Eliminatoria') }})
                                                    </span>
                                                </div>
                                                @if($partido->categoriaPartido->categoria->torneo)
                                                    <div class="text-xs text-gray-500">
                                                        {{ $partido->categoriaPartido->categoria->torneo->nombre }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-yellow-600">Sin grupo/categoría</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($partido->completado)
                                                <span class="font-medium">{{ $partido->resultado_local }} - {{ $partido->resultado_visitante }}</span>
                                            @else
                                                <span class="text-gray-400">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($partido->completado)
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Completado
                                                </span>
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('partidos.show', $partido) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    Ver
                                                </a>
                                                @if(!$partido->completado && $partido->equipoLocal && $partido->equipoVisitante)
                                                    <a href="{{ route('partidos.registrar-resultado.form', $partido) }}" class="text-green-600 hover:text-green-900" style="margin-left: 5px">
                                                        Registrar Resultado
                                                    </a>
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
</x-app-layout>
