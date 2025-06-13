<!-- Sección de Partidos de Categorías -->
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold mb-4">Partidos de Categorías</h2>

    @if(Schema::hasTable('categoria_partido'))
        @php
            $partidosCategoria = DB::table('categoria_partido')
                ->join('partidos', 'categoria_partido.partido_id', '=', 'partidos.id')
                ->join('categorias', 'categoria_partido.categoria_id', '=', 'categorias.id')
                ->leftJoin('equipos as local', 'partidos.equipo_local_id', '=', 'local.id')
                ->leftJoin('equipos as visitante', 'partidos.equipo_visitante_id', '=', 'visitante.id')
                ->select(
                    'partidos.*',
                    'categorias.nombre as categoria_nombre',
                    'categoria_partido.fase',
                    'categoria_partido.numero_partido',
                    'local.nombre as equipo_local_nombre',
                    'visitante.nombre as equipo_visitante_nombre'
                )
                ->orderBy('categorias.nombre')
                ->orderBy('partidos.fecha')
                ->get();
        @endphp

        @if($partidosCategoria->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Categoría</th>
                        <th class="px-4 py-2 text-left">Fase</th>
                        <th class="px-4 py-2 text-left">Número</th>
                        <th class="px-4 py-2 text-left">Equipo Local</th>
                        <th class="px-4 py-2 text-left">Equipo Visitante</th>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Completado</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($partidosCategoria as $partido)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $partido->id }}</td>
                            <td class="px-4 py-2">{{ $partido->categoria_nombre }}</td>
                            <td class="px-4 py-2">{{ $partido->fase }}</td>
                            <td class="px-4 py-2">{{ $partido->numero_partido }}</td>
                            <td class="px-4 py-2">{{ $partido->equipo_local_nombre ?? 'TBD' }}</td>
                            <td class="px-4 py-2">{{ $partido->equipo_visitante_nombre ?? 'TBD' }}</td>
                            <td class="px-4 py-2">{{ $partido->fecha }}</td>
                            <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs rounded {{ $partido->completado ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $partido->completado ? 'Sí' : 'No' }}
                                    </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500">No hay partidos de categorías registrados.</p>
        @endif
    @else
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            La tabla categoria_partido no existe. Ejecuta las migraciones.
        </div>
    @endif
</div>
