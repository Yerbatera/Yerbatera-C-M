<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EliminarDatosEjemploSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $idsEmpleados = DB::table('users')
                ->where('email', 'like', '%@ejemplo.test')
                ->pluck('id');

            DB::table('tareas')
                ->where('descripcion', 'like', '%(ejemplo)')
                ->delete();

            DB::table('turnos')
                ->whereIn('user_id', $idsEmpleados)
                ->delete();

            DB::table('users')
                ->whereIn('id', $idsEmpleados)
                ->delete();
        });
    }
}
