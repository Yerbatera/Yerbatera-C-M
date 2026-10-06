@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Monitoreo Global de Actividades</h1>
            <p class="text-gray-600 text-sm">Supervisión en tiempo real del estado de tareas asignadas a operarios</p>
        </div>
        <a href="{{ route('gerente') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
            ← Volver al Menú
        </a>
    </div>

    <form method="GET" action="{{ route('monitoreo') }}" class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
        <input type="hidden" name="semana" value="{{ $weekStart->toDateString() }}">
        @if ($hasSelectedDay)
            <input type="hidden" name="dia" value="{{ $selectedDate->toDateString() }}">
        @endif

        <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:w-48">
            Estado
            <select name="estado" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos</option>
                <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendiente</option>
                <option value="completada" @selected(request('estado') === 'completada')>Completada</option>
            </select>
        </label>

        <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:w-48">
            Empleado
            <select name="operario" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">Todos los empleados</option>
                @foreach ($empleados as $empleado)
                    <option value="{{ $empleado->id }}" @selected((string) request('operario') === (string) $empleado->id)>
                        {{ $empleado->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="w-full rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 sm:w-auto">
            Filtrar
        </button>
        <a href="{{ route('monitoreo') }}" class="w-full px-2 py-2 text-center text-sm font-medium text-gray-600 hover:text-gray-900 sm:w-auto">
            Limpiar
        </a>
    </form>

    <div class="mb-6 overflow-x-auto py-1">
        <div class="mx-auto flex w-max min-w-full items-center justify-center gap-2">
            <a href="{{ $previousWeekUrl }}" aria-label="Semana anterior" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-2xl text-gray-700 transition hover:bg-gray-50">
                ‹
            </a>

            <a href="{{ $allDaysUrl }}" @class([
                'flex min-w-20 shrink-0 items-center justify-between gap-3 rounded-lg border px-3 py-2 text-sm font-semibold transition',
                'border-green-800 bg-green-800 text-white hover:bg-green-900' => ! $hasSelectedDay,
                'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' => $hasSelectedDay,
            ])>
                <span>Todos</span>
                <span class="text-xs {{ ! $hasSelectedDay ? 'text-green-100' : 'text-gray-500' }}">{{ $totalTaskCount }}</span>
            </a>

            @foreach ($weekDays as $weekDay)
                @php
                    $dayQuery = array_merge(request()->query(), [
                        'dia' => $weekDay['date']->toDateString(),
                        'semana' => $weekStart->toDateString(),
                    ]);
                    unset($dayQuery['page']);
                    $isSelectedDay = $hasSelectedDay && $selectedDate->isSameDay($weekDay['date']);
                @endphp

                <a href="{{ route('monitoreo', $dayQuery) }}" @class([
                    'flex min-w-20 shrink-0 items-center justify-between gap-3 rounded-lg border px-3 py-2 text-sm transition',
                    'border-green-800 bg-green-800 text-white hover:bg-green-900' => $isSelectedDay,
                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' => ! $isSelectedDay,
                ])>
                    <span class="flex flex-col">
                        <span class="font-semibold">{{ $weekDay['label'] }}</span>
                        <span class="text-xs {{ $isSelectedDay ? 'text-green-100' : 'text-gray-500' }}">{{ $weekDay['date']->format('j') }}</span>
                    </span>
                    <span class="text-xs {{ $isSelectedDay ? 'text-green-100' : 'text-gray-500' }}">{{ $weekDay['count'] }}</span>
                </a>
            @endforeach

            <a href="{{ $nextWeekUrl }}" aria-label="Semana siguiente" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-2xl text-gray-700 transition hover:bg-gray-50">
                ›
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">Empleado</th>
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