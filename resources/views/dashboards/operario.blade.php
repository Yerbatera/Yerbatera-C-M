@extends('layouts.app')

@section('contenido')
    <!-- Cabecera -->
    <div class="bg-white/80 backdrop-blur rounded-xl p-6 shadow-sm mb-8 border-t-4 border-green-700">
        <h1 class="text-3xl font-bold text-green-900">Bienvenido, Operario</h1>
        <p class="text-gray-600 mt-1">Panel de operaciones de planta · Seleccioná un módulo para continuar</p>
    </div>

    <!-- Grilla de Módulos (2 Tarjetas) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Tarjeta 1 -->
        <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100">
            <div class="bg-green-700 text-white w-12 h-12 rounded-lg flex items-center justify-center text-xl mb-4">👷</div>
            <h2 class="text-xl font-bold text-green-900">Tareas diarias</h2>
            <p class="text-gray-500 text-sm mb-6">Operario</p>
            <a href="/tareas" class="text-green-700 font-semibold hover:underline">Abrir →</a>
        </div>

        <!-- Tarjeta 2 -->
        <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100">
            <div class="bg-stone-600 text-white w-12 h-12 rounded-lg flex items-center justify-center text-xl mb-4">🌡️</div>
            <h2 class="text-xl font-bold text-green-900">Control productivo</h2>
            <p class="text-gray-500 text-sm mb-6">Procesos</p>
            <a href="/procesos" class="text-stone-600 font-semibold hover:underline">Abrir →</a>
        </div>

    </div>
@endsection