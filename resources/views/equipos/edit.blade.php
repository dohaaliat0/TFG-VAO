<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Equipo') }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <strong>¡Hay errores en el formulario!</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if($equipoOcupado)
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm">
                                <strong>Restricción:</strong> Este equipo está asignado a grupos o partidos, por lo que no se puede cambiar el torneo. Solo se pueden editar el nombre y teléfono de contacto.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('equipos.update', $equipo) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre *</label>
                    <input type="text"
                           name="nombre"
                           id="nombre"
                           value="{{ old('nombre', $equipo->nombre) }}"
                           class="mt-1 block w-full rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('nombre') border-red-500 @else border-gray-300 @enderror"
                           required>
                    @error('nombre')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="telefono_contacto" class="block text-sm font-medium text-gray-700">Teléfono de contacto</label>
                    <input type="text"
                           name="telefono_contacto"
                           id="telefono_contacto"
                           value="{{ old('telefono_contacto', $equipo->telefono_contacto) }}"
                           class="mt-1 block w-full rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('telefono_contacto') border-red-500 @else border-gray-300 @enderror"
                           placeholder="Ej: +34 123 456 789">
                    @error('telefono_contacto')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Campo opcional. Máximo 20 caracteres.</p>
                </div>

                <div class="mb-4">
                    <label for="torneo_id" class="block text-sm font-medium text-gray-700">Torneo *</label>
                    @if($equipoOcupado)
                        <div class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
                            {{ $equipo->torneo->nombre }}
                        </div>
                        <p class="mt-1 text-sm text-gray-500">El torneo no se puede modificar porque el equipo está asignado a grupos o partidos.</p>
                    @else
                        <select name="torneo_id"
                                id="torneo_id"
                                class="mt-1 block w-full rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('torneo_id') border-red-500 @else border-gray-300 @enderror"
                                required>
                            <option value="">Selecciona un torneo</option>
                            @foreach($torneos as $torneo)
                                <option value="{{ $torneo->id }}" {{ old('torneo_id', $equipo->torneo_id) == $torneo->id ? 'selected' : '' }}>
                                    {{ $torneo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('torneo_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Puedes cambiar el torneo porque el equipo no está asignado a ningún grupo o partido.</p>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('equipos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Actualizar Equipo
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
