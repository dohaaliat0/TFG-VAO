<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Estadísticas del Torneo</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-500">Equipos</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['equipos'] }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-500">Grupos</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['grupos'] }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-500">Categorías</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['categorias'] }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-500">Sets Jugados</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['sets'] }}</div>
            </div>
        </div>

        <div class="mt-6">
            <div class="flex justify-between items-center mb-2">
                <div class="text-sm font-medium text-gray-500">Progreso del Torneo</div>
                <div class="text-sm font-medium text-gray-900">{{ $stats['partidos']['porcentaje_completado'] }}%</div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $stats['partidos']['porcentaje_completado'] }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-2">
                <div>Partidos completados: {{ $stats['partidos']['completados'] }}/{{ $stats['partidos']['total'] }}</div>
                <div>Partidos pendientes: {{ $stats['partidos']['pendientes'] }}</div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-sm font-medium text-gray-500">Estado</div>
                <div class="mt-1">
                    @if($torneo->estado === 'preparacion')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            En Preparación
                        </span>
                    @elseif($torneo->estado === 'en_curso')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            En Curso
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                            Finalizado
                        </span>
                    @endif
                </div>
            </div>

            <div>
                <div class="text-sm font-medium text-gray-500">Fecha Inicio</div>
                <div class="mt-1 text-sm text-gray-900">{{ $torneo->fecha_inicio->format('d/m/Y') }}</div>
            </div>

            <div>
                <div class="text-sm font-medium text-gray-500">Fecha Fin</div>
                <div class="mt-1 text-sm text-gray-900">{{ $torneo->fecha_fin->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</div>
