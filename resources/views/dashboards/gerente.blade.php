@extends('layouts.app')

@section('contenido')
    <!-- Grilla de Módulos -->
    <div class="my-auto py-4 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Tarjeta 1: Producción y ventas -->
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="bg-orange-600 text-white w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 3v18h18" />
                            <path d="M7 16v-5" />
                            <path d="M12 16V8" />
                            <path d="M17 16v-8" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-900">Producción y ventas</h2>
                    <p class="text-gray-500 text-sm mb-6">Consulta gerencial de indicadores comerciales, volúmenes y movimientos</p>
                </div>
                <a href="{{ route('gerencia.ventas') }}" class="text-orange-600 font-semibold hover:underline">Abrir</a>
            </div>

            <!-- Tarjeta 2: Tareas diarias -->
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="bg-green-700 text-white w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                            <rect x="9" y="3" width="6" height="4" rx="1" />
                            <path d="m9 14 2 2 4-4" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-900">Tareas diarias</h2>
                    <p class="text-gray-500 text-sm mb-6">Asignación, relevancia y seguimiento</p>
                </div>
                <a href="/tareas" class="text-green-700 font-semibold hover:underline">Abrir</a>
            </div>

            <!-- Tarjeta 3: Control productivo -->
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex flex-col justify-between">
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
                    <p class="text-gray-500 text-sm mb-6">Historial de procesos de secadero, temperatura y lotes trabajados</p>
                </div>
                <a href="{{ route('gerencia.procesos') }}" class="text-stone-600 font-semibold hover:underline">Abrir</a>
            </div>

            <!-- Tarjeta 4: Registro de ventas -->
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="bg-yellow-700 text-white w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                            <path d="M8 10h8" />
                            <path d="M8 14h5" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-900">Registro de ventas</h2>
                    <p class="text-gray-500 text-sm mb-6">Detalle de operaciones comerciales por cliente, lote y punto de venta</p>
                </div>
                <a href="{{ route('gerencia.ventas') }}" class="text-yellow-700 font-semibold hover:underline">Abrir</a>
            </div>

            <!-- Tarjeta 5: Cupones y remitos -->
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="bg-green-800 text-white w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z" />
                            <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z" />
                            <path d="M7 21h10" />
                            <path d="M12 3v18" />
                            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-900">Cupones y remitos</h2>
                    <p class="text-gray-500 text-sm mb-6">Recepción de materia prima en balanza, pesaje y productores</p>
                </div>
                <a href="/balanza" class="text-green-800 font-semibold hover:underline">Abrir</a>
            </div>

            <!-- Tarjeta 6: Gestión de Personal -->
            <div class="bg-white rounded-xl p-6 shadow hover:shadow-lg transition border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="bg-green-800 text-white w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-900">Gestión de personal</h2>
                    <p class="text-gray-500 text-sm mb-6">Asistencia, turnos y franjas horarias</p>
                </div>
                <a href="{{ route('turnos.index') }}" class="text-green-800 font-semibold hover:underline">Abrir</a>
            </div>

        </div>
    </div>
@endsection
