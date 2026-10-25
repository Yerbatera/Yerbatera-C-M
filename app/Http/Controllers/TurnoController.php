<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TurnoController extends Controller
{
    public function index(Request $request): View
    {
        $weekStart = Carbon::parse($request->query('semana', now()->toDateString()))
            ->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(5);

        $empleados = User::query()
            ->leftJoin('contactos', 'users.id_contacto', '=', 'contactos.id')
            ->leftJoin('contactoxidentificador', 'contactos.id', '=', 'contactoxidentificador.id_contacto')
            ->where('users.rol', 'operario')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.rol',
                'users.estado',
                'users.horas_max_dia',
                'users.horas_max_semana',
                'contactos.telefono',
                'contactoxidentificador.valor as dni'
            )
            ->orderBy('users.name')
            ->get();

        $personalHorario = User::query()
            ->leftJoin('contactos', 'users.id_contacto', '=', 'contactos.id')
            ->leftJoin('contactoxidentificador', 'contactos.id', '=', 'contactoxidentificador.id_contacto')
            ->whereIn('users.rol', ['operario', 'administracion'])
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.rol',
                'users.estado',
                'users.horas_max_dia',
                'users.horas_max_semana',
                'contactos.telefono',
                'contactoxidentificador.valor as dni'
            )
            ->orderBy('users.rol')
            ->orderBy('users.name')
            ->get();

        $nombresDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $diasSemana = [];
        foreach ($nombresDias as $offset => $nombreDia) {
            $fechaDia = $weekStart->copy()->addDays($offset);
            $diasSemana[] = [
                'offset' => $offset,
                'nombre' => $nombreDia,
                'fecha' => $fechaDia->toDateString(),
                'fecha_corta' => $fechaDia->format('d/m'),
            ];
        }

        $turnosSemana = Turno::query()
            ->whereBetween('fecha', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $agendaPorEmpleado = [];
        $limitesPorEmpleado = [];
        $catalogoEmpleados = [];
        $conteoDiasConHorario = [];
        $horasSemanalesPorEmpleado = [];

        foreach ($personalHorario as $emp) {
            $agendaPorEmpleado[$emp->id] = [];
            $conteoDiasConHorario[$emp->id] = 0;
            $horasSemanalesPorEmpleado[$emp->id] = 0.0;
            $limitesPorEmpleado[$emp->id] = [
                'horas_max_dia' => (float) ($emp->horas_max_dia ?? 8),
                'horas_max_semana' => (float) ($emp->horas_max_semana ?? 48),
            ];
            $catalogoEmpleados[$emp->id] = [
                'id' => $emp->id,
                'name' => $emp->name,
                'rol' => $emp->rol,
                'dni' => $emp->dni,
                'telefono' => $emp->telefono,
            ];

            foreach ($diasSemana as $dia) {
                $agendaPorEmpleado[$emp->id][$dia['fecha']] = [
                    'trabaja' => false,
                    'turno' => 'mañana',
                    'horario_entrada' => '06:00',
                    'horario_salida' => '14:00',
                    'tiene_descanso' => false,
                    'descanso_inicio' => '10:00',
                    'descanso_fin' => '10:30',
                    'descanso' => '',
                    'horas_dia' => 0.0,
                ];
            }
        }

        foreach ($turnosSemana as $turnoItem) {
            $fechaStr = $turnoItem->fecha->toDateString();
            if (isset($agendaPorEmpleado[$turnoItem->user_id][$fechaStr])) {
                $entrada = $turnoItem->horario_entrada ?? '06:00';
                $salida = $turnoItem->horario_salida ?? '14:00';
                $horasDia = $this->calcularHoras($entrada, $salida);
                $rangoDescanso = $this->extraerRangoDescanso($turnoItem->descanso);

                $agendaPorEmpleado[$turnoItem->user_id][$fechaStr] = [
                    'trabaja' => true,
                    'turno' => $turnoItem->turno ?? 'mañana',
                    'horario_entrada' => $entrada,
                    'horario_salida' => $salida,
                    'tiene_descanso' => $rangoDescanso['tiene_descanso'],
                    'descanso_inicio' => $rangoDescanso['inicio'],
                    'descanso_fin' => $rangoDescanso['fin'],
                    'descanso' => $turnoItem->descanso ?? '',
                    'horas_dia' => $horasDia,
                ];
                $conteoDiasConHorario[$turnoItem->user_id] = ($conteoDiasConHorario[$turnoItem->user_id] ?? 0) + 1;
                $horasSemanalesPorEmpleado[$turnoItem->user_id] = round(($horasSemanalesPorEmpleado[$turnoItem->user_id] ?? 0.0) + $horasDia, 1);
            }
        }

        $empleadosSinHorario = $personalHorario
            ->filter(fn ($persona): bool => ($conteoDiasConHorario[$persona->id] ?? 0) === 0)
            ->values();

        return view('turnos.index', [
            'empleados' => $empleados,
            'personalHorario' => $personalHorario,
            'empleadosSinHorario' => $empleadosSinHorario,
            'limitesPorEmpleado' => $limitesPorEmpleado,
            'catalogoEmpleados' => $catalogoEmpleados,
            'horasSemanalesPorEmpleado' => $horasSemanalesPorEmpleado,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'diasSemana' => $diasSemana,
            'agendaPorEmpleado' => $agendaPorEmpleado,
            'semanaAnteriorUrl' => route('turnos.index', ['semana' => $weekStart->copy()->subWeek()->toDateString()]),
            'semanaActualUrl' => route('turnos.index', ['semana' => now()->startOfWeek(Carbon::MONDAY)->toDateString()]),
            'semanaSiguienteUrl' => route('turnos.index', ['semana' => $weekStart->copy()->addWeek()->toDateString()]),
        ]);
    }

    public function storeSemanal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'empleado_id' => ['required', 'integer', 'exists:users,id'],
            'semana' => ['required', 'date_format:Y-m-d'],
            'dias' => ['required', 'array'],
            'repetir_mensual' => ['nullable', 'boolean'],
            'meses_repeticion' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $empleado = User::query()
            ->where('id', $validated['empleado_id'])
            ->whereIn('rol', ['operario', 'administracion'])
            ->firstOrFail();

        $maxDia = (float) ($empleado->horas_max_dia ?? 8);
        $maxSemana = (float) ($empleado->horas_max_semana ?? 48);
        $totalHorasSemana = 0.0;

        foreach ($validated['dias'] as $datosDia) {
            if (! empty($datosDia['trabaja'])) {
                $entrada = ! empty($datosDia['entrada']) ? trim((string) $datosDia['entrada']) : '06:00';
                $salida = ! empty($datosDia['salida']) ? trim((string) $datosDia['salida']) : '14:00';
                $horasDia = $this->calcularHoras($entrada, $salida);

                if ($horasDia > $maxDia) {
                    return redirect()
                        ->route('turnos.index', [
                            'semana' => $validated['semana'],
                            'empleado_id' => $empleado->id,
                            'abrir_horarios' => 1,
                        ])
                        ->with('error', "No se pudo guardar: un día tiene {$horasDia} hs y supera el límite diario de {$empleado->name} ({$maxDia} hs/día).");
                }

                $tieneDescanso = ! empty($datosDia['tiene_descanso']);
                if ($tieneDescanso) {
                    $descInicio = ! empty($datosDia['descanso_inicio']) ? trim((string) $datosDia['descanso_inicio']) : '';
                    $descFin = ! empty($datosDia['descanso_fin']) ? trim((string) $datosDia['descanso_fin']) : '';

                    if ($descInicio === '' || $descFin === '' || ! $this->validarDescansoDentroDeHorario($entrada, $salida, $descInicio, $descFin)) {
                        return redirect()
                            ->route('turnos.index', [
                                'semana' => $validated['semana'],
                                'empleado_id' => $empleado->id,
                                'abrir_horarios' => 1,
                            ])
                            ->with('error', 'No se pudo guardar: el horario de trabajo y descanso no concuerda en uno o varios días.');
                    }
                }

                $totalHorasSemana += $horasDia;
            }
        }

        if ($totalHorasSemana > $maxSemana) {
            return redirect()
                ->route('turnos.index', [
                    'semana' => $validated['semana'],
                    'empleado_id' => $empleado->id,
                    'abrir_horarios' => 1,
                ])
                ->with('error', "No se pudo guardar: el total semanal ({$totalHorasSemana} hs) supera el límite semanal de {$empleado->name} ({$maxSemana} hs/semana).");
        }

        $repetirMensual = (bool) ($validated['repetir_mensual'] ?? false);
        $meses = $repetirMensual ? (int) ($validated['meses_repeticion'] ?? 1) : 0;
        $semanasTotales = $repetirMensual ? ($meses * 4) : 1;

        DB::transaction(function () use ($empleado, $validated, $semanasTotales): void {
            foreach ($validated['dias'] as $fechaBaseStr => $datosDia) {
                $trabaja = ! empty($datosDia['trabaja']);
                $entrada = ! empty($datosDia['entrada']) ? trim((string) $datosDia['entrada']) : '06:00';
                $salida = ! empty($datosDia['salida']) ? trim((string) $datosDia['salida']) : '14:00';

                $tieneDescanso = ! empty($datosDia['tiene_descanso']);
                $descInicio = ! empty($datosDia['descanso_inicio']) ? trim((string) $datosDia['descanso_inicio']) : '';
                $descFin = ! empty($datosDia['descanso_fin']) ? trim((string) $datosDia['descanso_fin']) : '';
                $descanso = ($tieneDescanso && $descInicio !== '' && $descFin !== '')
                    ? "{$descInicio} a {$descFin}"
                    : null;

                $horaInt = (int) substr($entrada, 0, 2);
                $turnoNombre = $horaInt < 13 ? 'mañana' : ($horaInt < 20 ? 'tarde' : 'noche');
                $franjaTexto = "{$entrada} a {$salida}".($descanso ? " · Descanso: {$descanso}" : '');

                $fechaCarbon = Carbon::parse($fechaBaseStr);

                for ($i = 0; $i < $semanasTotales; $i++) {
                    $fechaObjetivo = $fechaCarbon->copy()->addWeeks($i)->toDateString();

                    if ($trabaja) {
                        Turno::query()->updateOrCreate(
                            [
                                'user_id' => $empleado->id,
                                'fecha' => $fechaObjetivo,
                            ],
                            [
                                'turno' => $turnoNombre,
                                'franja_horaria' => $franjaTexto,
                                'horario_entrada' => $entrada,
                                'horario_salida' => $salida,
                                'descanso' => $descanso,
                            ]
                        );
                    } else {
                        Turno::query()
                            ->where('user_id', $empleado->id)
                            ->whereDate('fecha', $fechaObjetivo)
                            ->delete();
                    }
                }
            }
        });

        return redirect()
            ->route('turnos.index', [
                'semana' => $validated['semana'],
                'empleado_id' => $empleado->id,
            ])
            ->with('success', "Horarios de {$empleado->name} guardados correctamente.");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'empleado' => ['required', 'string', 'max:255'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'turno' => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
            'horario_entrada' => ['nullable', 'date_format:H:i'],
            'horario_salida' => ['nullable', 'date_format:H:i'],
            'descanso' => ['nullable', 'string', 'max:100'],
            'repetir_semanal' => ['nullable', 'boolean'],
            'semanas_repeticion' => ['nullable', 'integer', 'min:1', 'max:12'],
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

        $franjasPorDefecto = [
            'mañana' => ['06:00', '14:00'],
            'tarde' => ['14:00', '22:00'],
            'noche' => ['22:00', '06:00'],
        ];

        $entrada = $validated['horario_entrada'] ?? $franjasPorDefecto[$validated['turno']][0];
        $salida = $validated['horario_salida'] ?? $franjasPorDefecto[$validated['turno']][1];
        $descanso = ! empty($validated['descanso']) ? trim($validated['descanso']) : null;
        $franjaHoraria = "{$entrada} a {$salida}".($descanso ? " · Descanso: {$descanso}" : '');

        $repetirSemanal = (bool) ($validated['repetir_semanal'] ?? false);
        $semanas = $repetirSemanal ? (int) ($validated['semanas_repeticion'] ?? 4) : 1;
        $fechaBase = Carbon::parse($validated['fecha']);

        for ($i = 0; $i < $semanas; $i++) {
            $fechaObjetivo = $fechaBase->copy()->addWeeks($i)->toDateString();

            $yaExiste = Turno::query()
                ->where('user_id', $empleado->id)
                ->whereDate('fecha', $fechaObjetivo)
                ->exists();

            if (! $yaExiste) {
                Turno::query()->create([
                    'user_id' => $empleado->id,
                    'fecha' => $fechaObjetivo,
                    'turno' => $validated['turno'],
                    'franja_horaria' => $franjaHoraria,
                    'horario_entrada' => $entrada,
                    'horario_salida' => $salida,
                    'descanso' => $descanso,
                ]);
            }
        }

        return redirect()
            ->route('turnos.index')
            ->with('success', 'El turno se registró correctamente.');
    }

    public function storeEmpleado(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'rol' => ['nullable', Rule::in(['operario', 'administracion'])],
            'dni' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'telefono' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'horas_max_dia' => ['required', 'numeric', 'min:1', 'max:24'],
            'horas_max_semana' => ['required', 'numeric', 'min:1', 'max:168'],
        ], [
            'dni.regex' => 'El DNI solo debe contener números.',
            'telefono.regex' => 'El teléfono solo debe contener números.',
        ]);

        $nombreLimpio = trim($validated['nombre']);
        $rolElegido = $validated['rol'] ?? 'operario';
        $horasMaxDia = (float) $validated['horas_max_dia'];
        $horasMaxSemana = (float) $validated['horas_max_semana'];

        $existeNombre = User::query()
            ->whereIn('rol', ['operario', 'administracion'])
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($nombreLimpio)])
            ->exists();

        if ($existeNombre) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Ya existe una persona en el personal con ese nombre.',
                ], 422);
            }

            return back()->withErrors(['nombre' => 'Ya existe una persona en el personal con ese nombre.']);
        }

        $ahora = Carbon::now();
        $dni = ! empty($validated['dni']) ? trim($validated['dni']) : null;
        $telefono = ! empty($validated['telefono']) ? trim($validated['telefono']) : null;

        $empleado = DB::transaction(function () use ($nombreLimpio, $rolElegido, $dni, $telefono, $horasMaxDia, $horasMaxSemana, $ahora): User {
            $contacto = Contacto::create([
                'nombre' => $nombreLimpio,
                'telefono' => $telefono,
            ]);

            $idRol = DB::table('roles')->where('nombre', $rolElegido)->value('id');
            if ($idRol) {
                DB::table('contactoxrol')->insert([
                    'id_rol' => $idRol,
                    'id_contacto' => $contacto->id,
                    'nombre' => $rolElegido,
                ]);
            }

            if ($dni) {
                $idIdentificadorDni = DB::table('identificadores')->where('nombre', 'DNI')->value('id');
                if (! $idIdentificadorDni) {
                    $idIdentificadorDni = DB::table('identificadores')->insertGetId([
                        'nombre' => 'DNI',
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                }

                DB::table('contactoxidentificador')->insert([
                    'id_identificador' => $idIdentificadorDni,
                    'id_contacto' => $contacto->id,
                    'valor' => $dni,
                ]);
            }

            $slug = Str::slug($nombreLimpio) ?: $rolElegido;
            $email = "{$slug}.{$contacto->id}@yerbatera.com";

            $idUser = DB::table('users')->insertGetId([
                'id_contacto' => $contacto->id,
                'name' => $nombreLimpio,
                'email' => $email,
                'password' => Hash::make('123456'),
                'rol' => $rolElegido,
                'estado' => 'Activo',
                'horas_max_dia' => $horasMaxDia,
                'horas_max_semana' => $horasMaxSemana,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            return User::findOrFail($idUser);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $empleado->id,
                'name' => $empleado->name,
                'rol' => $rolElegido,
                'dni' => $dni,
                'telefono' => $telefono,
                'horas_max_dia' => $horasMaxDia,
                'horas_max_semana' => $horasMaxSemana,
                'message' => 'Personal registrado.',
            ]);
        }

        return redirect()->route('turnos.index')->with('success', 'Personal registrado.');
    }

    public function updateEmpleado(Request $request, User $empleado): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'rol' => ['nullable', Rule::in(['operario', 'administracion'])],
            'dni' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'telefono' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'horas_max_dia' => ['required', 'numeric', 'min:1', 'max:24'],
            'horas_max_semana' => ['required', 'numeric', 'min:1', 'max:168'],
        ], [
            'dni.regex' => 'El DNI solo debe contener números.',
            'telefono.regex' => 'El teléfono solo debe contener números.',
        ]);

        $nombreLimpio = trim($validated['nombre']);
        $rolElegido = $validated['rol'] ?? $empleado->rol;
        $dni = ! empty($validated['dni']) ? trim($validated['dni']) : null;
        $telefono = ! empty($validated['telefono']) ? trim($validated['telefono']) : null;
        $horasMaxDia = (float) $validated['horas_max_dia'];
        $horasMaxSemana = (float) $validated['horas_max_semana'];
        $ahora = Carbon::now();

        DB::transaction(function () use ($empleado, $nombreLimpio, $rolElegido, $dni, $telefono, $horasMaxDia, $horasMaxSemana, $ahora): void {
            DB::table('users')
                ->where('id', $empleado->id)
                ->update([
                    'name' => $nombreLimpio,
                    'rol' => $rolElegido,
                    'horas_max_dia' => $horasMaxDia,
                    'horas_max_semana' => $horasMaxSemana,
                    'updated_at' => $ahora,
                ]);

            $idContacto = DB::table('users')->where('id', $empleado->id)->value('id_contacto');
            if (! $idContacto) {
                $contacto = Contacto::create([
                    'nombre' => $nombreLimpio,
                    'telefono' => $telefono,
                ]);
                $idContacto = $contacto->id;
                DB::table('users')->where('id', $empleado->id)->update(['id_contacto' => $idContacto]);
            } else {
                DB::table('contactos')->where('id', $idContacto)->update([
                    'nombre' => $nombreLimpio,
                    'telefono' => $telefono,
                    'updated_at' => $ahora,
                ]);
            }

            $idIdentificadorDni = DB::table('identificadores')->where('nombre', 'DNI')->value('id');
            if (! $idIdentificadorDni) {
                $idIdentificadorDni = DB::table('identificadores')->insertGetId([
                    'nombre' => 'DNI',
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }

            if ($dni) {
                $existeDni = DB::table('contactoxidentificador')
                    ->where('id_contacto', $idContacto)
                    ->where('id_identificador', $idIdentificadorDni)
                    ->exists();

                if ($existeDni) {
                    DB::table('contactoxidentificador')
                        ->where('id_contacto', $idContacto)
                        ->where('id_identificador', $idIdentificadorDni)
                        ->update(['valor' => $dni]);
                } else {
                    DB::table('contactoxidentificador')->insert([
                        'id_identificador' => $idIdentificadorDni,
                        'id_contacto' => $idContacto,
                        'valor' => $dni,
                    ]);
                }
            } else {
                DB::table('contactoxidentificador')
                    ->where('id_contacto', $idContacto)
                    ->where('id_identificador', $idIdentificadorDni)
                    ->delete();
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $empleado->id,
                'name' => $nombreLimpio,
                'rol' => $rolElegido,
                'dni' => $dni,
                'telefono' => $telefono,
                'horas_max_dia' => $horasMaxDia,
                'horas_max_semana' => $horasMaxSemana,
                'message' => 'Personal actualizado.',
            ]);
        }

        return redirect()->route('turnos.index')->with('success', 'Personal actualizado.');
    }

    private function calcularHoras(string $entrada, string $salida): float
    {
        [$hE, $mE] = array_pad(explode(':', $entrada), 2, '0');
        [$hS, $mS] = array_pad(explode(':', $salida), 2, '0');

        $minEntrada = ((int) $hE) * 60 + ((int) $mE);
        $minSalida = ((int) $hS) * 60 + ((int) $mS);

        if ($minSalida <= $minEntrada) {
            $minSalida += 24 * 60;
        }

        return round(($minSalida - $minEntrada) / 60, 1);
    }

    /**
     * @return array{tiene_descanso: bool, inicio: string, fin: string}
     */
    private function extraerRangoDescanso(?string $descanso): array
    {
        $texto = trim((string) $descanso);
        if ($texto === '') {
            return [
                'tiene_descanso' => false,
                'inicio' => '10:00',
                'fin' => '10:30',
            ];
        }

        if (preg_match('/(\d{2}:\d{2})\s*(?:a|-)\s*(\d{2}:\d{2})/', $texto, $matches)) {
            return [
                'tiene_descanso' => true,
                'inicio' => $matches[1],
                'fin' => $matches[2],
            ];
        }

        return [
            'tiene_descanso' => true,
            'inicio' => '10:00',
            'fin' => '10:30',
        ];
    }

    private function validarDescansoDentroDeHorario(string $entrada, string $salida, string $descInicio, string $descFin): bool
    {
        if (! preg_match('/^\d{2}:\d{2}$/', $entrada) || ! preg_match('/^\d{2}:\d{2}$/', $salida)) {
            return false;
        }
        if (! preg_match('/^\d{2}:\d{2}$/', $descInicio) || ! preg_match('/^\d{2}:\d{2}$/', $descFin)) {
            return false;
        }

        [$hE, $mE] = array_map('intval', explode(':', $entrada));
        [$hS, $mS] = array_map('intval', explode(':', $salida));
        [$hDI, $mDI] = array_map('intval', explode(':', $descInicio));
        [$hDF, $mDF] = array_map('intval', explode(':', $descFin));

        $minEntrada = $hE * 60 + $mE;
        $minSalida = $hS * 60 + $mS;
        $minDescInicio = $hDI * 60 + $mDI;
        $minDescFin = $hDF * 60 + $mDF;

        if ($minSalida <= $minEntrada) {
            $minSalida += 24 * 60;
            if ($minDescInicio < $minEntrada) {
                $minDescInicio += 24 * 60;
                $minDescFin += 24 * 60;
            }
            if ($minDescFin <= $minDescInicio) {
                $minDescFin += 24 * 60;
            }
        }

        if ($minDescFin <= $minDescInicio) {
            return false;
        }

        return $minDescInicio >= $minEntrada && $minDescFin <= $minSalida;
    }
}
