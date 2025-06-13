<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Categorías: {{ $torneo->nombre }}
            </h2>
            <div class="flex space-x-2">
                <form action="{{ route('categorias.generar', $torneo) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Regenerar Categorías
                    </button>
                </form>
                <a href="{{ route('torneos.show', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver al Torneo
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 text-gray-900">
            <h3 class="text-lg font-semibold mb-4">Distribución de Categorías</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($torneo->categorias as $categoria)
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-gray-100 px-4 py-2 flex justify-between items-center">
                            <h4 class="font-semibold">{{ $categoria->nombre }}</h4>
                            <a href="{{ route('categorias.show', $categoria) }}" class="text-blue-600 hover:text-blue-900">Ver detalles</a>
                        </div>
                        <div class="p-4">
                            <h5 class="font-medium mb-2">Equipos ({{ $categoria->equipos->count() }})</h5>
                            @if($categoria->equipos->isEmpty())
                                <p class="text-sm text-gray-500">No hay equipos asignados</p>
                            @else
                                <div class="overflow-y-auto max-h-60">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr>
                                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pos</th>
                                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipo</th>
                                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($categoria->equipos->sortBy('pivot.posicion') as $equipo)
                                                @php
                                                    $equipoGrupo = DB::table('equipo_grupo')
                                                        ->where('id', $equipo->pivot->equipo_grupo_id)
                                                        ->first();
                                                    
                                                    $grupo = $equipoGrupo ? App\Models\Grupo::find($equipoGrupo->grupo_id) : null;
                                                    $equipoReal = App\Models\Equipo::find($equipoGrupo->equipo_id ?? null);
                                                @endphp
                                                
                                                @if($equipoReal && $grupo)
                                                    <tr class="border-b">
                                                        <td class="py-2 text-sm">{{ $equipo->pivot->posicion ?? '-' }}</td>
                                                        <td class="py-2 text-sm">{{ $equipoReal->nombre }}</td>
                                                        <td class="py-2 text-sm">{{ $grupo->nombre }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($torneo->categorias->isEmpty())
                <div class="text-center py-8">
                    <p class="text-gray-500 mb-4">No hay categorías creadas para este torneo.</p>
                    <form action="{{ route('categorias.generar', $torneo) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Generar Categorías
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>