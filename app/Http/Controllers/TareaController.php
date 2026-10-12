<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TareaController extends Controller
{
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
        $weekDays = [];

        foreach ($dayNames as $offset => $dayName) {
            $date = $weekStart->copy()->addDays($offset);
            $weekDays[] = [
                'date' => $date,
                'label' => $dayName,
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

        return view('dashboards.monitoreo', [
            'tareas' => $tareas,
            'empleados' => $empleados,
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
