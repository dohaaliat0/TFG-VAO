<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Calendario: {{ $torneo->nombre }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('torneos.show', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver al Torneo
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 text-gray-900">
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Calendario de Partidos</h3>
                
                @if($fechas->isEmpty())
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                        <p>No hay horarios configurados para este torneo.</p>
                        <p class="mt-2">
                            <a href="{{ route('horarios.create', $torneo) }}" class="font-medium underline">Crear horarios</a>
                        </p>
                    </div>
                @else
                    <div x-data="{ selectedDate: '{{ $fechas->first() }}' }">
                        <div class="mb-4">
                            <label for="fecha" class="block text-sm font-medium text-gray-700">Seleccionar Fecha</label>
                            <select id="fecha" x-model="selectedDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($fechas as $fecha)
                                    <option value="{{ $fecha }}">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white tabla-horarios">
                                <thead class="bg-gray-100 border-b">
                                    <tr>
                                        <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Hora</th>
                                        @foreach($pistas as $pista)
                                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Pista {{ $pista }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $horariosAgrupados = $horarios->groupBy(function($horario) {
                                            return $horario->fecha->format('Y-m-d H:i');
                                        });
                                    @endphp
                                    
                                    @foreach($horariosAgrupados as $hora => $horariosHora)
                                        @php
                                            $fechaHora = \Carbon\Carbon::parse($hora);
                                            $soloFecha = $fechaHora->format('Y-m-d');
                                        @endphp
                                        
                                        <tr x-show="selectedDate === '{{ $soloFecha }}'" class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4 font-medium">{{ $fechaHora->format('H:i') }}</td>
                                            
                                            @foreach($pistas as $pista)
                                                <td class="py-3 px-4">
                                                    @php
                                                        $horarioPista = $horariosHora->firstWhere('pista', $pista);
                                                    @endphp
                                                    
                                                    @if($horarioPista && $horarioPista->partidos->isNotEmpty())
                                                        @php
                                                            $partido = $horarioPista->partidos->first();
                                                        @endphp
                                                        <div class="border rounded p-2 bg-blue-50">
                                                            <div class="font-medium">{{ $partido->grupo->nombre }}</div>
                                                            <div class="flex justify-between items-center">
                                                                <span>{{ $partido->equipoLocal->nombre }}</span>
                                                                @if($partido->completado)
                                                                    <span class="font-bold">{{ $partido->resultado_local }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="flex justify-between items-center">
                                                                <span>{{ $partido->equipoVisitante->nombre }}</span>
                                                                @if($partido->completado)
                                                                    <span class="font-bold">{{ $partido->resultado_visitante }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="mt-1 text-right">
                                                                <a href="{{ route('partidos.show', $partido) }}" class="text-xs text-blue-600 hover:text-blue-900">Editar</a>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-gray-500">Disponible</span>
                                                    @endif
                                                </td>
                                            @endforeach
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