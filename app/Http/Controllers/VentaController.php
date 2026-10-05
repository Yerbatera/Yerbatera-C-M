<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\Contacto;
use App\Models\Lote;
use Carbon\Carbon; // Para manejar la fecha actual

class VentaController extends Controller
{
    public function create()
    {
        // Traemos los clientes y los lotes que tengan peso mayor a 0
        $clientes = Contacto::all(); 
        $productos = Lote::where('peso', '>', 0)->get(); 

        return view('ventas.create', compact('clientes', 'productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:contactos,id',
            'producto_id' => 'required|exists:lotes,id',
            'cantidad_kg' => 'required|numeric|min:0.1',
            'precio_kilo' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $producto = Lote::findOrFail($validated['producto_id']);

                if ($producto->peso < $validated['cantidad_kg']) {
                    throw new \Exception('No hay stock (peso) suficiente en este lote.');
                }

                // Guardar la venta usando exactamente los nombres de tu migración
                $venta = new Venta();
                $venta->fecha = Carbon::now(); // Fecha y hora actual
                $venta->id_cliente = $validated['cliente_id'];
                $venta->id_lote = $validated['producto_id']; // Apunta a id_lote
                $venta->cantidad = $validated['cantidad_kg']; // Se llama cantidad
                $venta->precio_kg = $validated['precio_kilo']; // Se llama precio_kg
                $venta->total_venta = $validated['cantidad_kg'] * $validated['precio_kilo']; // Se llama total_venta
                $venta->punto_venta = 'Casa Central'; // Valor por defecto obligatorio
                $venta->save();

                // Descontar stock general de la columna 'peso' en lotes
                $producto->peso -= $validated['cantidad_kg'];
                $producto->save();
            });

            return redirect()->back()->with('success', '¡Venta registrada con éxito y stock descontado!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}