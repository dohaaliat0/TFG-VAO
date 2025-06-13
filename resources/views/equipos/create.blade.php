<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Equipo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('equipos.store') }}" class="space-y-6">
                        @csrf

                        <!-- Selector de Torneo -->
                        <div>
                            <x-label for="torneo_id" value="{{ __('Torneo') }}" />
                            <select name="torneo_id" id="torneo_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('Seleccionar torneo...') }}</option>
                                @foreach($torneos as $torneo)
                                    <option value="{{ $torneo->id }}" {{ old('torneo_id') == $torneo->id ? 'selected' : '' }}>
                                        {{ $torneo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error for="torneo_id" class="mt-2" />
                        </div>

                        <!-- Nombre del Equipo -->
                        <div>
                            <x-label for="nombre" value="{{ __('Nombre del Equipo') }}" />
                            <x-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
                            <x-input-error for="nombre" class="mt-2" />
                        </div>

                        <!-- Teléfono de Contacto -->
                        <div>
                            <x-label for="telefono_contacto" value="{{ __('Teléfono de Contacto') }}" />
                            <x-input id="telefono_contacto" class="block mt-1 w-full" type="text" name="telefono_contacto" :value="old('telefono_contacto')" />
                            <p class="mt-1 text-sm text-gray-500">{{ __('Opcional') }}</p>
                            <x-input-error for="telefono_contacto" class="mt-2" />
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center justify-between pt-4">
                            <x-button class="ml-4">
                                {{ __('Crear Equipo') }}
                            </x-button>

                            <a href="{{ route('equipos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                {{ __('Cancelar') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
