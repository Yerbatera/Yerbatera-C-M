<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRol
{
    public function handle(Request $request, Closure $next, $rolRequerido)
    {
        if (!Auth::check() || Auth::user()->rol !== $rolRequerido) {
            abort(403, 'Acceso denegado. Tu rol no tiene permisos para ver esta pantalla.');
        }
        
        return $next($request);
    }
}