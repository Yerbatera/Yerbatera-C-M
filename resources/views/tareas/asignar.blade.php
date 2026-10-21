@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Asignar tareas</h1>
            <p class="text-sm text-gray-600">Las tareas nuevas comienzan con estado Pendiente.</p>
        </div>
        <a href="{{ route('turnos.index') }}" class="w-full rounded-md bg-gray-200 px-4 py-2 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-300 sm:w-auto">
            Administrar turnos
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800" role="alert">
            {{ session('warning') }}
        </div>
    @endif

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Nueva tarea</h2>
        <form method="POST" action="{{ route('tareas.asignar.store') }}" class="flex flex-col gap-4 sm:flex-row sm:flex-wrap">
            @csrf

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:w-[calc(50%-0.5rem)]">
                Empleado
                <input type="text" name="empleado" list="empleados" value="{{ old('empleado') }}" placeholder="Escribí el nombre del empleado" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                <datalist id="empleados">
                    @foreach ($empleados as $empleado)
                        <option value="{{ $empleado->name }}">
                    @endforeach
                </datalist>
                @error('empleado')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:w-[calc(50%-0.5rem)]">
                Nombre de la tarea
                <input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="255" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                @error('nombre')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700">
                Descripción
                <textarea name="descripcion" rows="3" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:w-[calc(50%-0.5rem)]">
                Fecha
                <input type="date" name="fecha_asignacion" value="{{ old('fecha_asignacion', now()->toDateString()) }}" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                @error('fecha_asignacion')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <div class="flex w-full items-end sm:w-[calc(50%-0.5rem)]">
                <button type="submit" class="w-full rounded-md bg-green-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-900 sm:w-auto">
                    Asignar tarea
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="p-4">Empleado</th>
                        <th class="p-4">Tarea</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($tareas as $tarea)
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-gray-900">{{ $tarea->operario?->name ?? 'Sin asignar' }}</td>
                            <td class="p-4 font-semibold text-gray-800">{{ $tarea->nombre }}</td>
                            <td class="p-4 text-gray-500">{{ $tarea->descripcion ?? 'Sin detalle adicional' }}</td>
                            <td class="p-4">{{ $tarea->fecha_asignacion ? \Illuminate\Support\Carbon::parse($tarea->fecha_asignacion)->format('d/m/Y') : '—' }}</td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                    {{ $tarea->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">Todavía no hay tareas cargadas.</td>
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
