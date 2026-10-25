@extends('layouts.app')

@section('contenido')
    <!-- Grilla de Módulos (2 Tarjetas) -->
    <div class="my-auto py-4 w-full">
        <div class="flex flex-wrap justify-center gap-6">

            <!-- Tarjeta 1: Tareas diarias -->
            <div class="w-full md:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1rem)] bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="bg-green-700 text-white w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                            <rect x="9" y="3" width="6" height="4" rx="1" />
                            <path d="m9 14 2 2 4-4" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-900">Tareas diarias</h2>
                    <p class="text-gray-500 text-sm mb-6">Consulta de actividades asignadas en el turno y actualización de estado</p>
                </div>
                <a href="/tareas" class="text-green-700 font-semibold hover:underline">Abrir</a>
            </div>

            <!-- Tarjeta 2: Control productivo -->
            <div class="w-full md:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1rem)] bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="bg-stone-600 text-white w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z" />
                            <path d="M12 18v.01" />
                            <path d="M18 6h2" />
                            <path d="M18 10h2" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-900">Control productivo</h2>
                    <p class="text-gray-500 text-sm mb-6">Registro de parámetros de secadero, sapecado, temperatura y lotes</p>
                </div>
                <a href="/procesos" class="text-stone-600 font-semibold hover:underline">Abrir</a>
            </div>

        </div>
    </div>
@endsection
