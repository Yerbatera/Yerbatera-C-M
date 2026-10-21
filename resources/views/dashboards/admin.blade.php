@extends('layouts.app')

@section('contenido')
    <!-- Cabecera -->
    <div class="bg-white/80 backdrop-blur rounded-xl p-6 shadow-sm mb-8 border-t-4 border-green-700">
        <h1 class="text-3xl font-bold text-green-900">Bienvenido, Administración</h1>
        <p class="text-gray-600 mt-1">Gestión de personal y operaciones · Seleccioná un módulo para continuar</p>
    </div>

    <!-- Grilla de Módulos (4 Tarjetas) -->
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
            <div class="bg-yellow-700 text-white w-12 h-12 rounded-lg flex items-center justify-center text-xl mb-4">🏪</div>
            <h2 class="text-xl font-bold text-green-900">Registro de ventas</h2>
            <p class="text-gray-500 text-sm mb-6">Ventas</p>
            <a href="/ventas" class="text-yellow-700 font-semibold hover:underline">Abrir →</a>
        </div>

        <!-- Tarjeta 3 -->
        <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100">
            <div class="bg-green-800 text-white w-12 h-12 rounded-lg flex items-center justify-center text-xl mb-4">⚖️</div>
            <h2 class="text-xl font-bold text-green-900">Cupones y remitos</h2>
            <p class="text-gray-500 text-sm mb-6">Balanza</p>
            <a href="/balanza" class="text-green-800 font-semibold hover:underline">Abrir →</a>
        </div>

        <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100">
            <div class="bg-green-800 text-white w-12 h-12 rounded-lg flex items-center justify-center text-xl mb-4">🗓️</div>
            <h2 class="text-xl font-bold text-green-900">Horarios y tareas</h2>
            <p class="text-gray-500 text-sm mb-6">Personal de producción</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('turnos.index') }}" class="text-green-800 font-semibold hover:underline">Turnos →</a>
                <a href="{{ route('tareas.asignar') }}" class="text-green-800 font-semibold hover:underline">Asignar tarea →</a>
            </div>
        </div>

    </div>
@endsection