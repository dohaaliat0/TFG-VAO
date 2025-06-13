<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Torneos') }}
            </h2>
            <a href="{{ route('torneos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Crear Torneo
            </a>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            @if($torneos->isEmpty())
                <p class="text-center py-4">No hay torneos registrados.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Nombre</th>
                                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Fecha Inicio</th>
                                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Fecha Fin</th>
                                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Grupos</th>
                                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Categorías</th>
                                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Estado</th>
                                <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($torneos as $torneo)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">{{ $torneo->nombre }}</td>
                                    <td class="py-3 px-4">{{ $torneo->fecha_inicio->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4">{{ $torneo->fecha_fin->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4">{{ $torneo->num_grupos }}</td>
                                    <td class="py-3 px-4">{{ $torneo->num_categorias }}</td>
                                    <td class="py-3 px-4">
                                        @if($torneo->estado == 'preparacion')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Preparación</span>
                                        @elseif($torneo->estado == 'en_curso')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">En curso</span>
                                        @else
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Finalizado</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('torneos.show', $torneo) }}" class="text-blue-600 hover:text-blue-900" style="margin-right: 5px">Ver</a>
                                            <a href="{{ route('torneos.edit', $torneo) }}" class="text-yellow-600 hover:text-yellow-900" style="margin-right: 5px">Editar</a>
                                            <a href="{{ route('horarios.index', $torneo) }}" class="text-purple-600 hover:text-purple-900" style="margin-right: 5px">Horarios</a>
                                            <a href="{{ route('categorias.index', ['torneo_id' => $torneo->id]) }}" class="text-green-600 hover:text-green-900" style="margin-right: 5px">Categorías</a>

                                            <form action="{{ route('torneos.destroy', $torneo) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Estás seguro de eliminar este torneo?')">Eliminar</button>
                                            </form>
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
</x-app-layout>
