<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Horarios: {{ $torneo->nombre }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('horarios.create', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Crear Horarios
                </a>
                <a href="{{ route('torneos.show', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver al Torneo
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 text-gray-900">
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Horarios del Torneo</h3>
                
                @if($horarios->isEmpty())
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                        <p>No hay horarios configurados para este torneo.</p>
                        <p class="mt-2">
                            <a href="{{ route('horarios.create', $torneo) }}" class="font-medium underline">Crear horarios</a>
                        </p>
                    </div>
                @else
                    <div class="mb-4 flex justify-between items-center">
                        <div>
                            <span class="font-medium">Total de horarios:</span> {{ $horarios->count() }}
                        </div>
                        <a href="{{ route('horarios.calendario', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Ver Calendario
                        </a>
                    </div>
                    
                    <div x-data="{ selectedPista: 'todas' }">
                        <div class="mb-4">
                            <label for="pista" class="block text-sm font-medium text-gray-700">Filtrar por Pista</label>
                            <select id="pista" x-model="selectedPista" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="todas">Todas las pistas</option>
                                @foreach($pistas as $pista)
                                    <option value="{{ $pista }}">Pista {{ $pista }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100 border-b">
                                    <tr>
                                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Fecha</th>
                                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Hora</th>
                                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Pista</th>
                                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Estado</th>
                                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Partido</th>
                                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($horarios as $horario)
                                        <tr 
                                            x-show="selectedPista === 'todas' || selectedPista === '{{ $horario->pista }}'"
                                            class="border-b hover:bg-gray-50"
                                        >
                                            <td class="py-3 px-4">{{ $horario->fecha->format('d/m/Y') }}</td>
                                            <td class="py-3 px-4">{{ $horario->fecha->format('H:i') }}</td>
                                            <td class="py-3 px-4">{{ $horario->pista }}</td>
                                            <td class="py-3 px-4">
                                                @if($horario->partidos->isEmpty())
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Disponible</span>
                                                @else
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Asignado</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                @if($horario->partidos->isNotEmpty())
                                                    @php
                                                        $partido = $horario->partidos->first();
                                                    @endphp
                                                    <div>
                                                        <span class="font-medium">{{ $partido->grupo->nombre }}:</span>
                                                        {{ $partido->equipoLocal->nombre }} vs {{ $partido->equipoVisitante->nombre }}
                                                    </div>
                                                @else
                                                    <span class="text-gray-500">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex space-x-2">
                                                    @if($horario->partidos->isNotEmpty())
                                                        <a href="{{ route('partidos.show', $horario->partidos->first()) }}" class="text-blue-600 hover:text-blue-900">Ver partido</a>
                                                    @endif
                                                    
                                                    <form action="{{ route('horarios.destroy', $horario) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro de eliminar este horario?')">Eliminar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>