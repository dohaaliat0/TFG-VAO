<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Asignar Equipos a Categoría') }}
            </h2>
            <a href="{{ route('categorias.show', $categoria) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
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

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Información de la categoría</h3>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Nombre:</span> {{ $categoria->nombre }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Torneo:</span> {{ $categoria->torneo->nombre }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Estado del torneo:</span> {{ ucfirst($categoria->torneo->estado) }}
                                </p>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Equipos actualmente asignados</h3>
                                @if($categoria->equipos->count() > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($categoria->equipos as $equipo)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $equipo->nombre }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-600">
                                        No hay equipos asignados a esta categoría.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($categoria->torneo->estado === 'preparacion' || $categoria->torneo->estado === 'en_curso')
                        <form action="{{ route('categorias.asignar-equipos.store', $categoria) }}" method="POST" class="space-y-6">
                            @csrf
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Seleccionar equipos para asignar</h3>
                                
                                @if($equiposDelTorneo->count() > 0)
                                    <div class="bg-white border border-gray-300 rounded-md p-4 max-h-96 overflow-y-auto">
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($equiposDelTorneo as $equipo)
                                                <div class="flex items-center">
                                                    <input id="equipo-{{ $equipo->id }}" name="equipo_ids[]" type="checkbox" value="{{ $equipo->id }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                    <label for="equipo-{{ $equipo->id }}" class="ml-2 block text-sm text-gray-900">
                                                        {{ $equipo->nombre }}
                                                        @if($equipo->telefono_contacto)
                                                            <span class="text-xs text-gray-500">(Tel: {{ $equipo->telefono_contacto }})</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex justify-between items-center">
                                        <div>
                                            <button type="button" id="seleccionar-todos" class="text-sm text-indigo-600 hover:text-indigo-500">
                                                Seleccionar todos
                                            </button>
                                            <span class="text-gray-300 mx-2">|</span>
                                            <button type="button" id="deseleccionar-todos" class="text-sm text-indigo-600 hover:text-indigo-500">
                                                Deseleccionar todos
                                            </button>
                                        </div>
                                        
                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Asignar equipos seleccionados
                                        </button>
                                    </div>
                                @else
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700">
                                                    No hay equipos disponibles para asignar a esta categoría. Todos los equipos del torneo ya están asignados a esta categoría o no hay equipos en el torneo.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </form>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No se pueden asignar equipos manualmente a categorías en torneos finalizados.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seleccionarTodosBtn = document.getElementById('seleccionar-todos');
            const deseleccionarTodosBtn = document.getElementById('deseleccionar-todos');
            const checkboxes = document.querySelectorAll('input[name="equipo_ids[]"]');
            
            if (seleccionarTodosBtn) {
                seleccionarTodosBtn.addEventListener('click', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                });
            }
            
            if (deseleccionarTodosBtn) {
                deseleccionarTodosBtn.addEventListener('click', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
