<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProducerController extends Controller
{
    public function search(Request $request)
    {
        $deliveries = null;

        if ($request->filled('dni')) {
            $request->validate([
                'dni' => 'required|numeric'
            ]);

            $dni = trim($request->dni);

            // Consulta uniendo identificadores con los registros de balanza del productor
            $deliveries = DB::table('contactoxidentificador')
                ->join('contactos', 'contactos.id', '=', 'contactoxidentificador.id_contacto')
                ->join('registro_balanzas', 'registro_balanzas.id_proveedor', '=', 'contactos.id')
                ->where('contactoxidentificador.valor', $dni)
                ->select(
                    'registro_balanzas.id as numero_ticket',
                    'registro_balanzas.fecha_hora as fecha',
                    'registro_balanzas.peso_neto as kilos_netos'
                )
                ->orderBy('registro_balanzas.fecha_hora', 'desc')
                ->get();
        }

        return view('producer.search', compact('deliveries'));
    }
}