<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalles del Equipo') }}
            </h2>
            <a href="{{ route('equipos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <div class="flex flex-col items-center">
                                @if($equipo->logo)
                                    <img src="{{ asset('storage/' . $equipo->logo) }}" alt="{{ $equipo->nombre }}" class="h-32 w-32 object-contain mb-4">
                                @else
                                    <div class="h-32 w-32 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                                        <span class="text-4xl text-gray-600">{{ substr($equipo->nombre, 0, 1) }}</span>
                                    </div>
                                @endif
                                <h3 class="text-xl font-bold text-gray-900">{{ $equipo->nombre }}</h3>
                                @if ($equipo->telefono_contacto)
                                    <p class="text-sm text-gray-600 mt-1">Tel: {{ $equipo->telefono_contacto }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Grupos en los que participa</h3>

                            @if($equipo->grupos->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($equipo->grupos as $grupo)
                                        <div class="border rounded-lg p-4">
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium">{{ $grupo->nombre }}</span>
                                                <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded-full">{{ $grupo->torneo->nombre }}</span>
                                            </div>

                                            <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                                <div>
                                                    <span class="text-gray-500">Puntos:</span>
                                                    <span class="font-medium">{{ $grupo->pivot->puntos }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Partidos:</span>
                                                    <span class="font-medium">{{ $grupo->pivot->partidos_jugados }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Ganados:</span>
                                                    <span class="font-medium">{{ $grupo->pivot->partidos_ganados_2_0 + $grupo->pivot->partidos_ganados_2_1 }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Perdidos:</span>
                                                    <span class="font-medium">{{ $grupo->pivot->partidos_perdidos_0_2 + $grupo->pivot->partidos_perdidos_1_2 }}</span>
                                                </div>
                                            </div>

                                            <div class="mt-2 flex justify-end">
                                                <a href="{{ route('grupos.show', $grupo) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                                    Ver grupo
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                Este equipo no está asignado a ningún grupo actualmente.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Próximos partidos</h3>

                        @php
                            $proximosPartidos = App\Models\Partido::where(function($query) use ($equipo) {
                                $query->where('equipo_local_id', $equipo->id)
                                    ->orWhere('equipo_visitante_id', $equipo->id);
                            })
                            ->where('completado', false)
                            ->orderBy('fecha')
                            ->take(5)
                            ->get();
                        @endphp

                        @if($proximosPartidos->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Torneo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">vs</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitante</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($proximosPartidos as $partido)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $partido->fecha->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        {{ $partido->grupo->torneo->nombre }}
                                                    </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $partido->equipoLocal->nombre }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="text-sm text-gray-500">vs</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $partido->equipoVisitante->nombre }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="{{ route('partidos.show', $partido) }}" class="text-blue-600 hover:text-blue-900">
                                                    Ver detalles
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p class="text-sm text-gray-700">No hay próximos partidos programados para este equipo.</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="{{ route('equipos.edit', $equipo) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar Equipo
                        </a>

                        <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este equipo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Eliminar Equipo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
