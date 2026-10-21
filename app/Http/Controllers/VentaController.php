<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Lote;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(): View
    {
        $ventas = Venta::with(['cliente', 'lote'])
            ->orderByDesc('fecha')
            ->paginate(15);

        return view('ventas.index', compact('ventas'));
    }

    public function create(): View
    {
        $clientes = Contacto::all();
        $productos = Lote::where('peso', '>', 0)->get();

        return view('ventas.create', compact('clientes', 'productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:contactos,id',
            'producto_id' => 'required|exists:lotes,id',
            'cantidad_kg' => 'required|numeric|min:0.1',
            'precio_kilo' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated): void {
                $producto = Lote::findOrFail($validated['producto_id']);

                if ($producto->peso < $validated['cantidad_kg']) {
                    throw new \Exception('No hay stock (peso) suficiente en este lote.');
                }

                $venta = new Venta;
                $venta->fecha = Carbon::now();
                $venta->id_cliente = $validated['cliente_id'];
                $venta->id_lote = $validated['producto_id'];
                $venta->cantidad = $validated['cantidad_kg'];
                $venta->precio_kg = $validated['precio_kilo'];
                $venta->total_venta = $validated['cantidad_kg'] * $validated['precio_kilo'];
                $venta->punto_venta = 'Casa Central';
                $venta->save();

                $producto->peso -= $validated['cantidad_kg'];
                $producto->save();
            });

            return redirect()->back()->with('success', '¡Venta registrada con éxito y stock descontado!');
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }
}