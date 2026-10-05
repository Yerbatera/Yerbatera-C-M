<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'estado' => ['nullable', 'in:pendiente,completada'],
            'operario' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $query = Tarea::query()->with('operario')->latest();

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

        $tareas = $query->paginate(15)->withQueryString();
        $operarios = User::query()
            ->whereIn('id', Tarea::query()->select('id_operario')->distinct())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('dashboards.monitoreo', compact('tareas', 'operarios'));
    }
}
