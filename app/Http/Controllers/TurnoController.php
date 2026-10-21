<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TurnoController extends Controller
{
    public function index(): View
    {
        $empleados = User::query()
            ->where('rol', 'operario')
            ->orderBy('name')
            ->get(['id', 'name']);

        $turnos = Turno::query()
            ->with('user:id,name')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15);

        return view('turnos.index', [
            'empleados' => $empleados,
            'turnos' => $turnos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'empleado' => ['required', 'string', 'max:255'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'turno' => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
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

        $turnoExistente = Turno::query()
            ->where('user_id', $empleado->id)
            ->whereDate('fecha', $validated['fecha'])
            ->exists();

        if ($turnoExistente) {
            return back()
                ->withErrors(['fecha' => 'Ese empleado ya tiene un turno asignado para ese día.'])
                ->withInput();
        }

        Turno::query()->create([
            'user_id' => $empleado->id,
            'fecha' => $validated['fecha'],
            'turno' => $validated['turno'],
        ]);

        return redirect()
            ->route('turnos.index')
            ->with('success', 'El turno se registró correctamente.');
    }
}
