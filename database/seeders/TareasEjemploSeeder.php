<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TareasEjemploSeeder extends Seeder
{
    public function run(): void
    {
        $operario = DB::table('users')->where('rol', 'operario')->orderBy('id')->first();
        $gerente = DB::table('users')->where('rol', 'gerente')->orderBy('id')->first();

        if ($operario === null || $gerente === null) {
            throw new RuntimeException('Se necesita un usuario existente con rol gerente y otro con rol operario.');
        }

        $tareas = [
            [
                'nombre' => 'Secado de yerba',
                'descripcion' => 'Completar el secado de la tanda asignada en el secadero principal.',
                'estado' => 'Pendiente',
                'dias' => 0,
                'hora' => 8,
            ],
            [
                'nombre' => 'Control de humedad',
                'descripcion' => 'Verificar y registrar la humedad de la yerba estacionada.',
                'estado' => 'Completada',
                'dias' => 1,
                'hora' => 7,
            ],
            [
                'nombre' => 'Empaquetado',
                'descripcion' => 'Preparar paquetes de yerba de un kilogramo para despacho.',
                'estado' => 'Pendiente',
                'dias' => 1,
                'hora' => 10,
            ],
            [
                'nombre' => 'Limpieza de zaranda',
                'descripcion' => 'Retirar residuos vegetales y limpiar la zaranda de clasificación.',
                'estado' => 'Completada',
                'dias' => 2,
                'hora' => 6,
            ],
            [
                'nombre' => 'Carga de camión',
                'descripcion' => 'Organizar y cargar los bultos correspondientes al pedido del día.',
                'estado' => 'Pendiente',
                'dias' => 2,
                'hora' => 9,
            ],
            [
                'nombre' => 'Control de temperatura del secadero',
                'descripcion' => 'Revisar el registro de temperatura durante el ciclo de secado.',
                'estado' => 'Completada',
                'dias' => 3,
                'hora' => 8,
            ],
            [
                'nombre' => 'Clasificación de hoja',
                'descripcion' => 'Separar hojas y palos antes de enviar el lote a molienda.',
                'estado' => 'Pendiente',
                'dias' => 4,
                'hora' => 7,
            ],
            [
                'nombre' => 'Mantenimiento de cinta transportadora',
                'descripcion' => 'Inspeccionar la cinta y ajustar sus guías laterales.',
                'estado' => 'Completada',
                'dias' => 5,
                'hora' => 11,
            ],
            [
                'nombre' => 'Preparación de lote para molienda',
                'descripcion' => 'Trasladar y acondicionar el lote aprobado para molienda.',
                'estado' => 'Pendiente',
                'dias' => 6,
                'hora' => 8,
            ],
        ];

        $ahora = now();

        foreach ($tareas as $tarea) {
            $fechaAsignacion = $ahora->copy()->subDays($tarea['dias'])->setTime($tarea['hora'], 0);
            $fechaActualizacion = $tarea['estado'] === 'Completada'
                ? $fechaAsignacion->copy()->addHours(3)
                : $fechaAsignacion->copy()->addMinutes(30);

            DB::table('tareas')->updateOrInsert(
                [
                    'nombre' => $tarea['nombre'],
                    'descripcion' => $tarea['descripcion'],
                    'id_operario' => $operario->id,
                ],
                [
                    'id_asignador' => $gerente->id,
                    'fecha_asignacion' => $fechaAsignacion,
                    'estado' => $tarea['estado'],
                    'created_at' => $fechaAsignacion,
                    'updated_at' => $fechaActualizacion,
                ]
            );
        }
    }
}
