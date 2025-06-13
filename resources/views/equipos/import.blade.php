<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Importar Equipos desde CSV') }}
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

                    @if (session('import_errors'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong>Errores encontrados:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach (session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('equipos.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-6">
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            El archivo CSV debe tener las siguientes columnas:
                                        <ul class="list-disc pl-5 mt-1">
                                            <li>Primera columna: Nombre del equipo</li>
                                            <li>Segunda columna: Teléfono de contacto (opcional)</li>
                                            <li>Tercera columna: Nombre del torneo (opcional, debe coincidir con el seleccionado)</li>
                                        </ul>
                                        <div class="mt-2 p-2 bg-gray-100 rounded">
                                            <code class="text-xs">
                                                Equipo A,123456789,Torneo Verano 2024<br>
                                                Equipo B,987654321,Torneo Verano 2024<br>
                                                Equipo C,555123456,Torneo Verano 2024
                                            </code>
                                        </div>
                                        <a href="#" class="mt-2 inline-block font-medium text-blue-600 hover:text-blue-500" onclick="descargarPlantilla(); return false;">
                                            Descargar plantilla de ejemplo
                                        </a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="torneo_id" class="block text-sm font-medium text-gray-700">Torneo</label>
                                <select name="torneo_id" id="torneo_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="">Seleccionar torneo...</option>
                                    @foreach($torneos as $torneo)
                                        <option value="{{ $torneo->id }}">{{ $torneo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('torneo_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="csv_file" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
                                <input type="file" id="csv_file" name="csv_file" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required accept=".csv,.txt">
                                @error('csv_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <div class="flex items-center">
                                    <input id="has_headers" name="has_headers" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="has_headers" class="ml-2 block text-sm text-gray-700">
                                        El archivo tiene fila de encabezados (omitir primera fila)
                                    </label>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="flex items-center">
                                    <input id="actualizar_telefonos" name="actualizar_telefonos" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="actualizar_telefonos" class="ml-2 block text-sm text-gray-700">
                                        Actualizar teléfonos de equipos existentes
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Importar equipos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function descargarPlantilla() {
            const contenido = 'Nombre,Teléfono,Torneo\nEquipo A,123456789,Torneo Verano 2024\nEquipo B,987654321,Torneo Verano 2024\nEquipo C,555123456,Torneo Verano 2024';
            const blob = new Blob([contenido], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('href', url);
            a.setAttribute('download', 'plantilla_equipos.csv');
            a.click();
        }
    </script>
</x-app-layout>
