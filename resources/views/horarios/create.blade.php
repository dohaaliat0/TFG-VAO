<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Horarios: {{ $torneo->nombre }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('horarios.index', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <form action="{{ route('horarios.store', $torneo) }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
                        <input type="date" name="fecha" id="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="hora_inicio" class="block text-sm font-medium text-gray-700">Hora de inicio</label>
                        <input type="time" name="hora_inicio" id="hora_inicio" value="{{ old('hora_inicio', '09:00') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="hora_fin" class="block text-sm font-medium text-gray-700">Hora de fin</label>
                        <input type="time" name="hora_fin" id="hora_fin" value="{{ old('hora_fin', '21:00') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>

                    <div>
                        <label for="intervalo" class="block text-sm font-medium text-gray-700">Intervalo (minutos)</label>
                        <input type="number" name="intervalo" id="intervalo" value="{{ old('intervalo', 60) }}" min="15" step="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="pista" class="block text-sm font-medium text-gray-700">Pista</label>
                    <input type="text" name="pista" id="pista" value="{{ old('pista', '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nombre de la pista" required>
                    <p class="mt-1 text-sm text-gray-500">Ingrese el nombre o número de la pista (ej: "Pista 1", "Cancha Central", etc.)</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('horarios.index', $torneo) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Crear Horarios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
