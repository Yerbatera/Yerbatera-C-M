@extends('layouts.app')

@section('contenido')
@php
    $empleadoSeleccionadoId = (int) request('empleado_id', $personalHorario->first()?->id ?? 0);
    $abrirHorariosInicial = request()->boolean('abrir_horarios');
@endphp

<div class="container mx-auto px-4 pb-6">
    <!-- Cabecera -->
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-green-900">Gestión de Personal · Turnos de producción</h1>
            <p class="text-sm text-gray-600">Control de personal (operarios y administrativos), límites horarios y asignación semanal/mensual.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                id="btn_abrir_modal_horarios"
                class="cursor-pointer rounded-lg border border-green-900 bg-green-800 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-green-900"
            >
                Cargar Horarios
            </button>
            <button
                type="button"
                id="btn_abrir_modal_personal"
                class="cursor-pointer rounded-lg border border-amber-700 bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-amber-700"
            >
                Personal
            </button>
        </div>
    </div>

    <!-- Aviso informativo (sin botones) de trabajadores sin horarios asignados en la semana -->
    @php
        $idsSinHorario = $empleadosSinHorario->pluck('id')->all();
    @endphp
    @if ($empleadosSinHorario->isNotEmpty())
        @php
            $cantSinHorario = $empleadosSinHorario->count();
        @endphp
        <div class="mb-5 rounded-xl border border-amber-400 bg-amber-50/90 px-4 py-3 text-sm text-amber-900 shadow-2xs" role="alert">
            <p class="font-semibold">
                Atención: {{ $cantSinHorario }} {{ $cantSinHorario === 1 ? 'empleado sin horario asignado' : 'empleados sin horarios asignados' }} esta semana.
            </p>
        </div>
    @endif

    <div
        id="notificacion_personal"
        class="mb-4 hidden rounded-lg border p-3 text-sm font-medium transition-opacity duration-300"
        role="status"
    ></div>

    @if (session('success'))
        <div id="alerta_turno_exito" class="mb-4 rounded-lg border border-green-400 bg-green-50 p-4 text-sm font-medium text-green-900" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-400 bg-red-50 p-4 text-sm font-medium text-red-900" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- Lista oculta de operarios para compatibilidad -->
    <datalist id="empleados" class="hidden">
        @foreach ($empleados as $empleado)
            <option value="{{ $empleado->name }}">
        @endforeach
    </datalist>

    <!-- Barra de Control UX/UI: Navegador de Semana sin saltos + Botones de Sector de alto contraste + Buscador con Desplegable -->
    @php
        $totalTodos = $personalHorario->count();
        $totalOperarios = $personalHorario->where('rol', 'operario')->count();
        $totalAdministrativos = $personalHorario->where('rol', 'administracion')->count();
        $esSemanaActual = $weekStart->isSameDay(now()->startOfWeek(\Carbon\Carbon::MONDAY));
    @endphp
    <div class="mb-5 rounded-2xl border border-gray-300 bg-white p-4 shadow-xs">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <!-- 1. Navegador de Semana (grupo horizontal unido que nunca se desarma en dos renglones) -->
            <div class="flex flex-col gap-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-600">
                    1. Período semanal (Lunes a Sábado)
                </span>
                <div class="flex flex-nowrap items-center gap-2">
                    <div class="inline-flex flex-nowrap items-center overflow-hidden rounded-xl border border-gray-300 bg-white shadow-2xs">
                        <a
                            href="{{ $semanaAnteriorUrl }}"
                            class="inline-flex items-center whitespace-nowrap bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-700 transition hover:bg-green-50 hover:text-green-900 active:bg-green-100"
                            title="Ir a la semana anterior"
                        >
                            <span>Anterior</span>
                        </a>
                        <div class="whitespace-nowrap border-x border-gray-300 bg-green-100/75 px-4 py-2 text-xs font-extrabold text-green-950">
                            {{ $weekStart->format('d/m/Y') }} al {{ $weekEnd->format('d/m/Y') }}
                        </div>
                        <a
                            href="{{ $semanaSiguienteUrl }}"
                            class="inline-flex items-center whitespace-nowrap bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-700 transition hover:bg-green-50 hover:text-green-900 active:bg-green-100"
                            title="Ir a la semana siguiente"
                        >
                            <span>Siguiente</span>
                        </a>
                    </div>

                    @if (! $esSemanaActual)
                        <a
                            href="{{ $semanaActualUrl }}"
                            class="whitespace-nowrap rounded-xl border border-green-600 bg-green-50 px-3 py-2 text-xs font-bold text-green-900 transition hover:bg-green-800 hover:text-white"
                        >
                            Ir a hoy
                        </a>
                    @endif
                </div>
            </div>

            <!-- 2. Botones de Filtro por Tipo de Personal (alto contraste visual) -->
            <div class="flex flex-col gap-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-600">
                    2. Filtrar por sector
                </span>
                <div class="flex flex-nowrap items-center gap-2 overflow-x-auto pb-0.5" role="tablist" aria-label="Filtrar por tipo de personal">
                    <button
                        type="button"
                        data-filtro-rol="todos"
                        class="btn-filtro-rol cursor-pointer inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-green-800 bg-green-800 px-3.5 py-2 text-xs font-bold text-white shadow-2xs transition"
                    >
                        <span>Todos</span>
                        <span class="badge-contador-rol rounded-full bg-white/25 px-2 py-0.5 text-[11px] font-extrabold text-white">
                            {{ $totalTodos }}
                        </span>
                    </button>
                    <button
                        type="button"
                        data-filtro-rol="operario"
                        class="btn-filtro-rol cursor-pointer inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-700 shadow-2xs transition hover:border-green-600 hover:bg-green-50 hover:text-green-900"
                    >
                        <span>Operarios</span>
                        <span class="badge-contador-rol rounded-full border border-gray-200 bg-white px-2 py-0.5 text-[11px] font-extrabold text-gray-700">
                            {{ $totalOperarios }}
                        </span>
                    </button>
                    <button
                        type="button"
                        data-filtro-rol="administracion"
                        class="btn-filtro-rol cursor-pointer inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-700 shadow-2xs transition hover:border-green-600 hover:bg-green-50 hover:text-green-900"
                    >
                        <span>Administrativos</span>
                        <span class="badge-contador-rol rounded-full border border-gray-200 bg-white px-2 py-0.5 text-[11px] font-extrabold text-gray-700">
                            {{ $totalAdministrativos }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- 3. Buscador Autocompletable con Desplegable de Empleados -->
            <div class="relative flex flex-col gap-1.5 sm:w-72" id="contenedor_buscador_empleado">
                <label for="input_buscar_empleado" class="whitespace-nowrap text-[11px] font-bold uppercase tracking-wider text-gray-600">
                    3. Buscar o elegir 1 empleado
                </label>
                <div class="relative flex items-center">
                    <input
                        type="text"
                        id="input_buscar_empleado"
                        autocomplete="off"
                        placeholder="Escribí un nombre (ej: Juan...)"
                        class="w-full rounded-xl border border-gray-300 bg-gray-50/80 py-2 pr-24 pl-3 text-xs font-semibold text-gray-800 placeholder-gray-400 shadow-2xs transition focus:border-green-700 focus:bg-white focus:ring-2 focus:ring-green-700/20 focus:outline-none"
                    >
                    <button
                        type="button"
                        id="btn_limpiar_buscador_empleado"
                        class="hidden absolute right-12 rounded-md px-1.5 py-0.5 text-[11px] font-bold text-gray-600 hover:bg-gray-200 hover:text-gray-900"
                        title="Limpiar búsqueda y ver todos"
                    >
                        Limpiar
                    </button>
                    <button
                        type="button"
                        id="btn_toggle_desplegable_empleado"
                        class="absolute right-2 rounded-md px-1.5 py-0.5 text-[11px] font-semibold text-gray-600 hover:bg-gray-200/70 hover:text-gray-900"
                        title="Mostrar lista de empleados"
                    >
                        Lista
                    </button>
                </div>

                <!-- Menú Desplegable de Coincidencias -->
                <div
                    id="desplegable_buscar_empleado"
                    class="hidden absolute top-full right-0 left-0 z-30 mt-1.5 max-h-64 overflow-y-auto rounded-xl border border-gray-300 bg-white p-1.5 shadow-xl"
                >
                    <button
                        type="button"
                        data-seleccionar-emp="todos"
                        class="opcion-buscador-emp flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-bold text-green-900 transition hover:bg-green-50"
                    >
                        <span>Ver todos en la cuadrícula semanal</span>
                        <span class="rounded border border-green-300 bg-green-100 px-1.5 py-0.5 text-[10px] font-semibold text-green-800">General</span>
                    </button>
                    <div class="my-1 border-t border-gray-200"></div>
                    <div id="lista_opciones_buscador_emp" class="space-y-0.5"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- VISTA 1: MATRIZ SEMANAL DE PERSONAL (Estructura Fija Indeformable) -->
    <div id="contenedor_matriz_semanal" class="overflow-hidden rounded-2xl border border-gray-300 bg-white shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-300 bg-gray-50/90 px-4 py-3">
            <div class="flex items-center gap-2.5">
                <h3 id="titulo_matriz_activa" class="text-sm font-extrabold text-gray-900">
                    Cuadrícula Semanal — Todo el Personal
                </h3>
                <span id="subtitulo_conteo_matriz" class="rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-[11px] font-bold text-green-900">
                    {{ $totalTodos }} empleado(s)
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs font-medium text-gray-600">
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Mañana</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Tarde</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Noche</span>
                <span class="text-gray-300">|</span>
                <span class="text-gray-500">Clic en el nombre para ver su ficha · Clic en un día para editar</span>
            </div>
        </div>

        <div>
            <table class="w-full table-fixed border-collapse text-left">
                <colgroup>
                    <col class="w-[17%]">
                    @foreach ($diasSemana as $dia)
                        <col class="w-[12%]">
                    @endforeach
                    <col class="w-[11%]">
                </colgroup>
                <thead>
                    <tr class="border-b border-gray-300 bg-gray-100 text-[11px] font-extrabold uppercase tracking-wider text-gray-700">
                        <th class="p-3">Personal / Rol</th>
                        @foreach ($diasSemana as $dia)
                            <th class="border-l border-gray-300 p-2 text-center">
                                <div class="text-gray-900">{{ $dia['nombre'] }}</div>
                                <div class="text-[11px] font-semibold text-gray-500">{{ $dia['fecha_corta'] }}</div>
                            </th>
                        @endforeach
                        <th class="border-l border-gray-300 bg-gray-200/60 p-2 text-center text-gray-800">Total Sem.</th>
                    </tr>
                    <!-- Fila Resumen de Cobertura Diaria (debajo del día de la semana) -->
                    <tr class="border-b border-gray-300 bg-green-50/25 text-xs font-bold normal-case tracking-normal text-gray-700">
                        <th class="px-3 py-1.5 text-[11px] font-bold text-gray-600">
                            Personal activo
                        </th>
                        @foreach ($diasSemana as $dia)
                            <th class="border-l border-gray-300 px-2 py-1.5 text-center">
                                <span
                                    data-cobertura-dia="{{ $dia['fecha'] }}"
                                    class="badge-cobertura-dia inline-flex items-center rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-900"
                                >
                                    0 activos
                                </span>
                            </th>
                        @endforeach
                        <th class="border-l border-gray-300 bg-gray-100/70 px-2 py-1.5 text-center">
                            <span
                                id="badge_activos_semana"
                                class="inline-flex items-center rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-900"
                            >
                                0/0 activos
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody id="tbody_matriz_semanal" class="divide-y divide-gray-300 text-sm text-gray-700">
                    @forelse ($personalHorario as $persona)
                        @php
                            $sinHorarioFila = in_array($persona->id, $idsSinHorario, true);
                            $horasSem = (float) ($horasSemanalesPorEmpleado[$persona->id] ?? 0);
                            $maxSem = (float) ($persona->horas_max_semana ?? 48);
                            $porcentajeSem = $maxSem > 0 ? min(100, round(($horasSem / $maxSem) * 100)) : 0;
                            $inicialPersona = mb_strtoupper(mb_substr(trim($persona->name), 0, 1));
                        @endphp
                        <tr
                            data-fila-matriz="{{ $persona->id }}"
                            data-rol="{{ $persona->rol }}"
                            data-nombre-busqueda="{{ mb_strtolower($persona->name) }}"
                            class="fila-empleado-matriz group transition hover:bg-green-50/30"
                        >
                            <!-- Columna Empleado (sin botón de editar) -->
                            <td class="p-2.5 align-middle">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border font-extrabold text-xs {{ $persona->rol === 'administracion' ? 'border-blue-300 bg-blue-100 text-blue-900' : 'border-green-300 bg-green-100 text-green-900' }}">
                                        {{ $inicialPersona }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <button
                                            type="button"
                                            data-enfocar-empleado="{{ $persona->id }}"
                                            class="btn-enfocar-empleado block w-full truncate text-left text-xs font-bold text-gray-900 hover:text-green-800 hover:underline"
                                            title="Ver ficha individual de {{ $persona->name }}"
                                        >
                                            {{ $persona->name }}
                                        </button>
                                        <div class="mt-0.5 flex flex-wrap items-center gap-1">
                                            @if ($persona->rol === 'administracion')
                                                <span class="inline-block rounded border border-blue-300 bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-800">
                                                    Administrativo
                                                </span>
                                            @else
                                                <span class="inline-block rounded border border-green-300 bg-green-50 px-1.5 py-0.5 text-[10px] font-bold text-green-800">
                                                    Operario
                                                </span>
                                            @endif
                                            @if ($sinHorarioFila)
                                                <span class="inline-block rounded border border-amber-300 bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">
                                                    Sin horario
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Columnas Lunes a Sábado (altura fija h-[76px] en todas las celdas) -->
                            @foreach ($diasSemana as $dia)
                                @php
                                    $celda = $agendaPorEmpleado[$persona->id][$dia['fecha']] ?? ['trabaja' => false];
                                    $trabajaDia = ! empty($celda['trabaja']);
                                    $turnoDia = $celda['turno'] ?? 'mañana';
                                    $clasesTurno = match ($turnoDia) {
                                        'tarde' => 'border border-amber-400 bg-amber-50 text-amber-950 hover:border-amber-600 hover:bg-amber-100/70',
                                        'noche' => 'border border-indigo-400 bg-indigo-50 text-indigo-950 hover:border-indigo-600 hover:bg-indigo-100/70',
                                        default => 'border border-emerald-400 bg-emerald-50 text-emerald-950 hover:border-emerald-600 hover:bg-emerald-100/70',
                                    };
                                    $puntoTurno = match ($turnoDia) {
                                        'tarde' => 'bg-amber-500',
                                        'noche' => 'bg-indigo-500',
                                        default => 'bg-emerald-500',
                                    };
                                @endphp
                                <td
                                    data-celda-fecha="{{ $dia['fecha'] }}"
                                    data-trabaja="{{ $trabajaDia ? '1' : '0' }}"
                                    class="border-l border-gray-200 p-1.5 align-middle"
                                >
                                    @if ($trabajaDia)
                                        <button
                                            type="button"
                                            data-editar-horario="{{ $persona->id }}"
                                            class="btn-editar-horario-fila cursor-pointer flex h-[74px] w-full flex-col justify-between rounded-lg p-2 text-left transition hover:shadow-xs {{ $clasesTurno }}"
                                            title="Editar horario de {{ $persona->name }} ({{ $dia['nombre'] }})"
                                        >
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-tight">
                                                    <span class="h-2 w-2 shrink-0 rounded-full {{ $puntoTurno }}"></span>
                                                    {{ $turnoDia }}
                                                </span>
                                                <span class="rounded border border-gray-200 bg-white px-1.5 py-0.2 text-[10px] font-extrabold shadow-2xs">
                                                    {{ $celda['horas_dia'] ?? 8 }}h
                                                </span>
                                            </div>
                                            <div class="text-xs font-extrabold tracking-tight">
                                                {{ $celda['horario_entrada'] }} - {{ $celda['horario_salida'] }}
                                            </div>
                                            <div class="truncate text-[10px] font-medium opacity-85" title="Descanso: {{ $celda['descanso'] ?: 'Sin descanso' }}">
                                                {{ $celda['descanso'] ? 'Desc.: ' . $celda['descanso'] : 'Sin desc.' }}
                                            </div>
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            data-editar-horario="{{ $persona->id }}"
                                            class="btn-editar-horario-fila cursor-pointer flex h-[74px] w-full flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50/90 text-[11px] font-semibold text-gray-500 transition hover:border-green-500 hover:bg-green-50/60 hover:text-green-900"
                                            title="Asignar horario a {{ $persona->name }} el {{ $dia['nombre'] }}"
                                        >
                                            <span>Franco</span>
                                            <span class="text-[9px] font-medium opacity-0 transition group-hover:opacity-100">Asignar</span>
                                        </button>
                                    @endif
                                </td>
                            @endforeach

                            <!-- Columna Total Semanal -->
                            <td class="border-l border-gray-300 bg-gray-50/80 p-2.5 align-middle text-center">
                                <div class="text-xs font-extrabold {{ $horasSem > 0 ? 'text-gray-900' : 'text-amber-700' }}">
                                    {{ $horasSem }} / {{ $maxSem }} hs
                                </div>
                                <div class="mx-auto mt-1.5 h-2 w-20 overflow-hidden rounded-full border border-gray-200 bg-white">
                                    <div
                                        class="h-full rounded-full {{ $horasSem === 0.0 ? 'bg-amber-400' : ($porcentajeSem >= 95 ? 'bg-amber-600' : 'bg-green-700') }}"
                                        style="width: {{ $porcentajeSem }}%"
                                    ></div>
                                </div>
                                <div class="mt-1 text-[10px] font-medium text-gray-500">
                                    Mes: ~{{ round($horasSem * 4, 1) }} hs
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-400">
                                No hay personal registrado. Usá el botón "Personal" para dar de alta empleados.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="fila_sin_resultados_busqueda" class="hidden">
                        <td colspan="8" class="p-8 text-center text-sm font-medium text-gray-500">
                            No se encontraron empleados que coincidan con la búsqueda actual.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- VISTA 2: FICHA DETALLADA DE 1 SOLO EMPLEADO (Al seleccionar un empleado específico) -->
    <div id="contenedor_vista_individual" class="hidden rounded-xl border border-gray-300 bg-white p-5 shadow-xs">
        <div class="mb-5 flex flex-col gap-3 border-b border-gray-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-800 text-lg font-bold text-white" id="ficha_emp_inicial">
                    E
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 id="ficha_emp_nombre" class="text-lg font-bold text-gray-900">Nombre del Empleado</h3>
                        <span id="ficha_emp_rol" class="rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Operario</span>
                    </div>
                    <p id="ficha_emp_resumen" class="mt-0.5 text-xs text-gray-600">
                        Semana del {{ $weekStart->format('d/m/Y') }} al {{ $weekEnd->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    id="btn_volver_cuadricula"
                    class="cursor-pointer rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-200"
                >
                    Volver a la cuadrícula
                </button>
                <button
                    type="button"
                    id="btn_editar_desde_ficha"
                    class="cursor-pointer rounded-lg bg-green-800 px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-900"
                >
                    Editar horarios de este empleado
                </button>
            </div>
        </div>

        <!-- Resumen de métricas del empleado en la semana -->
        <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-300 bg-gray-50 p-3.5">
                <div class="text-xs font-medium text-gray-600">Días laborales asignados</div>
                <div id="ficha_metric_dias" class="mt-1 text-xl font-bold text-gray-900">0 de 6 días</div>
            </div>
            <div class="rounded-xl border border-green-300 bg-green-50/70 p-3.5">
                <div class="text-xs font-medium text-green-900">Carga horaria esta semana</div>
                <div id="ficha_metric_semana" class="mt-1 text-xl font-bold text-green-900">0 / 48 hs</div>
            </div>
            <div class="rounded-xl border border-blue-300 bg-blue-50/70 p-3.5">
                <div class="text-xs font-medium text-blue-900">Proyección mensual (4 sem.)</div>
                <div id="ficha_metric_mes" class="mt-1 text-xl font-bold text-blue-900">0 hs / mes</div>
            </div>
        </div>

        <!-- Tarjetas de Lunes a Sábado del empleado seleccionado -->
        <div id="grilla_ficha_individual" class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($diasSemana as $dia)
                <div
                    data-ficha-dia="{{ $dia['fecha'] }}"
                    data-ficha-nombre="{{ $dia['nombre'] }}"
                    data-ficha-corta="{{ $dia['fecha_corta'] }}"
                    class="tarjeta-ficha-dia rounded-xl border p-4 transition"
                >
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2.5">
                        <div>
                            <span class="text-sm font-bold text-gray-900">{{ $dia['nombre'] }}</span>
                            <span class="ml-1 text-xs text-gray-500">({{ $dia['fecha_corta'] }})</span>
                        </div>
                        <span class="ficha-dia-badge rounded-full px-2.5 py-0.5 text-xs font-semibold"></span>
                    </div>
                    <div class="ficha-dia-cuerpo mt-3 space-y-1.5 text-xs"></div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- VENTANA EMERGENTE 1: Cargar / Editar Horarios del Empleado (Operarios y Administrativos) -->
<div
    id="modal_horarios_empleado"
    class="fixed inset-0 z-50 {{ $abrirHorariosInicial ? 'flex' : 'hidden' }} items-center justify-center bg-black/50 p-[3%] backdrop-blur-xs"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo_modal_horarios"
>
    <div class="max-h-[94vh] w-full max-w-4xl overflow-y-auto rounded-2xl border border-gray-300 bg-white px-7 py-5 shadow-2xl">
        <form method="POST" action="{{ route('turnos.semanal.store') }}">
            @csrf
            <input type="hidden" name="semana" value="{{ $weekStart->toDateString() }}">

            <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pb-3.5">
                <h2 id="titulo_modal_horarios" class="text-base font-bold text-green-900">
                    Cargar Horarios Semanales del Personal (Lunes a Sábado)
                </h2>

                <div class="flex items-center gap-2">
                    <label for="select_empleado_semana" class="text-xs font-semibold text-gray-700 whitespace-nowrap">
                        Empleado:
                    </label>
                    <select
                        name="empleado_id"
                        id="select_empleado_semana"
                        required
                        class="w-72 rounded-lg border border-gray-300 bg-gray-50/70 px-2.5 py-1.5 text-xs font-semibold text-gray-800 focus:border-green-700 focus:bg-white"
                    >
                        @foreach ($personalHorario as $persona)
                            @php
                                $sinHorario = in_array($persona->id, $idsSinHorario, true);
                            @endphp
                            <option
                                value="{{ $persona->id }}"
                                data-sin-horario="{{ $sinHorario ? '1' : '0' }}"
                                @selected($persona->id === $empleadoSeleccionadoId)
                            >
                                {{ $persona->name }} — {{ $persona->rol === 'administracion' ? 'Administrativo' : 'Operario' }}@if ($persona->dni) (DNI: {{ $persona->dni }})@endif{{ $sinHorario ? ' — Sin horarios cargados' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button
                        type="button"
                        id="btn_cerrar_modal_horarios"
                        class="cursor-pointer rounded-md border border-gray-300 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700"
                        aria-label="Cerrar ventana"
                    >
                        Cerrar
                    </button>
                </div>
            </div>

            <!-- Grilla de Lunes a Sábado -->
            <div class="mb-2.5 grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($diasSemana as $dia)
                    @php
                        $fechaIso = $dia['fecha'];
                        $estadoInicial = $agendaPorEmpleado[$empleadoSeleccionadoId][$fechaIso] ?? [
                            'trabaja' => false,
                            'horario_entrada' => '06:00',
                            'horario_salida' => '14:00',
                            'tiene_descanso' => false,
                            'descanso_inicio' => '10:00',
                            'descanso_fin' => '10:30',
                            'descanso' => '',
                        ];
                        $tieneDescansoInicial = ! empty($estadoInicial['tiene_descanso']);
                    @endphp
                    <div
                        data-dia-card="{{ $fechaIso }}"
                        data-dia-nombre="{{ $dia['nombre'] }}"
                        class="tarjeta-dia-personal rounded-xl border p-2.5 transition {{ $estadoInicial['trabaja'] ? 'border-green-500 bg-green-50/50' : 'border-red-300 bg-red-50/40' }}"
                    >
                        <div class="mb-1.5 flex items-center justify-between border-b border-gray-200 pb-1.5">
                            <div>
                                <span class="text-xs font-bold text-gray-900">{{ $dia['nombre'] }}</span>
                                <span class="ml-1 text-[11px] font-medium text-gray-500">({{ $dia['fecha_corta'] }})</span>
                            </div>

                            <label class="inline-flex cursor-pointer items-center gap-1.5">
                                <input
                                    type="checkbox"
                                    name="dias[{{ $fechaIso }}][trabaja]"
                                    value="1"
                                    data-fecha="{{ $fechaIso }}"
                                    @checked($estadoInicial['trabaja'])
                                    class="chk-trabaja-dia h-3.5 w-3.5 rounded border-gray-300 text-green-800 focus:ring-green-700"
                                >
                                <span class="badge-estado-dia rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $estadoInicial['trabaja'] ? 'bg-green-700 text-white' : 'border border-red-300 bg-red-100 text-red-800' }}">
                                    {{ $estadoInicial['trabaja'] ? 'Trabaja' : 'No trabaja' }}
                                </span>
                            </label>
                        </div>

                        <div class="contenedor-inputs-dia space-y-1.5 {{ $estadoInicial['trabaja'] ? '' : 'opacity-45 pointer-events-none' }}">
                            <div class="grid grid-cols-2 gap-1.5">
                                <label class="flex flex-col gap-0.5 text-[11px] font-semibold text-gray-700">
                                    Entrada
                                    <input
                                        type="time"
                                        name="dias[{{ $fechaIso }}][entrada]"
                                        value="{{ $estadoInicial['horario_entrada'] }}"
                                        class="input-entrada-dia w-full rounded-md border border-gray-300 bg-white px-2 py-0.5 text-xs font-medium text-gray-900 focus:border-green-700"
                                    >
                                </label>
                                <label class="flex flex-col gap-0.5 text-[11px] font-semibold text-gray-700">
                                    Salida
                                    <input
                                        type="time"
                                        name="dias[{{ $fechaIso }}][salida]"
                                        value="{{ $estadoInicial['horario_salida'] }}"
                                        class="input-salida-dia w-full rounded-md border border-gray-300 bg-white px-2 py-0.5 text-xs font-medium text-gray-900 focus:border-green-700"
                                    >
                                </label>
                            </div>

                            <!-- Opción de dejar asentado o no horas de descanso -->
                            <div class="rounded-lg border border-green-300/70 bg-white/90 px-2 py-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="inline-flex cursor-pointer items-center gap-1.5 text-[11px] font-semibold text-gray-700">
                                        <input
                                            type="checkbox"
                                            name="dias[{{ $fechaIso }}][tiene_descanso]"
                                            value="1"
                                            @checked($tieneDescansoInicial)
                                            class="chk-tiene-descanso h-3.5 w-3.5 rounded border-gray-300 text-green-800 focus:ring-green-700"
                                        >
                                        <span>Asentar descanso</span>
                                    </label>
                                    <span class="texto-sin-descanso text-[10px] font-medium text-gray-400 {{ $tieneDescansoInicial ? 'hidden' : '' }}">
                                        Sin descanso
                                    </span>
                                </div>

                                <div class="contenedor-rango-descanso mt-1.5 grid grid-cols-2 gap-1.5 {{ $tieneDescansoInicial ? '' : 'hidden' }}">
                                    <label class="flex flex-col gap-0.5 text-[10px] font-semibold text-gray-600">
                                        Inicio descanso
                                        <input
                                            type="time"
                                            name="dias[{{ $fechaIso }}][descanso_inicio]"
                                            value="{{ $estadoInicial['descanso_inicio'] ?? '10:00' }}"
                                            class="input-descanso-inicio w-full rounded border border-gray-300 bg-white px-1.5 py-0.5 text-xs font-medium text-gray-900 focus:border-green-700"
                                        >
                                    </label>
                                    <label class="flex flex-col gap-0.5 text-[10px] font-semibold text-gray-600">
                                        Fin descanso
                                        <input
                                            type="time"
                                            name="dias[{{ $fechaIso }}][descanso_fin]"
                                            value="{{ $estadoInicial['descanso_fin'] ?? '10:30' }}"
                                            class="input-descanso-fin w-full rounded border border-gray-300 bg-white px-1.5 py-0.5 text-xs font-medium text-gray-900 focus:border-green-700"
                                        >
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Leyenda inferior del día -->
                        <div class="leyenda-horas-dia mt-1.5 border-t border-gray-200 pt-1 text-[10px] font-medium text-gray-600">
                            Horas este día: <strong class="valor-horas-dia text-green-900">0 hs</strong>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Alerta de exceso de límite horario diario o semanal -->
            <div
                id="alerta_limite_horas"
                class="mb-2 hidden rounded-lg border border-red-400 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-900"
                role="alert"
            ></div>

            <!-- Leyenda inferior general: Horas por día, por semana y por mes -->
            <div class="mb-2.5 rounded-lg border border-green-300 bg-green-50/80 px-3 py-1.5 text-xs text-green-950">
                <span class="font-semibold">Resumen de carga horaria:</span>
                Por día (promedio activo): <strong id="leyenda_horas_dia">0 hs</strong> ·
                Total por semana: <strong id="leyenda_horas_semana">0 hs</strong> (Máx: <span id="leyenda_tope_semana">48 hs</span>) ·
                Total por mes (4 semanas): <strong id="leyenda_horas_mes">0 hs</strong>
            </div>

            <div class="flex flex-col gap-2 border-t border-gray-200 pt-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <label class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-medium text-gray-800">
                        <input
                            type="checkbox"
                            name="repetir_mensual"
                            value="1"
                            class="h-3.5 w-3.5 rounded border-gray-300 text-green-800"
                        >
                        <span>Repetir y dejar registrado por meses:</span>
                    </label>
                    <select name="meses_repeticion" class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-800">
                        <option value="1" selected>1 mes</option>
                        <option value="2">2 meses</option>
                        <option value="3">3 meses</option>
                        <option value="6">6 meses</option>
                        <option value="12">12 meses (1 año)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        id="btn_cancelar_modal_horarios"
                        class="cursor-pointer rounded-lg border border-gray-300 bg-gray-100 px-3.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        id="btn_submit_horarios_semana"
                        class="cursor-pointer rounded-lg bg-green-800 px-4 py-1.5 text-xs font-semibold text-white shadow-2xs transition hover:bg-green-900"
                    >
                        Guardar Horarios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- VENTANA EMERGENTE 2: Alta y Edición de Personal (con Límites de Horas por Día y por Semana) -->
<div
    id="modal_personal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
    role="dialog"
    aria-modal="true"
>
    <div class="w-full max-w-2xl rounded-xl border border-gray-300 bg-white p-6 shadow-xl">
        <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-3">
            <h3 class="text-lg font-bold text-green-900">Nómina de Personal (Operarios y Administrativos)</h3>
            <button type="button" id="btn_cerrar_modal_personal" class="cursor-pointer rounded-md border border-gray-300 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700">Cerrar</button>
        </div>

        <div class="mb-4 flex gap-2 border-b border-gray-200 pb-3">
            <button type="button" id="tab_personal_form" class="cursor-pointer rounded-lg bg-green-800 px-3.5 py-1.5 text-xs font-semibold text-white transition">
                Agregar Empleado
            </button>
            <button type="button" id="tab_personal_lista" class="cursor-pointer rounded-lg border border-gray-300 bg-gray-100 px-3.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200">
                Lista de Personal (<span id="contador_personal_modal">{{ $personalHorario->count() }}</span>)
            </button>
        </div>

        <div id="error_modal_personal" class="mb-3 hidden rounded-md border border-red-300 bg-red-50 p-3 text-xs font-semibold text-red-800"></div>

        <!-- Vista Formulario -->
        <div id="vista_personal_form">
            <form id="form_personal" action="{{ route('personal.empleados.store') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" id="personal_id_edicion" value="">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                        Nombre y Apellido *
                        <input
                            type="text"
                            name="nombre"
                            id="personal_nombre"
                            required
                            maxlength="255"
                            placeholder="Ej: Carlos Gómez"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-700"
                        >
                    </label>

                    <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                        Rol / Sector *
                        <select
                            name="rol"
                            id="personal_rol"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-700"
                        >
                            <option value="operario">Operario de planta</option>
                            <option value="administracion">Administrativo</option>
                        </select>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                        DNI (solo números)
                        <input
                            type="text"
                            name="dni"
                            id="personal_dni"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="15"
                            placeholder="Ej: 35123456"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-700"
                        >
                    </label>

                    <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                        Teléfono (solo números)
                        <input
                            type="text"
                            name="telefono"
                            id="personal_telefono"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="20"
                            placeholder="Ej: 3764556677"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-700"
                        >
                    </label>
                </div>

                <!-- Límite de horas por día y por semana del perfil -->
                <div class="grid grid-cols-1 gap-4 rounded-lg border border-green-300 bg-green-50/50 p-3.5 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-sm font-medium text-gray-800">
                        Horas máximas de trabajo por Día *
                        <input
                            type="number"
                            name="horas_max_dia"
                            id="personal_horas_dia"
                            value="8"
                            step="0.5"
                            min="1"
                            max="24"
                            required
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-700"
                        >
                    </label>

                    <label class="flex flex-col gap-1 text-sm font-medium text-gray-800">
                        Horas máximas de trabajo por Semana *
                        <input
                            type="number"
                            name="horas_max_semana"
                            id="personal_horas_semana"
                            value="48"
                            step="0.5"
                            min="1"
                            max="168"
                            required
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-700"
                        >
                    </label>
                </div>

                <div class="mt-2 flex items-center justify-end gap-2 border-t border-gray-200 pt-4">
                    <button type="button" id="btn_volver_lista_personal" class="hidden cursor-pointer rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                        Volver a la lista
                    </button>
                    <button type="button" id="btn_cancelar_personal" class="cursor-pointer rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                        Cerrar
                    </button>
                    <button type="submit" id="btn_submit_personal" class="cursor-pointer rounded-md bg-green-800 px-4 py-2 text-sm font-semibold text-white hover:bg-green-900">
                        Guardar Empleado
                    </button>
                </div>
            </form>
        </div>

        <!-- Vista Lista -->
        <div id="vista_personal_lista" class="hidden">
            <div class="max-h-72 overflow-y-auto rounded-lg border border-gray-300">
                <table class="w-full border-collapse text-left text-sm">
                    <thead class="sticky top-0 bg-gray-100 text-xs font-bold uppercase text-gray-600">
                        <tr class="border-b border-gray-300">
                            <th class="p-3">Nombre</th>
                            <th class="p-3">Rol</th>
                            <th class="p-3">Límite (Día / Sem.)</th>
                            <th class="p-3">DNI / Tel.</th>
                            <th class="p-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_modal_personal" class="divide-y divide-gray-200 text-gray-700">
                        @foreach ($personalHorario as $persona)
                            <tr
                                data-personal-row="{{ $persona->id }}"
                                data-nombre="{{ $persona->name }}"
                                data-rol="{{ $persona->rol }}"
                                data-dni="{{ $persona->dni ?? '' }}"
                                data-telefono="{{ $persona->telefono ?? '' }}"
                                data-horas-dia="{{ (float) ($persona->horas_max_dia ?? 8) }}"
                                data-horas-semana="{{ (float) ($persona->horas_max_semana ?? 48) }}"
                                class="hover:bg-gray-50"
                            >
                                <td class="p-3 font-medium text-gray-900 col-nombre">{{ $persona->name }}</td>
                                <td class="p-3 col-rol">{{ $persona->rol === 'administracion' ? 'Administrativo' : 'Operario' }}</td>
                                <td class="p-3 text-xs font-semibold text-green-900 col-limites">
                                    {{ (float) ($persona->horas_max_dia ?? 8) }} hs/día · {{ (float) ($persona->horas_max_semana ?? 48) }} hs/sem
                                </td>
                                <td class="p-3 text-xs col-contacto">
                                    DNI: {{ $persona->dni ?? '—' }}<br>Tel: {{ $persona->telefono ?? '—' }}
                                </td>
                                <td class="p-3 text-right">
                                    <button type="button" class="btn-editar-personal cursor-pointer rounded-md border border-amber-400 bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-200">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const agendaPorEmpleado = @json($agendaPorEmpleado);
    const limitesPorEmpleado = @json($limitesPorEmpleado);
    const catalogoEmpleados = @json($catalogoEmpleados ?? []);

    const modalHorarios = document.getElementById('modal_horarios_empleado');
    const btnAbrirModalHorarios = document.getElementById('btn_abrir_modal_horarios');
    const btnCerrarModalHorarios = document.getElementById('btn_cerrar_modal_horarios');
    const btnCancelarModalHorarios = document.getElementById('btn_cancelar_modal_horarios');
    const selectEmpleadoSemana = document.getElementById('select_empleado_semana');
    const alertaLimiteHoras = document.getElementById('alerta_limite_horas');
    const btnSubmitHorariosSemana = document.getElementById('btn_submit_horarios_semana');

    // --- Controles de la Cuadrícula Semanal, Buscador Autocompletable y Ficha Individual ---
    const contenedorMatrizSemanal = document.getElementById('contenedor_matriz_semanal');
    const contenedorVistaIndividual = document.getElementById('contenedor_vista_individual');
    const tituloMatrizActiva = document.getElementById('titulo_matriz_activa');
    const subtituloConteoMatriz = document.getElementById('subtitulo_conteo_matriz');
    const filaSinResultadosBusqueda = document.getElementById('fila_sin_resultados_busqueda');
    const btnVolverCuadricula = document.getElementById('btn_volver_cuadricula');
    const btnEditarDesdeFicha = document.getElementById('btn_editar_desde_ficha');

    const contenedorBuscadorEmpleado = document.getElementById('contenedor_buscador_empleado');
    const inputBuscarEmpleado = document.getElementById('input_buscar_empleado');
    const btnLimpiarBuscadorEmpleado = document.getElementById('btn_limpiar_buscador_empleado');
    const btnToggleDesplegableEmpleado = document.getElementById('btn_toggle_desplegable_empleado');
    const desplegableBuscarEmpleado = document.getElementById('desplegable_buscar_empleado');
    const listaOpcionesBuscadorEmp = document.getElementById('lista_opciones_buscador_emp');

    let rolFiltroActivo = 'todos';
    let empleadoEnfocadoId = null;
    let textoBusquedaActivo = '';

    function normalizarTexto(str) {
        return String(str || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function coincideNombreEmpleado(nombreOriginal, queryNormalizada) {
        if (!queryNormalizada) return { coincide: true, prioridad: 1 };
        const nombreNorm = normalizarTexto(nombreOriginal);
        if (nombreNorm.startsWith(queryNormalizada)) {
            return { coincide: true, prioridad: 1 };
        }
        const palabras = nombreNorm.split(/\s+/);
        for (const palabra of palabras) {
            if (palabra.startsWith(queryNormalizada)) {
                return { coincide: true, prioridad: 2 };
            }
        }
        if (nombreNorm.includes(queryNormalizada)) {
            return { coincide: true, prioridad: 3 };
        }
        return { coincide: false, prioridad: 99 };
    }

    function resaltarCoincidencia(texto, query) {
        if (!query) return texto;
        const textoNorm = normalizarTexto(texto);
        const queryNorm = normalizarTexto(query);
        const idx = textoNorm.indexOf(queryNorm);
        if (idx === -1) return texto;
        const antes = texto.slice(0, idx);
        const match = texto.slice(idx, idx + query.length);
        const despues = texto.slice(idx + query.length);
        return `${antes}<mark class="rounded bg-green-200 px-0.5 font-extrabold text-green-950">${match}</mark>${despues}`;
    }

    function renderizarOpcionesBuscador(query = '') {
        if (!listaOpcionesBuscadorEmp) return;
        const queryNorm = normalizarTexto(query);
        const listaEmpleados = Object.values(catalogoEmpleados);

        const filtrados = listaEmpleados
            .map((emp) => {
                const res = coincideNombreEmpleado(emp.name, queryNorm);
                const coincideRol = rolFiltroActivo === 'todos' || emp.rol === rolFiltroActivo;
                return { emp, ...res, coincideRol };
            })
            .filter((item) => item.coincide && item.coincideRol)
            .sort((a, b) => a.prioridad - b.prioridad || a.emp.name.localeCompare(b.emp.name));

        listaOpcionesBuscadorEmp.innerHTML = '';

        if (filtrados.length === 0) {
            listaOpcionesBuscadorEmp.innerHTML = `
                <div class="px-3 py-2.5 text-center text-xs text-gray-400">
                    No hay empleados que coincidan con "${query}"
                </div>
            `;
            return;
        }

        filtrados.forEach(({ emp }) => {
            const esAdmin = emp.rol === 'administracion';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.seleccionarEmp = String(emp.id);
            btn.className = 'opcion-buscador-emp flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs transition hover:bg-green-50';
            btn.innerHTML = `
                <div class="flex items-center gap-2 min-w-0">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[10px] font-bold ${esAdmin ? 'bg-blue-100 text-blue-900' : 'bg-green-100 text-green-900'}">
                        ${(emp.name || 'E').trim().charAt(0).toUpperCase()}
                    </span>
                    <span class="truncate font-semibold text-gray-800">${resaltarCoincidencia(emp.name, query.trim())}</span>
                </div>
                <span class="ml-2 shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold ${esAdmin ? 'bg-blue-50 text-blue-800' : 'bg-green-50 text-green-800'}">
                    ${esAdmin ? 'Administrativo' : 'Operario'}
                </span>
            `;
            btn.addEventListener('click', function () {
                seleccionarEmpleadoDesdeBuscador(String(emp.id));
            });
            listaOpcionesBuscadorEmp.appendChild(btn);
        });
    }

    function recalcularCoberturaMatriz() {
        let filasVisibles = 0;
        let activosEnLaSemana = 0;

        document.querySelectorAll('.fila-empleado-matriz').forEach(function (fila) {
            if (!fila.classList.contains('hidden')) {
                filasVisibles++;
                const trabajaAlgunDia = fila.querySelector('td[data-trabaja="1"]') !== null;
                if (trabajaAlgunDia) {
                    activosEnLaSemana++;
                }
            }
        });

        if (subtituloConteoMatriz) {
            subtituloConteoMatriz.textContent = `${filasVisibles} empleado(s)`;
        }

        const badgeActivosSemana = document.getElementById('badge_activos_semana');
        if (badgeActivosSemana) {
            badgeActivosSemana.textContent = `${activosEnLaSemana}/${filasVisibles} activos`;
            badgeActivosSemana.className = activosEnLaSemana > 0
                ? 'inline-flex items-center rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-900'
                : 'inline-flex items-center rounded-full border border-red-300 bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800';
        }

        if (filaSinResultadosBusqueda) {
            filaSinResultadosBusqueda.classList.toggle('hidden', filasVisibles > 0);
        }

        document.querySelectorAll('.badge-cobertura-dia').forEach(function (badge) {
            const fecha = badge.dataset.coberturaDia;
            let activos = 0;

            document.querySelectorAll('.fila-empleado-matriz').forEach(function (fila) {
                if (fila.classList.contains('hidden')) return;
                const celda = fila.querySelector(`td[data-celda-fecha="${fecha}"]`);
                if (celda && celda.dataset.trabaja === '1') {
                    activos++;
                }
            });

            badge.textContent = activos === 1 ? '1 activo' : `${activos} activos`;
            badge.className = activos > 0
                ? 'badge-cobertura-dia inline-flex items-center rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-900'
                : 'badge-cobertura-dia inline-flex items-center rounded-full border border-red-300 bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800';
        });
    }

    function actualizarEstilosBotonesRol() {
        document.querySelectorAll('.btn-filtro-rol').forEach(function (btn) {
            const activo = btn.dataset.filtroRol === rolFiltroActivo;
            const badge = btn.querySelector('.badge-contador-rol');

            if (activo) {
                btn.className = 'btn-filtro-rol cursor-pointer inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-green-800 bg-green-800 px-3.5 py-2 text-xs font-bold text-white shadow-2xs transition';
                if (badge) {
                    badge.className = 'badge-contador-rol rounded-full bg-white/25 px-2 py-0.5 text-[11px] font-extrabold text-white';
                }
            } else {
                btn.className = 'btn-filtro-rol cursor-pointer inline-flex items-center gap-2 whitespace-nowrap rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-700 shadow-2xs transition hover:border-green-600 hover:bg-green-50 hover:text-green-900';
                if (badge) {
                    badge.className = 'badge-contador-rol rounded-full border border-gray-200 bg-white px-2 py-0.5 text-[11px] font-extrabold text-gray-700';
                }
            }
        });
    }

    function filtrarFilasMatriz() {
        const queryNorm = normalizarTexto(textoBusquedaActivo);

        document.querySelectorAll('.fila-empleado-matriz').forEach(function (fila) {
            const coincideRol = rolFiltroActivo === 'todos' || fila.dataset.rol === rolFiltroActivo;
            const infoEmp = catalogoEmpleados[fila.dataset.filaMatriz];
            const nombreEmp = infoEmp ? infoEmp.name : (fila.dataset.nombreBusqueda || '');
            const { coincide } = coincideNombreEmpleado(nombreEmp, queryNorm);

            fila.classList.toggle('hidden', !(coincideRol && coincide));
        });

        recalcularCoberturaMatriz();
    }

    function aplicarFiltroRol(rol) {
        rolFiltroActivo = rol;
        empleadoEnfocadoId = null;

        contenedorVistaIndividual.classList.add('hidden');
        contenedorMatrizSemanal.classList.remove('hidden');

        actualizarEstilosBotonesRol();

        if (tituloMatrizActiva) {
            tituloMatrizActiva.textContent = rol === 'operario'
                ? 'Cuadrícula Semanal — Solo Operarios'
                : (rol === 'administracion' ? 'Cuadrícula Semanal — Solo Administrativos' : 'Cuadrícula Semanal — Todo el Personal');
        }

        filtrarFilasMatriz();
        renderizarOpcionesBuscador(textoBusquedaActivo);
    }

    function seleccionarEmpleadoDesdeBuscador(valor) {
        if (desplegableBuscarEmpleado) {
            desplegableBuscarEmpleado.classList.add('hidden');
        }

        if (valor === 'todos') {
            textoBusquedaActivo = '';
            if (inputBuscarEmpleado) inputBuscarEmpleado.value = '';
            if (btnLimpiarBuscadorEmpleado) btnLimpiarBuscadorEmpleado.classList.add('hidden');
            aplicarFiltroRol(rolFiltroActivo);
            return;
        }

        const infoEmp = catalogoEmpleados[valor];
        if (!infoEmp) return;

        if (inputBuscarEmpleado) {
            inputBuscarEmpleado.value = infoEmp.name;
        }
        if (btnLimpiarBuscadorEmpleado) {
            btnLimpiarBuscadorEmpleado.classList.remove('hidden');
        }
        mostrarFichaIndividual(valor);
    }

    function mostrarFichaIndividual(empId) {
        const infoEmp = catalogoEmpleados[empId];
        if (!infoEmp) return;

        empleadoEnfocadoId = String(empId);
        if (inputBuscarEmpleado) {
            inputBuscarEmpleado.value = infoEmp.name;
        }
        if (btnLimpiarBuscadorEmpleado) {
            btnLimpiarBuscadorEmpleado.classList.remove('hidden');
        }
        if (desplegableBuscarEmpleado) {
            desplegableBuscarEmpleado.classList.add('hidden');
        }

        contenedorMatrizSemanal.classList.add('hidden');
        contenedorVistaIndividual.classList.remove('hidden');

        const esAdmin = infoEmp.rol === 'administracion';
        document.getElementById('ficha_emp_inicial').textContent = (infoEmp.name || 'E').trim().charAt(0).toUpperCase();
        document.getElementById('ficha_emp_nombre').textContent = infoEmp.name;
        const badgeRol = document.getElementById('ficha_emp_rol');
        badgeRol.textContent = esAdmin ? 'Administrativo' : 'Operario';
        badgeRol.className = esAdmin
            ? 'rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800'
            : 'rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800';

        const agendaEmp = agendaPorEmpleado[empId] || {};
        const limitesEmp = limitesPorEmpleado[empId] || { horas_max_dia: 8, horas_max_semana: 48 };
        let diasTrabajados = 0;
        let horasTotalesSemana = 0;

        document.querySelectorAll('.tarjeta-ficha-dia').forEach(function (card) {
            const fecha = card.dataset.fichaDia;
            const datos = agendaEmp[fecha] || { trabaja: false };
            const badge = card.querySelector('.ficha-dia-badge');
            const cuerpo = card.querySelector('.ficha-dia-cuerpo');

            if (datos.trabaja) {
                const hsDia = calcularHorasEntre(datos.horario_entrada || '06:00', datos.horario_salida || '14:00');
                diasTrabajados++;
                horasTotalesSemana += hsDia;

                card.className = 'tarjeta-ficha-dia rounded-xl border border-green-500 bg-green-50/50 p-4 transition';
                badge.className = 'ficha-dia-badge rounded-full bg-green-700 px-2.5 py-0.5 text-[11px] font-semibold text-white capitalize';
                badge.textContent = `Turno ${datos.turno || 'mañana'}`;

                cuerpo.innerHTML = `
                    <div class="flex items-center justify-between text-gray-800">
                        <span class="font-medium text-gray-600">Horario:</span>
                        <span class="text-sm font-bold text-green-950">${datos.horario_entrada} a ${datos.horario_salida}</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-700">
                        <span class="font-medium text-gray-600">Descanso:</span>
                        <span class="font-semibold">${datos.descanso ? datos.descanso : 'Sin descanso'}</span>
                    </div>
                    <div class="mt-2 border-t border-green-200 pt-1.5 flex items-center justify-between">
                        <span class="text-gray-600">Carga del día:</span>
                        <span class="font-bold text-green-900">${formatearHoras(hsDia)} (máx. ${limitesEmp.horas_max_dia} hs)</span>
                    </div>
                `;
            } else {
                card.className = 'tarjeta-ficha-dia rounded-xl border border-gray-300 bg-gray-50/80 p-4 transition';
                badge.className = 'ficha-dia-badge rounded-full border border-gray-300 bg-gray-100 px-2.5 py-0.5 text-[11px] font-semibold text-gray-700';
                badge.textContent = 'Franco / No trabaja';

                cuerpo.innerHTML = `
                    <div class="py-3 text-center text-xs text-gray-500">
                        Sin turno asignado para este día.
                    </div>
                `;
            }
        });

        document.getElementById('ficha_metric_dias').textContent = `${diasTrabajados} de 6 días`;
        document.getElementById('ficha_metric_semana').textContent = `${Math.round(horasTotalesSemana * 10) / 10} / ${limitesEmp.horas_max_semana} hs`;
        document.getElementById('ficha_metric_mes').textContent = `${Math.round(horasTotalesSemana * 4 * 10) / 10} hs / mes`;
    }

    document.querySelectorAll('.btn-filtro-rol').forEach(function (btn) {
        btn.addEventListener('click', function () {
            aplicarFiltroRol(this.dataset.filtroRol);
        });
    });

    if (inputBuscarEmpleado) {
        inputBuscarEmpleado.addEventListener('focus', function () {
            renderizarOpcionesBuscador(this.value);
            desplegableBuscarEmpleado.classList.remove('hidden');
        });

        inputBuscarEmpleado.addEventListener('input', function () {
            textoBusquedaActivo = this.value;
            btnLimpiarBuscadorEmpleado.classList.toggle('hidden', !this.value);

            // Mientras escribe, muestra la cuadrícula filtrada en vivo y el desplegable de coincidencias
            contenedorVistaIndividual.classList.add('hidden');
            contenedorMatrizSemanal.classList.remove('hidden');
            filtrarFilasMatriz();
            renderizarOpcionesBuscador(this.value);
            desplegableBuscarEmpleado.classList.remove('hidden');
        });

        inputBuscarEmpleado.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                desplegableBuscarEmpleado.classList.add('hidden');
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const primeraOpcion = listaOpcionesBuscadorEmp.querySelector('.opcion-buscador-emp');
                if (primeraOpcion) {
                    seleccionarEmpleadoDesdeBuscador(primeraOpcion.dataset.seleccionarEmp);
                }
            }
        });
    }

    if (btnLimpiarBuscadorEmpleado) {
        btnLimpiarBuscadorEmpleado.addEventListener('click', function () {
            seleccionarEmpleadoDesdeBuscador('todos');
            if (inputBuscarEmpleado) inputBuscarEmpleado.focus();
        });
    }

    if (btnToggleDesplegableEmpleado) {
        btnToggleDesplegableEmpleado.addEventListener('click', function () {
            const estaOculto = desplegableBuscarEmpleado.classList.contains('hidden');
            if (estaOculto) {
                renderizarOpcionesBuscador(inputBuscarEmpleado ? inputBuscarEmpleado.value : '');
                desplegableBuscarEmpleado.classList.remove('hidden');
                if (inputBuscarEmpleado) inputBuscarEmpleado.focus();
            } else {
                desplegableBuscarEmpleado.classList.add('hidden');
            }
        });
    }

    document.querySelectorAll('[data-seleccionar-emp="todos"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            seleccionarEmpleadoDesdeBuscador('todos');
        });
    });

    document.addEventListener('click', function (e) {
        if (contenedorBuscadorEmpleado && !contenedorBuscadorEmpleado.contains(e.target)) {
            desplegableBuscarEmpleado.classList.add('hidden');
        }
    });

    document.querySelectorAll('.btn-enfocar-empleado').forEach(function (btn) {
        btn.addEventListener('click', function () {
            mostrarFichaIndividual(this.dataset.enfocarEmpleado);
        });
    });

    if (btnVolverCuadricula) {
        btnVolverCuadricula.addEventListener('click', function () {
            seleccionarEmpleadoDesdeBuscador('todos');
        });
    }

    function abrirModalHorariosParaEmpleado(empId) {
        if (selectEmpleadoSemana && empId) {
            selectEmpleadoSemana.value = String(empId);
            selectEmpleadoSemana.dispatchEvent(new Event('change'));
        }
        abrirModalHorarios();
    }

    document.querySelectorAll('.btn-editar-horario-fila').forEach(function (btn) {
        btn.addEventListener('click', function () {
            abrirModalHorariosParaEmpleado(this.dataset.editarHorario);
        });
    });

    if (btnEditarDesdeFicha) {
        btnEditarDesdeFicha.addEventListener('click', function () {
            if (empleadoEnfocadoId) {
                abrirModalHorariosParaEmpleado(empleadoEnfocadoId);
            }
        });
    }

    recalcularCoberturaMatriz();
    renderizarOpcionesBuscador('');

    function calcularHorasEntre(entradaStr, salidaStr) {
        if (!entradaStr || !salidaStr) return 0;
        const [hE, mE] = entradaStr.split(':').map(Number);
        const [hS, mS] = salidaStr.split(':').map(Number);
        if (isNaN(hE) || isNaN(hS)) return 0;

        let minEntrada = hE * 60 + (mE || 0);
        let minSalida = hS * 60 + (mS || 0);
        if (minSalida <= minEntrada) {
            minSalida += 24 * 60;
        }
        return Math.max(0, (minSalida - minEntrada) / 60);
    }

    function formatearHoras(valor) {
        const redondeado = Math.round(valor * 10) / 10;
        return `${redondeado} hs`;
    }

    function validarDescansoEnHorario(entradaStr, salidaStr, descInicioStr, descFinStr) {
        if (!entradaStr || !salidaStr || !descInicioStr || !descFinStr) return false;
        const [hE, mE] = entradaStr.split(':').map(Number);
        const [hS, mS] = salidaStr.split(':').map(Number);
        const [hDI, mDI] = descInicioStr.split(':').map(Number);
        const [hDF, mDF] = descFinStr.split(':').map(Number);
        if ([hE, mE, hS, mS, hDI, mDI, hDF, mDF].some(isNaN)) return false;

        let minEntrada = hE * 60 + (mE || 0);
        let minSalida = hS * 60 + (mS || 0);
        let minDescInicio = hDI * 60 + (mDI || 0);
        let minDescFin = hDF * 60 + (mDF || 0);

        if (minSalida <= minEntrada) {
            minSalida += 24 * 60;
            if (minDescInicio < minEntrada) {
                minDescInicio += 24 * 60;
                minDescFin += 24 * 60;
            }
            if (minDescFin <= minDescInicio) {
                minDescFin += 24 * 60;
            }
        }

        if (minDescFin <= minDescInicio) {
            return false;
        }

        return minDescInicio >= minEntrada && minDescFin <= minSalida;
    }

    function actualizarVisibilidadDescanso(card) {
        const chkDescanso = card.querySelector('.chk-tiene-descanso');
        const contenedorRango = card.querySelector('.contenedor-rango-descanso');
        const textoSinDescanso = card.querySelector('.texto-sin-descanso');
        if (!chkDescanso || !contenedorRango || !textoSinDescanso) return;

        if (chkDescanso.checked) {
            contenedorRango.classList.remove('hidden');
            textoSinDescanso.classList.add('hidden');
        } else {
            contenedorRango.classList.add('hidden');
            textoSinDescanso.classList.remove('hidden');
        }
    }

    function recalcularResumenHoras() {
        const empId = selectEmpleadoSemana ? selectEmpleadoSemana.value : 0;
        const limites = limitesPorEmpleado[empId] || { horas_max_dia: 8, horas_max_semana: 48 };
        const maxDia = parseFloat(limites.horas_max_dia) || 8;
        const maxSemana = parseFloat(limites.horas_max_semana) || 48;

        document.getElementById('leyenda_tope_semana').textContent = `${maxSemana} hs`;

        let totalSemana = 0;
        let diasActivos = 0;
        let hayExcesoDiario = false;
        let hayDescansoInvalido = false;
        let erroresLimite = [];

        document.querySelectorAll('.tarjeta-dia-personal').forEach(function (card) {
            const trabaja = card.querySelector('.chk-trabaja-dia').checked;
            const entrada = card.querySelector('.input-entrada-dia').value;
            const salida = card.querySelector('.input-salida-dia').value;
            const tieneDescanso = card.querySelector('.chk-tiene-descanso')?.checked;
            const descInicio = card.querySelector('.input-descanso-inicio')?.value || '';
            const descFin = card.querySelector('.input-descanso-fin')?.value || '';
            const labelDia = card.querySelector('.valor-horas-dia');

            if (trabaja) {
                const hs = calcularHorasEntre(entrada, salida);
                const hsRedondeada = Math.round(hs * 10) / 10;
                totalSemana += hs;
                diasActivos++;

                let errorEnDia = false;

                if (hsRedondeada > maxDia) {
                    errorEnDia = true;
                    hayExcesoDiario = true;
                    labelDia.textContent = `${hsRedondeada} hs (Supera máx. de ${maxDia} hs)`;
                    labelDia.className = 'valor-horas-dia text-red-700 font-bold';
                }

                if (tieneDescanso) {
                    const descansoValido = validarDescansoEnHorario(entrada, salida, descInicio, descFin);
                    if (!descansoValido) {
                        errorEnDia = true;
                        hayDescansoInvalido = true;
                        labelDia.textContent = `${hsRedondeada} hs (Descanso fuera de horario)`;
                        labelDia.className = 'valor-horas-dia text-red-700 font-bold';
                    }
                }

                if (!errorEnDia) {
                    labelDia.textContent = formatearHoras(hs);
                    labelDia.className = 'valor-horas-dia text-green-900';
                }
            } else {
                labelDia.textContent = '0 hs (No trabaja)';
                labelDia.className = 'valor-horas-dia text-red-700';
            }
        });

        if (hayDescansoInvalido) {
            erroresLimite.push('El horario de trabajo y descanso no concuerda en uno o varios días.');
        }

        if (hayExcesoDiario) {
            erroresLimite.push(`Uno o varios días superan el límite diario (${maxDia} hs).`);
        }

        const totalSemanaRedondeado = Math.round(totalSemana * 10) / 10;
        if (totalSemanaRedondeado > maxSemana) {
            erroresLimite.push(`El total semanal (${totalSemanaRedondeado} hs) supera el límite (${maxSemana} hs).`);
        }

        const promedioDia = diasActivos > 0 ? (totalSemana / diasActivos) : 0;
        const totalMes = totalSemana * 4;

        document.getElementById('leyenda_horas_dia').textContent = formatearHoras(promedioDia);
        document.getElementById('leyenda_horas_semana').textContent = formatearHoras(totalSemana);
        document.getElementById('leyenda_horas_mes').textContent = formatearHoras(totalMes);

        if (erroresLimite.length > 0) {
            alertaLimiteHoras.textContent = 'No se puede guardar: ' + erroresLimite.join(' ');
            alertaLimiteHoras.classList.remove('hidden');
            btnSubmitHorariosSemana.disabled = true;
            btnSubmitHorariosSemana.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            alertaLimiteHoras.classList.add('hidden');
            btnSubmitHorariosSemana.disabled = false;
            btnSubmitHorariosSemana.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function abrirModalHorarios() {
        modalHorarios.classList.remove('hidden');
        modalHorarios.classList.add('flex');
        recalcularResumenHoras();
    }

    function cerrarModalHorarios() {
        modalHorarios.classList.add('hidden');
        modalHorarios.classList.remove('flex');
    }

    btnAbrirModalHorarios.addEventListener('click', abrirModalHorarios);
    btnCerrarModalHorarios.addEventListener('click', cerrarModalHorarios);
    btnCancelarModalHorarios.addEventListener('click', cerrarModalHorarios);

    modalHorarios.addEventListener('click', function (e) {
        if (e.target === modalHorarios) cerrarModalHorarios();
    });

    function actualizarEstiloTarjetaDia(card, trabaja) {
        const badge = card.querySelector('.badge-estado-dia');
        const inputsContainer = card.querySelector('.contenedor-inputs-dia');

        if (trabaja) {
            card.className = 'tarjeta-dia-personal rounded-xl border p-2.5 transition border-green-500 bg-green-50/50';
            badge.className = 'badge-estado-dia rounded-full px-2 py-0.5 text-[10px] font-semibold bg-green-700 text-white';
            badge.textContent = 'Trabaja';
            inputsContainer.classList.remove('opacity-45', 'opacity-40', 'pointer-events-none');
        } else {
            card.className = 'tarjeta-dia-personal rounded-xl border p-2.5 transition border-red-300 bg-red-50/40';
            badge.className = 'badge-estado-dia rounded-full px-2 py-0.5 text-[10px] font-semibold border border-red-300 bg-red-100 text-red-800';
            badge.textContent = 'No trabaja';
            inputsContainer.classList.add('opacity-45', 'pointer-events-none');
        }
        recalcularResumenHoras();
    }

    document.querySelectorAll('.chk-trabaja-dia').forEach(function (chk) {
        chk.addEventListener('change', function () {
            const card = this.closest('.tarjeta-dia-personal');
            actualizarEstiloTarjetaDia(card, this.checked);
        });
    });

    document.querySelectorAll('.input-entrada-dia, .input-salida-dia, .input-descanso-inicio, .input-descanso-fin').forEach(function (inp) {
        inp.addEventListener('input', recalcularResumenHoras);
    });

    document.querySelectorAll('.chk-tiene-descanso').forEach(function (chk) {
        chk.addEventListener('change', function () {
            const card = this.closest('.tarjeta-dia-personal');
            actualizarVisibilidadDescanso(card);
            recalcularResumenHoras();
        });
    });

    if (selectEmpleadoSemana) {
        selectEmpleadoSemana.addEventListener('change', function () {
            const empId = this.value;
            const datosEmpleado = agendaPorEmpleado[empId] || {};

            document.querySelectorAll('.tarjeta-dia-personal').forEach(function (card) {
                const fecha = card.dataset.diaCard;
                const infoDia = datosEmpleado[fecha] || {
                    trabaja: false,
                    horario_entrada: '06:00',
                    horario_salida: '14:00',
                    tiene_descanso: false,
                    descanso_inicio: '10:00',
                    descanso_fin: '10:30',
                };

                const chk = card.querySelector('.chk-trabaja-dia');
                const inputEntrada = card.querySelector('.input-entrada-dia');
                const inputSalida = card.querySelector('.input-salida-dia');
                const chkDescanso = card.querySelector('.chk-tiene-descanso');
                const inputDescInicio = card.querySelector('.input-descanso-inicio');
                const inputDescFin = card.querySelector('.input-descanso-fin');

                chk.checked = Boolean(infoDia.trabaja);
                inputEntrada.value = infoDia.horario_entrada || '06:00';
                inputSalida.value = infoDia.horario_salida || '14:00';
                if (chkDescanso) chkDescanso.checked = Boolean(infoDia.tiene_descanso);
                if (inputDescInicio) inputDescInicio.value = infoDia.descanso_inicio || '10:00';
                if (inputDescFin) inputDescFin.value = infoDia.descanso_fin || '10:30';

                actualizarVisibilidadDescanso(card);
                actualizarEstiloTarjetaDia(card, chk.checked);
            });
        });
    }

    recalcularResumenHoras();

    const alertaTurnoExito = document.getElementById('alerta_turno_exito');
    if (alertaTurnoExito) {
        setTimeout(() => alertaTurnoExito.classList.add('hidden'), 3000);
    }

    // --- Modal de Personal (Operarios y Administrativos con Límite de Horas) ---
    const modalPersonal = document.getElementById('modal_personal');
    const btnAbrirModalPersonal = document.getElementById('btn_abrir_modal_personal');
    const btnCerrarModalPersonal = document.getElementById('btn_cerrar_modal_personal');
    const btnCancelarPersonal = document.getElementById('btn_cancelar_personal');
    const btnVolverListaPersonal = document.getElementById('btn_volver_lista_personal');
    const tabPersonalForm = document.getElementById('tab_personal_form');
    const tabPersonalLista = document.getElementById('tab_personal_lista');
    const vistaPersonalForm = document.getElementById('vista_personal_form');
    const vistaPersonalLista = document.getElementById('vista_personal_lista');
    const formPersonal = document.getElementById('form_personal');
    const inputPersonalId = document.getElementById('personal_id_edicion');
    const inputPersonalNombre = document.getElementById('personal_nombre');
    const selectPersonalRol = document.getElementById('personal_rol');
    const inputPersonalDni = document.getElementById('personal_dni');
    const inputPersonalTelefono = document.getElementById('personal_telefono');
    const inputPersonalHorasDia = document.getElementById('personal_horas_dia');
    const inputPersonalHorasSemana = document.getElementById('personal_horas_semana');
    const errorModalPersonal = document.getElementById('error_modal_personal');
    const tablaModalPersonal = document.getElementById('tabla_modal_personal');
    const contadorPersonalModal = document.getElementById('contador_personal_modal');
    const notificacionPersonal = document.getElementById('notificacion_personal');
    const btnSubmitPersonal = document.getElementById('btn_submit_personal');

    function soloDigitos(e) {
        e.target.value = e.target.value.replace(/\D+/g, '');
    }
    inputPersonalDni.addEventListener('input', soloDigitos);
    inputPersonalTelefono.addEventListener('input', soloDigitos);

    function mostrarAvisoPersonal(mensaje) {
        notificacionPersonal.textContent = mensaje;
        notificacionPersonal.className = 'mb-4 rounded-lg border border-green-400 bg-green-50 p-3 text-sm font-medium text-green-900';
        setTimeout(() => notificacionPersonal.classList.add('hidden'), 3000);
    }

    function resetearFormPersonal() {
        formPersonal.reset();
        inputPersonalId.value = '';
        selectPersonalRol.value = 'operario';
        inputPersonalHorasDia.value = '8';
        inputPersonalHorasSemana.value = '48';
        tabPersonalForm.textContent = 'Agregar Empleado';
        btnSubmitPersonal.textContent = 'Guardar Empleado';
        btnVolverListaPersonal.classList.add('hidden');
        errorModalPersonal.classList.add('hidden');
    }

    function mostrarTabFormPersonal(reset = true) {
        if (reset) resetearFormPersonal();
        vistaPersonalForm.classList.remove('hidden');
        vistaPersonalLista.classList.add('hidden');
        tabPersonalForm.className = 'cursor-pointer rounded-lg bg-green-800 px-3.5 py-1.5 text-xs font-semibold text-white transition';
        tabPersonalLista.className = 'cursor-pointer rounded-lg border border-gray-300 bg-gray-100 px-3.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200';
    }

    function mostrarTabListaPersonal() {
        resetearFormPersonal();
        vistaPersonalForm.classList.add('hidden');
        vistaPersonalLista.classList.remove('hidden');
        tabPersonalLista.className = 'cursor-pointer rounded-lg bg-green-800 px-3.5 py-1.5 text-xs font-semibold text-white transition';
        tabPersonalForm.className = 'cursor-pointer rounded-lg border border-gray-300 bg-gray-100 px-3.5 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200';
    }

    btnAbrirModalPersonal.addEventListener('click', () => {
        modalPersonal.classList.remove('hidden');
        modalPersonal.classList.add('flex');
        mostrarTabFormPersonal(true);
    });

    function cerrarModalPersonal() {
        modalPersonal.classList.add('hidden');
        modalPersonal.classList.remove('flex');
        resetearFormPersonal();
    }

    btnCerrarModalPersonal.addEventListener('click', cerrarModalPersonal);
    btnCancelarPersonal.addEventListener('click', cerrarModalPersonal);
    tabPersonalForm.addEventListener('click', () => mostrarTabFormPersonal(true));
    tabPersonalLista.addEventListener('click', mostrarTabListaPersonal);
    btnVolverListaPersonal.addEventListener('click', mostrarTabListaPersonal);

    tablaModalPersonal.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-editar-personal');
        if (!btn) return;
        const fila = btn.closest('tr');
        inputPersonalId.value = fila.dataset.personalRow;
        inputPersonalNombre.value = fila.dataset.nombre || '';
        selectPersonalRol.value = fila.dataset.rol || 'operario';
        inputPersonalDni.value = fila.dataset.dni || '';
        inputPersonalTelefono.value = fila.dataset.telefono || '';
        inputPersonalHorasDia.value = fila.dataset.horasDia || '8';
        inputPersonalHorasSemana.value = fila.dataset.horasSemana || '48';
        tabPersonalForm.textContent = 'Editando Empleado';
        btnSubmitPersonal.textContent = 'Guardar Cambios';
        btnVolverListaPersonal.classList.remove('hidden');
        mostrarTabFormPersonal(false);
    });

    formPersonal.addEventListener('submit', async function (e) {
        e.preventDefault();
        errorModalPersonal.classList.add('hidden');
        btnSubmitPersonal.disabled = true;

        const idEdicion = inputPersonalId.value;
        const esEdicion = Boolean(idEdicion);
        const url = esEdicion ? `{{ url('/personal/empleados') }}/${idEdicion}` : `{{ route('personal.empleados.store') }}`;
        const formData = new FormData(formPersonal);
        if (esEdicion) formData.append('_method', 'PUT');

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: formData,
            });
            const data = await response.json();

            if (!response.ok) {
                errorModalPersonal.textContent = data?.errors ? Object.values(data.errors)[0][0] : (data?.message || 'Error al guardar.');
                errorModalPersonal.classList.remove('hidden');
                return;
            }

            const rolEtiqueta = data.rol === 'administracion' ? 'Administrativo' : 'Operario';
            const textoBaseSelect = `${data.name} — ${rolEtiqueta}` + (data.dni ? ` (DNI: ${data.dni})` : '');
            limitesPorEmpleado[data.id] = {
                horas_max_dia: data.horas_max_dia,
                horas_max_semana: data.horas_max_semana,
            };
            catalogoEmpleados[data.id] = {
                id: data.id,
                name: data.name,
                rol: data.rol,
                dni: data.dni || '',
                telefono: data.telefono || '',
            };
            renderizarOpcionesBuscador(textoBusquedaActivo);

            if (esEdicion) {
                const fila = tablaModalPersonal.querySelector(`tr[data-personal-row="${data.id}"]`);
                if (fila) {
                    fila.dataset.nombre = data.name;
                    fila.dataset.rol = data.rol;
                    fila.dataset.dni = data.dni || '';
                    fila.dataset.telefono = data.telefono || '';
                    fila.dataset.horasDia = data.horas_max_dia;
                    fila.dataset.horasSemana = data.horas_max_semana;
                    fila.querySelector('.col-nombre').textContent = data.name;
                    fila.querySelector('.col-rol').textContent = rolEtiqueta;
                    fila.querySelector('.col-limites').textContent = `${data.horas_max_dia} hs/día · ${data.horas_max_semana} hs/sem`;
                    fila.querySelector('.col-contacto').innerHTML = `DNI: ${data.dni || '—'}<br>Tel: ${data.telefono || '—'}`;
                }
                const opt = selectEmpleadoSemana.querySelector(`option[value="${data.id}"]`);
                if (opt) {
                    const sinHorarioOpt = opt.dataset.sinHorario === '1';
                    opt.textContent = textoBaseSelect + (sinHorarioOpt ? ' — Sin horarios cargados' : '');
                }
                recalcularResumenHoras();
                mostrarAvisoPersonal(data.message || 'Personal actualizado.');
                mostrarTabListaPersonal();
            } else {
                const nuevaOpt = document.createElement('option');
                nuevaOpt.value = data.id;
                nuevaOpt.dataset.sinHorario = '1';
                nuevaOpt.textContent = `${textoBaseSelect} — Sin horarios cargados`;
                nuevaOpt.selected = true;
                selectEmpleadoSemana.appendChild(nuevaOpt);

                agendaPorEmpleado[data.id] = {};
                selectEmpleadoSemana.dispatchEvent(new Event('change'));

                const nuevaFila = document.createElement('tr');
                nuevaFila.dataset.personalRow = data.id;
                nuevaFila.dataset.nombre = data.name;
                nuevaFila.dataset.rol = data.rol;
                nuevaFila.dataset.dni = data.dni || '';
                nuevaFila.dataset.telefono = data.telefono || '';
                nuevaFila.dataset.horasDia = data.horas_max_dia;
                nuevaFila.dataset.horasSemana = data.horas_max_semana;
                nuevaFila.className = 'hover:bg-gray-50';
                nuevaFila.innerHTML = `
                    <td class="p-3 font-medium text-gray-900 col-nombre">${data.name}</td>
                    <td class="p-3 col-rol">${rolEtiqueta}</td>
                    <td class="p-3 text-xs font-semibold text-green-900 col-limites">${data.horas_max_dia} hs/día · ${data.horas_max_semana} hs/sem</td>
                    <td class="p-3 text-xs col-contacto">DNI: ${data.dni || '—'}<br>Tel: ${data.telefono || '—'}</td>
                    <td class="p-3 text-right">
                        <button type="button" class="btn-editar-personal cursor-pointer rounded-md border border-amber-400 bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-200">
                            Editar
                        </button>
                    </td>
                `;
                tablaModalPersonal.appendChild(nuevaFila);
                contadorPersonalModal.textContent = String(tablaModalPersonal.querySelectorAll('tr[data-personal-row]').length);
                mostrarAvisoPersonal(data.message || 'Personal registrado.');
                cerrarModalPersonal();
            }
        } catch (err) {
            errorModalPersonal.textContent = 'Error de conexión.';
            errorModalPersonal.classList.remove('hidden');
        } finally {
            btnSubmitPersonal.disabled = false;
        }
    });
</script>
@endsection
