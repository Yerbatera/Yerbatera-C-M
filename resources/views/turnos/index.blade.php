@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Turnos de producción</h1>
            <p class="text-sm text-gray-600">Registrá el día y turno de cada empleado.</p>
        </div>
        <a href="{{ route('tareas.asignar') }}" class="w-full rounded-md bg-gray-800 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-gray-700 sm:w-auto">
            Asignar una tarea
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Registrar turno</h2>
        <form method="POST" action="{{ route('turnos.store') }}" class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
            @csrf

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:flex-1">
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

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:flex-1">
                Fecha
                <input type="date" name="fecha" value="{{ old('fecha') }}" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                @error('fecha')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:flex-1">
                Turno
                <select name="turno" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="">Seleccioná un turno</option>
                    @foreach (['mañana', 'tarde', 'noche'] as $opcionTurno)
                        <option value="{{ $opcionTurno }}" @selected(old('turno') === $opcionTurno)>
                            {{ ucfirst($opcionTurno) }}
                        </option>
                    @endforeach
                </select>
                @error('turno')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit" class="w-full rounded-md bg-green-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-900 sm:w-auto">
                Guardar turno
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="p-4">Empleado</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Turno</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($turnos as $turno)
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-gray-900">{{ $turno->user?->name ?? 'Sin empleado' }}</td>
                            <td class="p-4">{{ $turno->fecha->format('d/m/Y') }}</td>
                            <td class="p-4 capitalize">{{ $turno->turno }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-8 text-center text-gray-400">Todavía no hay turnos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $turnos->links() }}
    </div>
</div>
@endsection
