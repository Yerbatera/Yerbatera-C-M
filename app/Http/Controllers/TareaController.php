<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TareaController extends Controller
{
    public function create(): View
    {
        $empleados = User::query()
            ->where('rol', 'operario')
            ->orderBy('name')
            ->get(['id', 'name']);

        $turnosProgramados = Turno::query()
            ->with('user:id,name')
            ->orderByDesc('fecha')
            ->limit(30)
            ->get();

        $tareas = Tarea::query()
            ->with('operario:id,name')
            ->orderByDesc('fecha_asignacion')
            ->paginate(15);

        return view('tareas.asignar', [
            'empleados' => $empleados,
            'turnosProgramados' => $turnosProgramados,
            'tareas' => $tareas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'empleado' => ['required', 'string', 'max:255'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'fecha_asignacion' => ['required', 'date_format:Y-m-d'],
            'relevancia' => ['nullable', Rule::in(['Alta', 'Media', 'Baja'])],
            'repetir_semanal' => ['nullable', 'boolean'],
            'semanas_repeticion' => ['nullable', 'integer', 'min:1', 'max:12'],
            'meses_repeticion' => ['nullable', 'integer', 'min:1', 'max:12'],
            'origen' => ['nullable', 'string'],
        ]);

        $nombreNormalizado = mb_strtolower(trim($validated['empleado']));
        $empleadosCoincidentes = User::query()
            ->where('rol', 'operario')
            ->get(['id', 'name'])
            ->filter(fn (User $empleado): bool => mb_strtolower(trim($empleado->name)) === $nombreNormalizado);

        if ($empleadosCoincidentes->isEmpty()) {
            return back()
                ->withErrors(['empleado' => 'No hay un empleado registrado con ese nombre'])
                ->withInput();
        }

        if ($empleadosCoincidentes->count() > 1) {
            return back()
                ->withErrors(['empleado' => 'Hay más de un empleado con ese nombre; diferenciá cuál querés asignar.'])
                ->withInput();
        }

        $empleado = $empleadosCoincidentes->first();

        $tieneTurno = Turno::query()
            ->where('user_id', $empleado->id)
            ->whereDate('fecha', $validated['fecha_asignacion'])
            ->exists();

        $relevancia = $validated['relevancia'] ?? 'Media';
        $repetirSemanal = (bool) ($validated['repetir_semanal'] ?? false);
        $semanas = $repetirSemanal
            ? (isset($validated['meses_repeticion']) ? ((int) $validated['meses_repeticion']) * 4 : (int) ($validated['semanas_repeticion'] ?? 4))
            : 1;
        $fechaBase = Carbon::parse($validated['fecha_asignacion']);

        for ($i = 0; $i < $semanas; $i++) {
            $fechaTarea = $fechaBase->copy()->addWeeks($i)->toDateString();

            $tarea = new Tarea;
            $tarea->nombre = $validated['nombre'];
            $tarea->id_operario = $empleado->id;
            $tarea->id_asignador = $request->user()->id;
            $tarea->fecha_asignacion = $fechaTarea.' 00:00:00';
            $tarea->estado = 'Pendiente';
            $tarea->relevancia = $relevancia;
            $tarea->es_recurrente = $repetirSemanal;
            $tarea->descripcion = $validated['descripcion'] ?? null;
            $tarea->save();
        }

        $rutaDestino = ($validated['origen'] ?? null) === 'monitoreo'
            ? route('monitoreo', ['dia' => $validated['fecha_asignacion']])
            : route('tareas.asignar');

        $redirect = redirect($rutaDestino)
            ->with('success', 'La tarea se asignó correctamente.');

        if (! $tieneTurno) {
            $redirect->with(
                'warning',
                'El empleado no tiene un turno asignado para esa fecha. La tarea se guardó igualmente.'
            );
        }

        return $redirect;
    }

    public function actualizarEstado(Request $request, Tarea $tarea): RedirectResponse
    {
        $estadoActual = mb_strtolower((string) $tarea->estado);
        $esCompletada = in_array($estadoActual, ['completada', 'completado', 'finalizada', 'finalizado'], true);

        $tarea->estado = $esCompletada ? 'Pendiente' : 'Completada';
        $tarea->save();

        return back()->with('success', "Estado de tarea actualizado a {$tarea->estado}.");
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'estado' => ['nullable', 'in:pendiente,completada'],
            'operario' => ['nullable', 'integer', 'exists:users,id'],
            'dia' => ['nullable', 'date_format:Y-m-d'],
            'semana' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $hasSelectedDay = isset($filters['dia'])
            || (! array_key_exists('dia', $request->query()) && ! array_key_exists('semana', $request->query()));
        $selectedDate = Carbon::parse($filters['dia'] ?? now()->toDateString());
        $weekStart = Carbon::parse($filters['semana'] ?? $selectedDate->toDateString())
            ->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(5);

        $query = Tarea::query();

        if (($filters['estado'] ?? null) === 'pendiente') {
            $query->whereRaw('LOWER(estado) = ?', ['pendiente']);
        }

        if (($filters['estado'] ?? null) === 'completada') {
            $query->whereRaw('LOWER(estado) IN (?, ?, ?, ?)', [
                'completada',
                'completado',
                'finalizada',
                'finalizado',
            ]);
        }

        if (isset($filters['operario'])) {
            $query->where('id_operario', $filters['operario']);
        }

        $dailyCounts = (clone $query)
            ->selectRaw('DATE(fecha_asignacion) as dia, COUNT(*) as cantidad')
            ->whereDate('fecha_asignacion', '>=', $weekStart->toDateString())
            ->whereDate('fecha_asignacion', '<=', $weekEnd->toDateString())
            ->groupByRaw('DATE(fecha_asignacion)')
            ->pluck('cantidad', 'dia');
        $totalTaskCount = (clone $query)->count();

        $tareasQuery = (clone $query)->with('operario')->latest();

        if ($hasSelectedDay) {
            $tareasQuery->whereDate('fecha_asignacion', $selectedDate->toDateString());
        }

        $tareas = $tareasQuery->paginate(15)->withQueryString();

        $dayNames = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $fullDayNames = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $weekDays = [];

        foreach ($dayNames as $offset => $dayName) {
            $date = $weekStart->copy()->addDays($offset);
            $weekDays[] = [
                'date' => $date,
                'label' => $dayName,
                'full_label' => $fullDayNames[$offset],
                'count' => (int) $dailyCounts->get($date->toDateString(), 0),
            ];
        }

        $preservedQuery = $request->query();
        unset($preservedQuery['page']);

        $allDaysQuery = $preservedQuery;
        unset($allDaysQuery['dia']);
        $allDaysQuery['semana'] = $weekStart->toDateString();

        $previousWeekQuery = $preservedQuery;
        $previousWeekQuery['semana'] = $weekStart->copy()->subWeek()->toDateString();

        $nextWeekQuery = $preservedQuery;
        $nextWeekQuery['semana'] = $weekStart->copy()->addWeek()->toDateString();

        if ($hasSelectedDay) {
            $previousWeekQuery['dia'] = $selectedDate->copy()->subWeek()->toDateString();
            $nextWeekQuery['dia'] = $selectedDate->copy()->addWeek()->toDateString();
        } else {
            unset($previousWeekQuery['dia'], $nextWeekQuery['dia']);
        }

        $empleados = User::query()
            ->where('rol', 'operario')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Mapa de turnos de la semana actual (Lunes a Sábado) para el modal y la tabla
        $turnosSemana = Turno::query()
            ->whereBetween('fecha', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $turnosMapa = $turnosSemana->keyBy(fn (Turno $t): string => $t->user_id.'_'.$t->fecha->toDateString());

        $horariosSemanalesModal = [];
        $operariosSinHorario = [];
        foreach ($empleados as $empleadoItem) {
            $diasEmpleado = [];
            $tieneAlgunDia = false;
            foreach ($weekDays as $wd) {
                $fechaIso = $wd['date']->toDateString();
                $turnoEncontrado = $turnosMapa->get($empleadoItem->id.'_'.$fechaIso);
                if ($turnoEncontrado) {
                    $tieneAlgunDia = true;
                }
                $diasEmpleado[] = [
                    'fecha' => $fechaIso,
                    'dia_corto' => $wd['label'],
                    'dia_nombre' => $wd['full_label'],
                    'fecha_corta' => $wd['date']->format('d/m'),
                    'trabaja' => (bool) $turnoEncontrado,
                    'entrada' => $turnoEncontrado?->horario_entrada ?? '06:00',
                    'salida' => $turnoEncontrado?->horario_salida ?? '14:00',
                    'descanso' => $turnoEncontrado?->descanso ?? null,
                    'franja' => $turnoEncontrado?->franja_horaria ?? null,
                ];
            }
            $horariosSemanalesModal[$empleadoItem->name] = $diasEmpleado;
            if (! $tieneAlgunDia) {
                $operariosSinHorario[] = $empleadoItem->name;
            }
        }

        return view('dashboards.monitoreo', [
            'tareas' => $tareas,
            'empleados' => $empleados,
            'operariosSinHorario' => $operariosSinHorario,
            'turnosMapa' => $turnosMapa,
            'horariosSemanalesModal' => $horariosSemanalesModal,
            'weekStart' => $weekStart,
            'weekDays' => $weekDays,
            'selectedDate' => $selectedDate,
            'hasSelectedDay' => $hasSelectedDay,
            'totalTaskCount' => $totalTaskCount,
            'allDaysUrl' => route('monitoreo', $allDaysQuery),
            'previousWeekUrl' => route('monitoreo', $previousWeekQuery),
            'nextWeekUrl' => route('monitoreo', $nextWeekQuery),
        ]);
    }
}
