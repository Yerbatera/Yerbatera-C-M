<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatosEjemploSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $empleados = [
                ['name' => 'Juan Pérez', 'email' => 'juan.perez@ejemplo.test'],
                ['name' => 'Carlos Gómez', 'email' => 'carlos.gomez@ejemplo.test'],
                ['name' => 'Marcelo Duarte', 'email' => 'marcelo.duarte@ejemplo.test'],
                ['name' => 'Luis Benítez', 'email' => 'luis.benitez@ejemplo.test'],
            ];

            $idsEmpleados = [];

            foreach ($empleados as $empleado) {
                $usuarioExistente = DB::table('users')
                    ->where('email', $empleado['email'])
                    ->first();

                if ($usuarioExistente !== null) {
                    if ($usuarioExistente->name !== $empleado['name'] || $usuarioExistente->rol !== 'operario') {
                        throw new RuntimeException("El email {$empleado['email']} ya pertenece a un usuario distinto.");
                    }

                    $idsEmpleados[$empleado['email']] = (int) $usuarioExistente->id;

                    continue;
                }

                $idsEmpleados[$empleado['email']] = (int) DB::table('users')->insertGetId([
                    'name' => $empleado['name'],
                    'email' => $empleado['email'],
                    'password' => Hash::make('YerbateraEjemplo2026!'),
                    'rol' => 'operario',
                    'estado' => 'Activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $idAsignador = DB::table('users')
                ->where('rol', 'gerente')
                ->orderBy('id')
                ->value('id') ?? reset($idsEmpleados);

            $tareas = [
                [
                    'empleado' => 'juan.perez@ejemplo.test',
                    'nombre' => 'Secado de yerba',
                    'fecha' => '2026-10-12',
                    'hora' => '07:30:00',
                    'estado' => 'Completada',
                    'descripcion' => 'Se completó el secado de la tanda del lote 24 y se verificó el color de la hoja. (ejemplo)',
                ],
                [
                    'empleado' => 'carlos.gomez@ejemplo.test',
                    'nombre' => 'Control de humedad',
                    'fecha' => '2026-10-12',
                    'hora' => '10:00:00',
                    'estado' => 'Pendiente',
                    'descripcion' => 'Medir la humedad en tres puntos de la tanda y anotar los valores antes de autorizar la descarga. (ejemplo)',
                ],
                [
                    'empleado' => 'marcelo.duarte@ejemplo.test',
                    'nombre' => 'Carga de la cinta',
                    'fecha' => '2026-10-13',
                    'hora' => '08:15:00',
                    'estado' => 'Completada',
                    'descripcion' => 'Se distribuyó la hoja verde de manera pareja sobre la cinta para mantener constante el flujo al secadero. (ejemplo)',
                ],
                [
                    'empleado' => 'luis.benitez@ejemplo.test',
                    'nombre' => 'Encendido del horno',
                    'fecha' => '2026-10-13',
                    'hora' => '06:45:00',
                    'estado' => 'Pendiente',
                    'descripcion' => 'Encender el horno y estabilizar la temperatura según la indicación de la tanda antes de iniciar la alimentación. (ejemplo)',
                ],
                [
                    'empleado' => 'juan.perez@ejemplo.test',
                    'nombre' => 'Limpieza de zaranda',
                    'fecha' => '2026-10-14',
                    'hora' => '09:00:00',
                    'estado' => 'Completada',
                    'descripcion' => 'Se retiraron restos de palo y polvo de la zaranda, y se dejó libre la superficie de clasificación. (ejemplo)',
                ],
                [
                    'empleado' => 'carlos.gomez@ejemplo.test',
                    'nombre' => 'Acomodar estibas',
                    'fecha' => '2026-10-14',
                    'hora' => '13:30:00',
                    'estado' => 'Pendiente',
                    'descripcion' => 'Ordenar las bolsas de yerba estacionada sobre estibas secas y dejar despejado el pasillo de circulación. (ejemplo)',
                ],
                [
                    'empleado' => 'marcelo.duarte@ejemplo.test',
                    'nombre' => 'Empaquetado',
                    'fecha' => '2026-10-15',
                    'hora' => '10:00:00',
                    'estado' => 'Pendiente',
                    'descripcion' => 'Preparar paquetes de un kilogramo del lote aprobado y controlar el cierre de cada envase. (ejemplo)',
                ],
                [
                    'empleado' => 'luis.benitez@ejemplo.test',
                    'nombre' => 'Revisión del horno',
                    'fecha' => '2026-10-15',
                    'hora' => '11:00:00',
                    'estado' => 'Completada',
                    'descripcion' => 'Se comprobó el funcionamiento de los quemadores y se registró la temperatura alcanzada durante el turno. (ejemplo)',
                ],
                [
                    'empleado' => 'juan.perez@ejemplo.test',
                    'nombre' => 'Preparación de lote para secado',
                    'fecha' => '2026-10-16',
                    'hora' => '07:15:00',
                    'estado' => 'Pendiente',
                    'descripcion' => 'Trasladar la hoja recibida a la zona de carga y confirmar la identificación del lote antes del proceso. (ejemplo)',
                ],
                [
                    'empleado' => 'carlos.gomez@ejemplo.test',
                    'nombre' => 'Control del flujo de aire',
                    'fecha' => '2026-10-16',
                    'hora' => '12:00:00',
                    'estado' => 'Completada',
                    'descripcion' => 'Se revisó la circulación de aire caliente en las cámaras y se confirmó que no hubiera obstrucciones. (ejemplo)',
                ],
                [
                    'empleado' => 'marcelo.duarte@ejemplo.test',
                    'nombre' => 'Separación de hoja y palo',
                    'fecha' => '2026-10-17',
                    'hora' => '08:30:00',
                    'estado' => 'Pendiente',
                    'descripcion' => 'Separar el palo grueso de la hoja seca y apartar el material fuera de especificación para su revisión. (ejemplo)',
                ],
                [
                    'empleado' => 'luis.benitez@ejemplo.test',
                    'nombre' => 'Limpieza del área de secadero',
                    'fecha' => '2026-10-17',
                    'hora' => '10:30:00',
                    'estado' => 'Completada',
                    'descripcion' => 'Se barrió el sector de descarga y se retiró el polvo acumulado junto a la cinta transportadora. (ejemplo)',
                ],
            ];

            foreach ($tareas as $tarea) {
                $fechaAsignacion = $tarea['fecha'].' '.$tarea['hora'];

                $existe = DB::table('tareas')
                    ->where('descripcion', $tarea['descripcion'])
                    ->exists();

                if ($existe) {
                    continue;
                }

                DB::table('tareas')->insert([
                    'nombre' => $tarea['nombre'],
                    'id_operario' => $idsEmpleados[$tarea['empleado']],
                    'id_asignador' => $idAsignador,
                    'fecha_asignacion' => $fechaAsignacion,
                    'estado' => $tarea['estado'],
                    'descripcion' => $tarea['descripcion'],
                    'created_at' => $fechaAsignacion,
                    'updated_at' => $fechaAsignacion,
                ]);
            }

            $turnos = [
                ['empleado' => 'juan.perez@ejemplo.test', 'fecha' => '2026-10-12', 'turno' => 'mañana'],
                ['empleado' => 'carlos.gomez@ejemplo.test', 'fecha' => '2026-10-13', 'turno' => 'tarde'],
                ['empleado' => 'marcelo.duarte@ejemplo.test', 'fecha' => '2026-10-14', 'turno' => 'noche'],
                ['empleado' => 'luis.benitez@ejemplo.test', 'fecha' => '2026-10-15', 'turno' => 'mañana'],
                ['empleado' => 'juan.perez@ejemplo.test', 'fecha' => '2026-10-16', 'turno' => 'tarde'],
                ['empleado' => 'carlos.gomez@ejemplo.test', 'fecha' => '2026-10-17', 'turno' => 'noche'],
                ['empleado' => 'marcelo.duarte@ejemplo.test', 'fecha' => '2026-10-12', 'turno' => 'tarde'],
                ['empleado' => 'luis.benitez@ejemplo.test', 'fecha' => '2026-10-14', 'turno' => 'mañana'],
            ];

            foreach ($turnos as $turno) {
                $idEmpleado = $idsEmpleados[$turno['empleado']];

                $existe = DB::table('turnos')
                    ->where('user_id', $idEmpleado)
                    ->where('fecha', $turno['fecha'])
                    ->exists();

                if ($existe) {
                    continue;
                }

                DB::table('turnos')->insert([
                    'user_id' => $idEmpleado,
                    'fecha' => $turno['fecha'],
                    'turno' => $turno['turno'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
