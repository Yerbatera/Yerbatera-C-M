@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 pb-6">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Asignar tareas</h1>
            <p class="text-sm text-gray-600">Las tareas nuevas comienzan con estado Pendiente y pueden programarse semanalmente.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('monitoreo') }}" class="rounded-md bg-green-800 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-green-900">
                Ver Tareas Diarias
            </a>
            <a href="{{ route('turnos.index') }}" class="rounded-md bg-gray-200 px-4 py-2 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-300">
                Gestión de Personal
            </a>
        </div>
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

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700">
                Seleccionar desde Personal con turno/franja horaria cargada (opcional)
                <select id="selector_turno_asignar" class="w-full rounded-md border border-green-300 bg-green-50/40 px-3 py-2 text-sm">
                    <option value="">-- Elegí un turno registrado en Personal para autocompletar empleado y fecha --</option>
                    @foreach ($turnosProgramados as $turnoProg)
                        @if ($turnoProg->user)
                            <option
                                value="{{ $turnoProg->id }}"
                                data-empleado="{{ $turnoProg->user->name }}"
                                data-fecha="{{ $turnoProg->fecha->toDateString() }}"
                            >
                                {{ $turnoProg->user->name }} · {{ $turnoProg->fecha->format('d/m/Y') }} · Turno {{ ucfirst($turnoProg->turno) }} ({{ $turnoProg->franja_horaria ?? 'Horario estándar' }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </label>

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:w-[calc(50%-0.5rem)]">
                Empleado
                <input type="text" name="empleado" id="input_asignar_empleado" list="empleados" value="{{ old('empleado') }}" placeholder="Escribí el nombre del empleado" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
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

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:w-[calc(50%-0.5rem)]">
                Nivel de Relevancia
                <select name="relevancia" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="Alta" @selected(old('relevancia') === 'Alta')>Alta (Prioritaria)</option>
                    <option value="Media" @selected(old('relevancia', 'Media') === 'Media')>Media (Normal)</option>
                    <option value="Baja" @selected(old('relevancia') === 'Baja')>Baja (Complementaria)</option>
                </select>
            </label>

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700 sm:w-[calc(50%-0.5rem)]">
                Fecha
                <input type="date" name="fecha_asignacion" id="input_asignar_fecha" value="{{ old('fecha_asignacion', now()->toDateString()) }}" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                @error('fecha_asignacion')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex w-full flex-col gap-1 text-sm font-medium text-gray-700">
                Descripción
                <textarea name="descripcion" rows="2" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <div class="flex w-full flex-wrap items-center justify-between gap-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800">
                        <input
                            type="checkbox"
                            name="repetir_semanal"
                            value="1"
                            @checked(old('repetir_semanal'))
                            class="h-4 w-4 rounded border-gray-300 text-green-800"
                        >
                        <span>Repetir todas las semanas de forma sistemática</span>
                    </label>
                    <select name="semanas_repeticion" class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs">
                        <option value="4" selected>Durante 4 semanas</option>
                        <option value="8">Durante 8 semanas</option>
                        <option value="12">Durante 12 semanas</option>
                    </select>
                </div>

                <button type="submit" class="rounded-md bg-green-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-900">
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
                        <th class="p-4">Relevancia</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($tareas as $tarea)
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-gray-900">{{ $tarea->operario?->name ?? 'Sin asignar' }}</td>
                            <td class="p-4 font-semibold text-gray-800">
                                {{ $tarea->nombre }}
                                @if ($tarea->es_recurrente)
                                    <span class="ml-1 rounded bg-purple-100 px-1.5 py-0.5 text-[11px] font-medium text-purple-800">Semanal</span>
                                @endif
                            </td>
                            <td class="p-4">{{ $tarea->relevancia ?? 'Media' }}</td>
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
                            <td colspan="6" class="p-8 text-center text-gray-400">Todavía no hay tareas cargadas.</td>
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

<script>
    const selectorTurnoAsignar = document.getElementById('selector_turno_asignar');
    const inputAsignarEmpleado = document.getElementById('input_asignar_empleado');
    const inputAsignarFecha = document.getElementById('input_asignar_fecha');
    if (selectorTurnoAsignar) {
        selectorTurnoAsignar.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.empleado) {
                inputAsignarEmpleado.value = opt.dataset.empleado;
                inputAsignarFecha.value = opt.dataset.fecha;
            }
        });
    }
</script>
@endsection
