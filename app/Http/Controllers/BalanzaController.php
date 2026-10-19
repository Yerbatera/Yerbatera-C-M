<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BalanzaController extends Controller
{
    /**
     * Muestra el formulario para registrar pesaje en balanza
     */
    public function create()
    {
        // Obtener proveedores
        $providers = DB::table('contactos')
            ->leftJoin('contactoxidentificador', 'contactos.id', '=', 'contactoxidentificador.id_contacto')
            ->select(
                'contactos.id',
                'contactos.nombre',
                'contactoxidentificador.valor as identificador'
            )
            ->get();

        // Obtener materias primas de la base de datos
        $materiasPrimas = DB::table('materia_primas')->get();

        return view('balanza.create', compact('providers', 'materiasPrimas'));
    }

    /**
     * Guarda el registro de pesaje en la tabla registro_balanzas
     */
    public function store(Request $request)
    {
        $request->validate([
            'contacto_id'  => 'required|integer',
            'id_materia'   => 'required|integer',
            'gross_weight' => 'required|numeric|min:0',
            'tare_weight'  => 'required|numeric|min:0|lte:gross_weight',
        ]);

        $grossWeight = $request->input('gross_weight');
        $tareWeight  = $request->input('tare_weight');
        $netWeight   = $grossWeight - $tareWeight;

        // Generar código de remito único
        $ticketCode = 'RMT-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        // Insertar registro en la base de datos
        DB::table('registro_balanzas')->insert([
            'fecha_hora'    => now(),
            'id_proveedor'  => $request->input('contacto_id'),
            'id_operario'   => auth()->id() ?? 2, // Se envía el usuario autenticado o ID existente
            'id_materia'    => $request->input('id_materia'),
            'remito'        => $ticketCode,
            'peso_bruto'    => $grossWeight,
            'peso_tara'     => $tareWeight,
            'descuento'     => 0.00,
            'peso_neto'     => $netWeight,
            'importe_total' => 0.00,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->back()->with('success', "Remito registrado con éxito. Código: $ticketCode");
    }
}