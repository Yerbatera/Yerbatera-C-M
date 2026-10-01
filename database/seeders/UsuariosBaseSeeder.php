<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosBaseSeeder extends Seeder
{
    public function run()
    {
        $roles = ['gerente', 'administracion', 'operario', 'yerbatero'];

        foreach ($roles as $rol) {
            // 1. Crear el Rol
            $id_rol = DB::table('roles')->insertGetId([
                'nombre' => $rol,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. Crear el Contacto (Persona física)
            $id_contacto = DB::table('contactos')->insertGetId([
                'nombre' => 'Usuario ' . ucfirst($rol),
                'telefono' => '3764000000', // Teléfono de prueba
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 3. Vincular el Contacto con el Rol en la tabla intermedia
            DB::table('contactoxrol')->insert([
                'id_rol' => $id_rol,
                'id_contacto' => $id_contacto,
                'nombre' => $rol
            ]);

            // 4. Crear el Usuario para el sistema de Login
            DB::table('users')->insert([
                'id_contacto' => $id_contacto,
                'name' => ucfirst($rol),
                'email' => $rol . '@yerbatera.com',
                'password' => Hash::make('123456'),
                'rol' => $rol,
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}