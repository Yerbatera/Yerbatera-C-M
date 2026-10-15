<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use Illuminate\View\View;

class GerenciaController extends Controller
{
    public function procesos(): View
    {
        $procesos = Proceso::with(['operario', 'lote'])
            ->orderByDesc('fecha')
            ->paginate(15);

        return view('gerencia.procesos', compact('procesos'));
    }
}
