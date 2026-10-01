<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuariosBaseSeeder extends Seeder
{
    public function run()
    {
        $roles = ['gerente', 'administracion', 'operario', 'yerbatero'];

        foreach ($roles as $rol) {
            User::create([
                'name' => ucfirst($rol),
                'email' => $rol . '@yerbatera.com',
                'password' => Hash::make('123456'), 
                'rol' => $rol
            ]);
        }
    }
}