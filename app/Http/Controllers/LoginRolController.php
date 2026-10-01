<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginRolController extends Controller
{
    public function loginRapido(Request $request)
    {
        // Busca al primer usuario que coincida con el rol del botón clickeado
        $user = User::where('rol', $request->rol)->first();

        if ($user) {
            Auth::login($user);
            
            // Redirige según el rol
            return match($request->rol) {
                'gerente' => redirect('/gerente'),
                'administracion' => redirect('/administracion'),
                'operario' => redirect('/operario'),
                'yerbatero' => redirect('/yerbatero'),
                default => redirect('/'),
            };
        }
        
        return back();
    }
}