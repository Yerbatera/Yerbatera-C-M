<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if ($user === null || ! in_array($user->rol, $roles, true)) {
            abort(403, 'Acceso denegado. Tu rol no tiene permisos para ver esta pantalla.');
        }

        return $next($request);
    }
}
