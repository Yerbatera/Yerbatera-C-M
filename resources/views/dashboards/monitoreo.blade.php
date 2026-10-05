@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Monitoreo Global de Actividades</h1>
            <p class="text-gray-600 text-sm">Supervisión en tiempo real del estado de tareas asignadas a operarios</p>
        </div>
        <a href="{{ route('login') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
            ← Volver al Menú
        </a>
    </div>

    <form method="GET" action="{{ route('monitoreo') }}" class="mb-6 flex flex-wrap items-end gap-4">
        <label class="flex min-w-48 flex-col gap-1 text-sm font-medium text-gray-700">
            Estado
            <select name="estado" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendiente</option>
                <option value="completada" @selected(request('estado') === 'completada')>Completada</option>
            </select>
        </label>

        <label class="flex min-w-48 flex-col gap-1 text-sm font-medium text-gray-700">
            Operario
            <select name="operario" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                @foreach ($operarios as $operario)
                    <option value="{{ $operario->id }}" @selected((string) request('operario') === (string) $operario->id)>
                        {{ $operario->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">
            Filtrar
        </button>
        <a href="{{ route('monitoreo') }}" class="px-2 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
            Limpiar
        </a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">Operario / Responsable</th>
                        <th class="p-4">Tarea / Actividad</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4">Última Actualización</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($tareas as $tarea)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-4 font-medium text-gray-900">
                                {{ $tarea->operario?->name ?? 'Sin asignar' }}
                            </td>
                            <td class="p-4 font-semibold text-gray-800">
                                {{ $tarea->nombre ?? 'Tarea #' . $tarea->id_tarea }}
                            </td>
                            <td class="p-4 text-gray-500">
                                {{ $tarea->descripcion ?? 'Sin detalle adicional' }}
                            </td>
                            <td class="p-4">
                                @if (in_array(strtolower((string) $tarea->estado), ['completada', 'completado', 'finalizada', 'finalizado'], true))
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓ Completada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 animate-pulse">
                                        ⏳ Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-gray-400 text-xs">
                                {{ $tarea->created_at?->diffForHumans() ?? 'Recientemente' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">
                                No hay actividades ni tareas registradas en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $tareas->links() }}
    </div>
</div>
@endsection