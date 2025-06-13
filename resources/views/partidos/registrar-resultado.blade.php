<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registrar Resultado') }}
            </h2>
            <a href="{{ route('partidos.show', $partido) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex flex-col md:flex-row justify-between items-center">
                                <div class="text-center md:text-left mb-4 md:mb-0">
                                    @php
                                        $esPartidoEliminatorio = isset($partido->categoriaPartido);
                                        $torneo = null;
                                        $nombreCategoria = null;
                                        $fase = null;

                                        if ($esPartidoEliminatorio) {
                                            $torneo = $partido->categoriaPartido->categoria->torneo ?? null;
                                            $nombreCategoria = $partido->categoriaPartido->categoria->nombre ?? 'Categoría';
                                            $fase = $partido->categoriaPartido->fase ?? null;
                                        } else {
                                            $torneo = $partido->grupo->torneo ?? null;
                                        }

                                        $nombreTorneo = $torneo ? $torneo->nombre : 'Torneo';

                                        // Formatear nombre de fase
                                        $nombreFase = '';
                                        if ($fase === 'cuartos') {
                                            $nombreFase = 'Cuartos de Final';
                                        } elseif ($fase === 'semifinal') {
                                            $nombreFase = 'Semifinales';
                                        } elseif ($fase === 'final') {
                                            $nombreFase = 'Final';
                                        }
                                    @endphp

                                    <h3 class="text-lg font-medium text-gray-900">{{ $nombreTorneo }}</h3>

                                    @if($esPartidoEliminatorio)
                                        <p class="text-sm text-gray-500">Categoría: {{ $nombreCategoria }}</p>
                                        <p class="text-sm font-medium text-red-600">{{ $nombreFase }}</p>
                                    @else
                                        <p class="text-sm text-gray-500">Grupo: {{ $partido->grupo->nombre ?? 'Sin grupo' }}</p>
                                        <p class="text-sm font-medium text-blue-600">Fase de Grupos</p>
                                    @endif

                                    <p class="text-sm text-gray-500">Fecha: {{ $partido->fecha ? $partido->fecha->format('d/m/Y H:i') : 'No asignada' }}</p>
                                </div>

                                <div class="flex items-center space-x-4">
                                    <div class="text-center">
                                        <div class="text-lg font-medium">{{ $partido->equipoLocal->nombre }}</div>
                                        <div class="mt-1">
                                            @if ($partido->equipoLocal->logo)
                                                <img src="{{ asset('storage/' . $partido->equipoLocal->logo) }}" alt="{{ $partido->equipoLocal->nombre }}" class="h-16 w-16 object-contain mx-auto">
                                            @else
                                                <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto">
                                                    <span class="text-gray-500 text-xl">{{ substr($partido->equipoLocal->nombre, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-2xl font-bold">VS</div>

                                    <div class="text-center">
                                        <div class="text-lg font-medium">{{ $partido->equipoVisitante->nombre }}</div>
                                        <div class="mt-1">
                                            @if ($partido->equipoVisitante->logo)
                                                <img src="{{ asset('storage/' . $partido->equipoVisitante->logo) }}" alt="{{ $partido->equipoVisitante->nombre }}" class="h-16 w-16 object-contain mx-auto">
                                            @else
                                                <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto">
                                                    <span class="text-gray-500 text-xl">{{ substr($partido->equipoVisitante->nombre, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <form id="form-resultado" action="{{ route('partidos.registrar-resultado', $partido) }}" method="POST">
                        @csrf

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Resultado Final</h3>

                            <div class="flex justify-center items-center space-x-4">
                                <div class="text-center">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $partido->equipoLocal->nombre }}</label>
                                    <input type="text" id="resultado_local_display" class="mt-1 block w-20 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="0" readonly>
                                </div>

                                <div class="text-xl font-bold">-</div>

                                <div class="text-center">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $partido->equipoVisitante->nombre }}</label>
                                    <input type="text" id="resultado_visitante_display" class="mt-1 block w-20 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="0" readonly>
                                </div>
                            </div>

                            <div class="text-center mt-2 text-sm text-gray-500">
                                El resultado final se calculará automáticamente basado en los sets
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Detalle de Sets</h3>

                            <div id="sets-container">
                                @for ($i = 0; $i < 3; $i++)
                                    @php
                                        $set = $partido->sets->where('numero_set', $i + 1)->first();
                                    @endphp

                                    <div class="set-row mb-4 p-4 border rounded-lg {{ $i % 2 == 0 ? 'bg-gray-50' : '' }}">
                                        <div class="flex justify-between items-center mb-2">
                                            <h4 class="font-medium text-gray-900">Set {{ $i + 1 }}</h4>
                                        </div>

                                        <input type="hidden" name="sets[{{ $i }}][numero_set]" value="{{ $i + 1 }}">

                                        <div class="flex justify-center items-center space-x-4">
                                            <div class="text-center">
                                                <label for="sets[{{ $i }}][puntos_local]" class="block text-sm font-medium text-gray-700 mb-1">{{ $partido->equipoLocal->nombre }}</label>
                                                <input type="number" name="sets[{{ $i }}][puntos_local]" class="set-input puntos-local mt-1 block w-20 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ $set ? $set->puntos_local : 0 }}" required min="0" max="99">
                                            </div>

                                            <div class="text-xl font-bold">-</div>

                                            <div class="text-center">
                                                <label for="sets[{{ $i }}][puntos_visitante]" class="block text-sm font-medium text-gray-700 mb-1">{{ $partido->equipoVisitante->nombre }}</label>
                                                <input type="number" name="sets[{{ $i }}][puntos_visitante]" class="set-input puntos-visitante mt-1 block w-20 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ $set ? $set->puntos_visitante : 0 }}" required min="0" max="99">
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Guardar Resultado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const formResultado = document.getElementById('form-resultado');
                const resultadoLocalDisplay = document.getElementById('resultado_local_display');
                const resultadoVisitanteDisplay = document.getElementById('resultado_visitante_display');

                // Inputs de sets
                const setInputs = document.querySelectorAll('.set-input');

                // Función para calcular el resultado final basado en los sets
                function calcularResultadoFinal() {
                    let setsGanadosLocal = 0;
                    let setsGanadosVisitante = 0;

                    // Obtener los sets por filas
                    const setRows = document.querySelectorAll('.set-row');

                    for (let i = 0; i < setRows.length; i++) {
                        const puntosLocalInput = setRows[i].querySelector('.puntos-local');
                        const puntosVisitanteInput = setRows[i].querySelector('.puntos-visitante');

                        if (puntosLocalInput && puntosVisitanteInput) {
                            const puntosLocal = parseInt(puntosLocalInput.value) || 0;
                            const puntosVisitante = parseInt(puntosVisitanteInput.value) || 0;

                            if (puntosLocal > 0 || puntosVisitante > 0) {
                                if (puntosLocal > puntosVisitante) {
                                    setsGanadosLocal++;
                                } else if (puntosVisitante > puntosLocal) {
                                    setsGanadosVisitante++;
                                }
                            }
                        }
                    }

                    // Actualizar el resultado final
                    resultadoLocalDisplay.value = setsGanadosLocal;
                    resultadoVisitanteDisplay.value = setsGanadosVisitante;

                    return {
                        local: setsGanadosLocal,
                        visitante: setsGanadosVisitante
                    };
                }

                // Actualizar resultado al cambiar cualquier valor de los sets
                setInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        calcularResultadoFinal();
                    });
                });

                // Calcular resultado inicial
                calcularResultadoFinal();

                // Validar que el resultado final sea válido (2-0, 2-1, 0-2 o 1-2)
                function validarResultado() {
                    const resultado = calcularResultadoFinal();

                    // Verificar que alguno de los equipos tiene exactamente 2 sets ganados
                    if (resultado.local !== 2 && resultado.visitante !== 2) {
                        return false;
                    }

                    // Verificar que el otro equipo tiene 0 o 1 sets
                    if (resultado.local === 2 && resultado.visitante > 1) {
                        return false;
                    }

                    if (resultado.visitante === 2 && resultado.local > 1) {
                        return false;
                    }

                    return true;
                }

                // Validar que no hay empates en los sets
                function validarNoEmpates() {
                    const setRows = document.querySelectorAll('.set-row');

                    for (let i = 0; i < setRows.length; i++) {
                        const puntosLocalInput = setRows[i].querySelector('.puntos-local');
                        const puntosVisitanteInput = setRows[i].querySelector('.puntos-visitante');

                        if (puntosLocalInput && puntosVisitanteInput) {
                            const puntosLocal = parseInt(puntosLocalInput.value) || 0;
                            const puntosVisitante = parseInt(puntosVisitanteInput.value) || 0;

                            if (puntosLocal > 0 && puntosLocal === puntosVisitante) {
                                return false;
                            }
                        }
                    }

                    return true;
                }

                // Validar el formulario antes de enviar
                formResultado.addEventListener('submit', function(event) {
                    if (!validarNoEmpates()) {
                        event.preventDefault();
                        alert('Los sets no pueden terminar en empate. Por favor, revisa los sets.');
                        return false;
                    }

                    if (!validarResultado()) {
                        event.preventDefault();
                        alert('El resultado final debe ser 2-0, 2-1, 0-2 o 1-2. Por favor, revisa los sets.');
                        return false;
                    }

                    return true;
                });
            });
        </script>
    @endpush
</x-app-layout>
