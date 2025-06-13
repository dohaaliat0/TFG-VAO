<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Categoría: {{ $categoria->nombre }} - {{ $categoria->torneo->nombre }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('categorias.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver
                </a>
                <a href="{{ route('categorias.edit', $categoria) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Editar
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

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Información de la Categoría</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><span class="font-medium">Nombre:</span> {{ $categoria->nombre }}</p>
                                <p><span class="font-medium">Torneo:</span>
                                    <a href="{{ route('torneos.show', $categoria->torneo) }}" class="text-blue-600 hover:text-blue-900">
                                        {{ $categoria->torneo->nombre }}
                                    </a>
                                </p>
                            </div>
                            <div>
                                <p><span class="font-medium">Equipos:</span> {{ $equiposCategoria->count() }} equipos</p>
                                <p><span class="font-medium">Creada:</span> {{ $categoria->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if($categoria->descripcion)
                            <div class="mt-4">
                                <p><span class="font-medium">Descripción:</span> {{ $categoria->descripcion }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Equipos en esta Categoría</h3>

                        @if($equiposCategoria->isEmpty())
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                                <p>No hay equipos asignados a esta categoría.</p>
                                <p class="mt-2 text-sm">Los equipos se asignan automáticamente después de completar todos los partidos de grupos usando el botón "Reparto Categorías" en la vista del torneo.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Pos. Categoría
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Equipo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Grupo Original
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Pos. Grupo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Puntos
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sets
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Puntos Juego
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($equiposCategoria as $equipoData)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $equipoData->posicion_categoria }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <a href="{{ route('equipos.show', $equipoData->equipo_id) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                                    {{ $equipoData->equipo_nombre }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $equipoData->grupo_nombre }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $equipoData->posicion_grupo }}º
                                                @if($equipoData->posicion_grupo == 1)
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        1º
                                                    </span>
                                                @elseif($equipoData->posicion_grupo == 2)
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        2º
                                                    </span>
                                                @elseif($equipoData->posicion_grupo == 3)
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        3º
                                                    </span>
                                                @elseif($equipoData->posicion_grupo == 4)
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        4º
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="font-medium">{{ $equipoData->puntos }}</span> pts
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="text-green-600">{{ $equipoData->sets_favor }}</span> -
                                                <span class="text-red-600">{{ $equipoData->sets_contra }}</span>
                                                <div class="text-xs text-gray-500">
                                                    ({{ $equipoData->sets_favor - $equipoData->sets_contra > 0 ? '+' : '' }}{{ $equipoData->sets_favor - $equipoData->sets_contra }})
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="text-green-600">{{ $equipoData->puntos_favor }}</span> -
                                                <span class="text-red-600">{{ $equipoData->puntos_contra }}</span>
                                                <div class="text-xs text-gray-500">
                                                    ({{ $equipoData->puntos_favor - $equipoData->puntos_contra > 0 ? '+' : '' }}{{ $equipoData->puntos_favor - $equipoData->puntos_contra }})
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    @if($partidosCategoria->isNotEmpty())
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">Bracket Eliminatorio</h3>

                            <div class="space-y-6">
                                @php
                                    $cuartos = $partidosCategoria->filter(function($partido) {
                                        return $partido->fase === 'cuartos';
                                    });
                                @endphp

                                @if($cuartos->isNotEmpty())
                                    <div>
                                        <h4 class="font-medium text-gray-900 mb-3">Cuartos de Final</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($cuartos as $partido)
                                                <div class="border rounded-lg p-4">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <span class="text-sm font-medium">{{ $partido->numero_partido }}</span>
                                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($partido->fecha)->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <div class="text-center">
                                                            <div class="font-medium">{{ $partido->equipoLocal ? $partido->equipoLocal->nombre : 'TBD' }}</div>
                                                            @if($partido->completado)
                                                                <div class="text-2xl font-bold">{{ $partido->resultado_local }}</div>
                                                            @endif
                                                        </div>
                                                        <div class="text-gray-500">vs</div>
                                                        <div class="text-center">
                                                            <div class="font-medium">{{ $partido->equipoVisitante ? $partido->equipoVisitante->nombre : 'TBD' }}</div>
                                                            @if($partido->completado)
                                                                <div class="text-2xl font-bold">{{ $partido->resultado_visitante }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 flex justify-end">
                                                        <a href="{{ route('partidos.show', $partido->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                                            Ver detalles
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @php
                                    $semifinales = $partidosCategoria->filter(function($partido) {
                                        return $partido->fase === 'semifinal';
                                    });
                                @endphp

                                @if($semifinales->isNotEmpty())
                                    <div>
                                        <h4 class="font-medium text-gray-900 mb-3">Semifinales</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($semifinales as $partido)
                                                <div class="border rounded-lg p-4">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <span class="text-sm font-medium">{{ $partido->numero_partido }}</span>
                                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($partido->fecha)->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <div class="text-center">
                                                            <div class="font-medium">
                                                                {{ $partido->equipoLocal ? $partido->equipoLocal->nombre : 'Por definir' }}
                                                            </div>
                                                            @if($partido->completado && $partido->equipoLocal)
                                                                <div class="text-2xl font-bold">{{ $partido->resultado_local }}</div>
                                                            @endif
                                                        </div>
                                                        <div class="text-gray-500">vs</div>
                                                        <div class="text-center">
                                                            <div class="font-medium">
                                                                {{ $partido->equipoVisitante ? $partido->equipoVisitante->nombre : 'Por definir' }}
                                                            </div>
                                                            @if($partido->completado && $partido->equipoVisitante)
                                                                <div class="text-2xl font-bold">{{ $partido->resultado_visitante }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($partido->equipoLocal && $partido->equipoVisitante)
                                                        <div class="mt-2 flex justify-end">
                                                            <a href="{{ route('partidos.show', $partido->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                                                Ver detalles
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @php
                                    $final = $partidosCategoria->filter(function($partido) {
                                        return $partido->fase === 'final';
                                    })->first();
                                @endphp

                                @if($final)
                                    <div>
                                        <h4 class="font-medium text-gray-900 mb-3">Final</h4>
                                        <div class="max-w-md mx-auto">
                                            <div class="border rounded-lg p-6 bg-yellow-50">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-lg font-bold">FINAL</span>
                                                    <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($final->fecha)->format('d/m/Y H:i') }}</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <div class="text-center">
                                                        <div class="font-medium">
                                                            {{ $final->equipoLocal ? $final->equipoLocal->nombre : 'Por definir' }}
                                                        </div>
                                                        @if($final->completado && $final->equipoLocal)
                                                            <div class="text-3xl font-bold">{{ $final->resultado_local }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-gray-500 text-xl">vs</div>
                                                    <div class="text-center">
                                                        <div class="font-medium">
                                                            {{ $final->equipoVisitante ? $final->equipoVisitante->nombre : 'Por definir' }}
                                                        </div>
                                                        @if($final->completado && $final->equipoVisitante)
                                                            <div class="text-3xl font-bold">{{ $final->resultado_visitante }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($final->equipoLocal && $final->equipoVisitante)
                                                    <div class="mt-4 flex justify-center">
                                                        <a href="{{ route('partidos.show', $final->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                                            Ver detalles
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <a href="{{ route('categorias.index', ['torneo_id' => $categoria->torneo->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver a la lista
                        </a>

                        <div class="flex space-x-2">
                            <a href="{{ route('categorias.edit', $categoria) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Editar
                            </a>

                            <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('¿Estás seguro que deseas eliminar esta categoría?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
