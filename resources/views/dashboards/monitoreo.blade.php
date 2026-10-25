@extends('layouts.app')

@section('contenido')
@php
    $puedeGestionarTareas = in_array(Auth::user()->rol, ['administracion', 'gerente'], true);
    $abrirModalInicial = request()->boolean('abrir_form') || $errors->any();
@endphp

<div class="container mx-auto px-4 pb-6">
    <!-- Cabecera (sin enlace a Gestión de Personal, con botón que abre el modal flotante de Asignar Tarea) -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tareas Diarias y Monitoreo de Actividades</h1>
            <p class="text-gray-600 text-sm">Supervisión en tiempo real, asignación según horarios de trabajo y nivel de relevancia</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($puedeGestionarTareas)
                <button
                    type="button"
                    id="btn_abrir_modal_tarea"
                    class="rounded-lg bg-green-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-900"
                >
                    Cargar / Asignar Tarea
                </button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div id="alerta_monitoreo_exito" class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800" role="alert">
            {{ session('warning') }}
        </div>
    @endif

    @if ($puedeGestionarTareas && ! empty($operariosSinHorario))
        @php
            $cantOpSinHorario = count($operariosSinHorario);
        @endphp
        <div class="mb-5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
            <p class="font-semibold">
                Atención: {{ $cantOpSinHorario }} {{ $cantOpSinHorario === 1 ? 'operario sin horario asignado' : 'operarios sin horarios asignados' }} esta semana.
            </p>
        </div>
    @endif

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
            <a href="{{ $previousWeekUrl }}" aria-label="Semana anterior" class="flex h-10 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                Anterior
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

            <a href="{{ $nextWeekUrl }}" aria-label="Semana siguiente" class="flex h-10 shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">
                Siguiente
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">Empleado / Horario del Día</th>
                        <th class="p-4">Tarea / Actividad</th>
                        <th class="p-4">Relevancia</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4">Estado</th>
                        <th class="p-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($tareas as $tarea)
                        @php
                            $fechaClave = $tarea->fecha_asignacion ? \Carbon\Carbon::parse($tarea->fecha_asignacion)->toDateString() : '';
                            $turnoDelDia = $turnosMapa->get($tarea->id_operario.'_'.$fechaClave);
                            $relevancia = $tarea->relevancia ?? 'Media';
                            $esCompletada = in_array(strtolower((string) $tarea->estado), ['completada', 'completado', 'finalizada', 'finalizado'], true);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-4">
                                <div class="font-medium text-gray-900">{{ $tarea->operario?->name ?? 'Sin asignar' }}</div>
                                @if ($turnoDelDia)
                                    <div class="text-xs text-green-800 font-medium">
                                        {{ $turnoDelDia->horario_entrada ?? '06:00' }} a {{ $turnoDelDia->horario_salida ?? '14:00' }}
                                        @if ($turnoDelDia->descanso)
                                            · Descanso: {{ $turnoDelDia->descanso }}
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-red-600 font-medium">Sin horario asignado ese día</div>
                                @endif
                            </td>
                            <td class="p-4 font-semibold text-gray-800">
                                <div>{{ $tarea->nombre ?? 'Tarea #' . $tarea->id_tarea }}</div>
                                @if ($tarea->es_recurrente)
                                    <span class="inline-block mt-0.5 rounded bg-purple-100 px-1.5 py-0.5 text-[11px] font-medium text-purple-800">
                                        Semanal sistemática
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if ($relevancia === 'Alta')
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
                                        Alta
                                    </span>
                                @elseif ($relevancia === 'Baja')
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                                        Baja
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                        Media
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-gray-500">
                                {{ $tarea->descripcion ?? 'Sin detalle adicional' }}
                            </td>
                            <td class="p-4">
                                @if ($esCompletada)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Completada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <form method="POST" action="{{ route('tareas.estado', $tarea->id_tarea) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="rounded-md px-3 py-1 text-xs font-semibold transition {{ $esCompletada ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-green-800 text-white hover:bg-green-900' }}"
                                    >
                                        {{ $esCompletada ? 'Reabrir' : 'Marcar completada' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">
                                No hay actividades ni tareas registradas para la selección actual.
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

<!-- Ventana Emergente (Modal Flotante) para Asignar Tarea seleccionando el Día y Horario de Trabajo -->
@if ($puedeGestionarTareas)
<div
    id="modal_asignar_tarea"
    class="fixed inset-0 z-50 {{ $abrirModalInicial ? 'flex' : 'hidden' }} items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo_modal_tarea"
>
    <div class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h2 id="titulo_modal_tarea" class="text-lg font-bold text-green-900">Asignar Tarea Operativa</h2>
                <p class="text-xs text-gray-500">Seleccioná al operario y elegí uno de sus días laborales habilitados (en verde).</p>
            </div>
            <button
                type="button"
                id="btn_cerrar_modal_tarea"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-800"
                aria-label="Cerrar ventana"
            >
                Cerrar
            </button>
        </div>

        <form method="POST" action="{{ route('tareas.asignar.store') }}" id="form_modal_asignar_tarea" class="space-y-4">
            @csrf
            <input type="hidden" name="origen" value="monitoreo">
            <input type="hidden" name="fecha_asignacion" id="input_tarea_fecha" value="{{ old('fecha_asignacion', $selectedDate->toDateString()) }}">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                    1. Operario (Personal) *
                    <select
                        name="empleado"
                        id="select_tarea_empleado"
                        required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium"
                    >
                        @foreach ($empleados as $empleado)
                            @php
                                $sinHorarioTarea = in_array($empleado->name, $operariosSinHorario ?? [], true);
                            @endphp
                            <option
                                value="{{ $empleado->name }}"
                                @selected(old('empleado', request('asignar_empleado')) === $empleado->name)
                            >
                                {{ $empleado->name }}{{ $sinHorarioTarea ? ' — Sin horarios cargados' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('empleado')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                    Nivel de Relevancia *
                    <select name="relevancia" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                        <option value="Alta" @selected(old('relevancia') === 'Alta')>Alta (Prioritaria / Crítica)</option>
                        <option value="Media" @selected(old('relevancia', 'Media') === 'Media')>Media (Operativa normal)</option>
                        <option value="Baja" @selected(old('relevancia') === 'Baja')>Baja (Complementaria)</option>
                    </select>
                </label>
            </div>

            <!-- Grilla visual Lunes a Sábado con horarios o bloqueo en rojo si no trabaja -->
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-800">
                        2. Seleccioná el día de la semana (según su horario en planta) *
                    </span>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="inline-flex items-center gap-1 text-green-800 font-medium">
                            <span class="h-2.5 w-2.5 rounded-full bg-green-600"></span> Trabaja (Seleccionable)
                        </span>
                        <span class="inline-flex items-center gap-1 text-red-700 font-medium">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> No trabaja (No seleccionable)
                        </span>
                    </div>
                </div>

                <div id="grilla_dias_modal_tarea" class="grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                    <!-- Se renderiza dinámicamente según el empleado seleccionado -->
                </div>
                <p id="aviso_sin_dia_seleccionado" class="mt-1.5 hidden text-xs font-medium text-red-600">
                    Este operario no tiene horarios activos cargados en esta semana o debes elegir un día en verde.
                </p>

                <!-- Leyenda inferior de horas trabajadas del día, por semana y por mes -->
                <div class="mt-2.5 rounded-lg border border-green-200 bg-green-50/70 px-3.5 py-2 text-xs text-green-950">
                    <span class="font-semibold">Carga horaria del empleado:</span>
                    Ese día: <strong id="tarea_leyenda_dia">0 hs</strong> ·
                    Por semana: <strong id="tarea_leyenda_semana">0 hs</strong> ·
                    Por mes (4 sem.): <strong id="tarea_leyenda_mes">0 hs</strong>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                    3. Nombre de la Tarea / Actividad *
                    <input
                        type="text"
                        name="nombre"
                        value="{{ old('nombre') }}"
                        maxlength="255"
                        placeholder="Ej: Operar balanza / Control de secado"
                        required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm"
                    >
                    @error('nombre')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                    Descripción / Instrucciones
                    <input
                        type="text"
                        name="descripcion"
                        value="{{ old('descripcion') }}"
                        placeholder="Instrucciones para su turno..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm"
                    >
                </label>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3.5">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800">
                    <input
                        type="checkbox"
                        name="repetir_semanal"
                        id="chk_repetir_tarea_monitoreo"
                        value="1"
                        @checked(old('repetir_semanal'))
                        class="h-4 w-4 rounded border-gray-300 text-green-800 focus:ring-green-700"
                    >
                    <span>Repetir y dejar registrado por meses</span>
                </label>

                <div id="bloque_semanas_tarea_monitoreo" class="{{ old('repetir_semanal') ? '' : 'hidden' }} flex items-center gap-2">
                    <span class="text-xs text-gray-600">Durante:</span>
                    <select name="meses_repeticion" class="rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-medium">
                        <option value="1" selected>1 mes</option>
                        <option value="2">2 meses</option>
                        <option value="3">3 meses</option>
                        <option value="6">6 meses</option>
                        <option value="12">12 meses (1 año)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
                <button
                    type="button"
                    id="btn_cancelar_modal_tarea"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    id="btn_submit_modal_tarea"
                    class="rounded-lg bg-green-800 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-900"
                >
                    Guardar y Asignar Tarea
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    const horariosSemanalesModal = @json($horariosSemanalesModal);
    const modalAsignarTarea = document.getElementById('modal_asignar_tarea');
    const btnAbrirModalTarea = document.getElementById('btn_abrir_modal_tarea');
    const btnCerrarModalTarea = document.getElementById('btn_cerrar_modal_tarea');
    const btnCancelarModalTarea = document.getElementById('btn_cancelar_modal_tarea');
    const selectTareaEmpleado = document.getElementById('select_tarea_empleado');
    const grillaDiasModalTarea = document.getElementById('grilla_dias_modal_tarea');
    const inputTareaFecha = document.getElementById('input_tarea_fecha');
    const btnSubmitModalTarea = document.getElementById('btn_submit_modal_tarea');
    const avisoSinDia = document.getElementById('aviso_sin_dia_seleccionado');
    const tareaLeyendaDia = document.getElementById('tarea_leyenda_dia');
    const tareaLeyendaSemana = document.getElementById('tarea_leyenda_semana');
    const tareaLeyendaMes = document.getElementById('tarea_leyenda_mes');

    function calcularHorasTramo(entradaStr, salidaStr) {
        if (!entradaStr || !salidaStr) return 0;
        const [hE, mE] = entradaStr.split(':').map(Number);
        const [hS, mS] = salidaStr.split(':').map(Number);
        if (isNaN(hE) || isNaN(hS)) return 0;
        let minEntrada = hE * 60 + (mE || 0);
        let minSalida = hS * 60 + (mS || 0);
        if (minSalida <= minEntrada) minSalida += 24 * 60;
        return Math.max(0, Math.round(((minSalida - minEntrada) / 60) * 10) / 10);
    }

    function renderizarDiasEmpleado() {
        if (!selectTareaEmpleado || !grillaDiasModalTarea) return;

        const nombreEmpleado = selectTareaEmpleado.value;
        const dias = horariosSemanalesModal[nombreEmpleado] || [];
        const fechaActualSeleccionada = inputTareaFecha.value;

        grillaDiasModalTarea.innerHTML = '';
        let algunDiaTrabaja = false;
        let diaSeleccionadoValido = false;

        dias.forEach(function (dia) {
            if (dia.trabaja) {
                algunDiaTrabaja = true;
            }
            if (dia.trabaja && dia.fecha === fechaActualSeleccionada) {
                diaSeleccionadoValido = true;
            }
        });

        // Si la fecha actual no es un día que trabaja, preseleccionar el primer día que sí trabaja
        if (!diaSeleccionadoValido && algunDiaTrabaja) {
            const primerDiaActivo = dias.find(d => d.trabaja);
            if (primerDiaActivo) {
                inputTareaFecha.value = primerDiaActivo.fecha;
                diaSeleccionadoValido = true;
            }
        }

        dias.forEach(function (dia) {
            const btnDia = document.createElement('button');
            btnDia.type = 'button';

            if (!dia.trabaja) {
                // En rojo y NO seleccionable si no trabaja ese día
                btnDia.disabled = true;
                btnDia.className = 'flex flex-col items-start justify-between rounded-xl border-2 border-red-300 bg-red-50 p-3 text-left opacity-75 cursor-not-allowed';
                btnDia.innerHTML = `
                    <div class="flex w-full items-center justify-between">
                        <span class="text-sm font-bold text-red-900">${dia.dia_nombre}</span>
                        <span class="text-xs font-semibold text-red-700">${dia.fecha_corta}</span>
                    </div>
                    <div class="mt-2 inline-flex items-center rounded bg-red-200/80 px-2 py-0.5 text-[11px] font-semibold text-red-900">
                        No trabaja / Franco
                    </div>
                `;
            } else {
                const esSeleccionado = inputTareaFecha.value === dia.fecha;
                btnDia.className = esSeleccionado
                    ? 'flex flex-col items-start justify-between rounded-xl border-2 border-green-800 bg-green-800 p-3 text-left text-white shadow-sm transition'
                    : 'flex flex-col items-start justify-between rounded-xl border-2 border-green-300 bg-green-50/70 p-3 text-left text-gray-800 hover:border-green-600 hover:bg-green-100/70 transition cursor-pointer';

                const textoDescanso = dia.descanso
                    ? `<div class="mt-1 text-[11px] ${esSeleccionado ? 'text-green-100' : 'text-gray-600'}">Descanso: ${dia.descanso}</div>`
                    : '';

                btnDia.innerHTML = `
                    <div class="flex w-full items-center justify-between">
                        <span class="text-sm font-bold ${esSeleccionado ? 'text-white' : 'text-green-950'}">${dia.dia_nombre}</span>
                        <span class="text-xs font-semibold ${esSeleccionado ? 'text-green-100' : 'text-green-800'}">${dia.fecha_corta}</span>
                    </div>
                    <div class="mt-2 text-xs font-semibold ${esSeleccionado ? 'text-white' : 'text-green-900'}">
                        Horario: ${dia.entrada} a ${dia.salida}
                    </div>
                    ${textoDescanso}
                `;

                btnDia.addEventListener('click', function () {
                    inputTareaFecha.value = dia.fecha;
                    renderizarDiasEmpleado();
                });
            }

            grillaDiasModalTarea.appendChild(btnDia);
        });

        let totalSemana = 0;
        let horasDiaSeleccionado = 0;
        dias.forEach(function (d) {
            if (d.trabaja) {
                const hs = calcularHorasTramo(d.entrada, d.salida);
                totalSemana += hs;
                if (d.fecha === inputTareaFecha.value) {
                    horasDiaSeleccionado = hs;
                }
            }
        });

        if (tareaLeyendaDia) tareaLeyendaDia.textContent = `${horasDiaSeleccionado} hs`;
        if (tareaLeyendaSemana) tareaLeyendaSemana.textContent = `${Math.round(totalSemana * 10) / 10} hs`;
        if (tareaLeyendaMes) tareaLeyendaMes.textContent = `${Math.round(totalSemana * 4 * 10) / 10} hs`;

        if (!algunDiaTrabaja) {
            avisoSinDia.classList.remove('hidden');
            btnSubmitModalTarea.disabled = true;
            btnSubmitModalTarea.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            avisoSinDia.classList.add('hidden');
            btnSubmitModalTarea.disabled = false;
            btnSubmitModalTarea.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function abrirModalTarea() {
        if (!modalAsignarTarea) return;
        modalAsignarTarea.classList.remove('hidden');
        modalAsignarTarea.classList.add('flex');
        renderizarDiasEmpleado();
    }

    function cerrarModalTarea() {
        if (!modalAsignarTarea) return;
        modalAsignarTarea.classList.add('hidden');
        modalAsignarTarea.classList.remove('flex');
    }

    if (btnAbrirModalTarea) btnAbrirModalTarea.addEventListener('click', abrirModalTarea);
    if (btnCerrarModalTarea) btnCerrarModalTarea.addEventListener('click', cerrarModalTarea);
    if (btnCancelarModalTarea) btnCancelarModalTarea.addEventListener('click', cerrarModalTarea);

    if (modalAsignarTarea) {
        modalAsignarTarea.addEventListener('click', function (e) {
            if (e.target === modalAsignarTarea) cerrarModalTarea();
        });
    }

    if (selectTareaEmpleado) {
        selectTareaEmpleado.addEventListener('change', renderizarDiasEmpleado);
        renderizarDiasEmpleado();
    }

    const chkRepetirTarea = document.getElementById('chk_repetir_tarea_monitoreo');
    const bloqueSemanasTarea = document.getElementById('bloque_semanas_tarea_monitoreo');
    if (chkRepetirTarea && bloqueSemanasTarea) {
        chkRepetirTarea.addEventListener('change', function () {
            bloqueSemanasTarea.classList.toggle('hidden', !this.checked);
        });
    }

    const alertaMonitoreoExito = document.getElementById('alerta_monitoreo_exito');
    if (alertaMonitoreoExito) {
        setTimeout(() => alertaMonitoreoExito.classList.add('hidden'), 3000);
    }
</script>
@endsection
