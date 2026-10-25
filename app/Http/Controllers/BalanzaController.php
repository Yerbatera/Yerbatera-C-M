<?php

namespace App\Http\Controllers;

use App\Models\Balanza;
use App\Models\Contacto;
use App\Models\Lote;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BalanzaController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('balanza.create');
    }

    public function create(): View
    {
        $idsUsuariosSistema = DB::table('users')
            ->whereNotNull('id_contacto')
            ->pluck('id_contacto');

        $proveedores = Contacto::query()
            ->leftJoin('contactoxidentificador', 'contactos.id', '=', 'contactoxidentificador.id_contacto')
            ->whereNotIn('contactos.id', $idsUsuariosSistema)
            ->select('contactos.*', 'contactoxidentificador.valor as dni')
            ->orderBy('contactos.nombre')
            ->get();

        $registros = Balanza::with(['proveedor', 'operario'])
            ->orderByDesc('fecha_hora')
            ->limit(10)
            ->get();

        return view('balanza.create', compact('proveedores', 'registros'));
    }

    public function storeProveedor(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'telefono' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
        ], [
            'dni.regex' => 'El DNI / CUIT solo debe contener números.',
            'telefono.regex' => 'El teléfono solo debe contener números.',
        ]);

        $ahora = Carbon::now();

        $proveedor = DB::transaction(function () use ($validated, $ahora): Contacto {
            $contacto = Contacto::create([
                'nombre' => trim($validated['nombre']),
                'telefono' => ! empty($validated['telefono']) ? trim($validated['telefono']) : null,
            ]);

            $idRolYerbatero = DB::table('roles')->where('nombre', 'yerbatero')->value('id');
            if ($idRolYerbatero) {
                DB::table('contactoxrol')->insert([
                    'id_rol' => $idRolYerbatero,
                    'id_contacto' => $contacto->id,
                    'nombre' => 'yerbatero',
                ]);
            }

            if (! empty($validated['dni'])) {
                $idIdentificadorDni = DB::table('identificadores')->where('nombre', 'DNI')->value('id');
                if (! $idIdentificadorDni) {
                    $idIdentificadorDni = DB::table('identificadores')->insertGetId([
                        'nombre' => 'DNI',
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                }

                DB::table('contactoxidentificador')->insert([
                    'id_identificador' => $idIdentificadorDni,
                    'id_contacto' => $contacto->id,
                    'valor' => trim($validated['dni']),
                ]);
            }

            return $contacto;
        });

        $dni = ! empty($validated['dni']) ? trim($validated['dni']) : null;
        $label = $proveedor->nombre;
        if ($dni) {
            $label .= " (DNI: {$dni})";
        }
        if ($proveedor->telefono) {
            $label .= " · Tel: {$proveedor->telefono}";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $proveedor->id,
                'nombre' => $proveedor->nombre,
                'dni' => $dni,
                'telefono' => $proveedor->telefono,
                'label' => $label,
                'message' => 'Proveedor guardado.',
            ]);
        }

        return redirect()
            ->route('balanza.create')
            ->with('success', 'Proveedor guardado.')
            ->with('nuevo_proveedor_id', $proveedor->id);
    }

    public function updateProveedor(Request $request, Contacto $proveedor): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'dni' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            'telefono' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
        ], [
            'dni.regex' => 'El DNI / CUIT solo debe contener números.',
            'telefono.regex' => 'El teléfono solo debe contener números.',
        ]);

        $ahora = Carbon::now();
        $dni = ! empty($validated['dni']) ? trim($validated['dni']) : null;

        DB::transaction(function () use ($proveedor, $validated, $dni, $ahora): void {
            $proveedor->update([
                'nombre' => trim($validated['nombre']),
                'telefono' => ! empty($validated['telefono']) ? trim($validated['telefono']) : null,
            ]);

            $idIdentificadorDni = DB::table('identificadores')->where('nombre', 'DNI')->value('id');
            if (! $idIdentificadorDni) {
                $idIdentificadorDni = DB::table('identificadores')->insertGetId([
                    'nombre' => 'DNI',
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }

            if ($dni) {
                $existeDni = DB::table('contactoxidentificador')
                    ->where('id_contacto', $proveedor->id)
                    ->where('id_identificador', $idIdentificadorDni)
                    ->exists();

                if ($existeDni) {
                    DB::table('contactoxidentificador')
                        ->where('id_contacto', $proveedor->id)
                        ->where('id_identificador', $idIdentificadorDni)
                        ->update(['valor' => $dni]);
                } else {
                    DB::table('contactoxidentificador')->insert([
                        'id_identificador' => $idIdentificadorDni,
                        'id_contacto' => $proveedor->id,
                        'valor' => $dni,
                    ]);
                }
            } else {
                DB::table('contactoxidentificador')
                    ->where('id_contacto', $proveedor->id)
                    ->where('id_identificador', $idIdentificadorDni)
                    ->delete();
            }
        });

        $label = $proveedor->nombre;
        if ($dni) {
            $label .= " (DNI: {$dni})";
        }
        if ($proveedor->telefono) {
            $label .= " · Tel: {$proveedor->telefono}";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $proveedor->id,
                'nombre' => $proveedor->nombre,
                'dni' => $dni,
                'telefono' => $proveedor->telefono,
                'label' => $label,
                'message' => 'Proveedor actualizado.',
            ]);
        }

        return redirect()
            ->route('balanza.create')
            ->with('success', 'Proveedor actualizado.');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'id_proveedor' => ['required', 'exists:contactos,id'],
            'nro_ticket' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9\-_\/ ]+$/'],
            'peso_bruto' => ['required', 'numeric', 'min:0.01'],
            'tara' => ['required', 'numeric', 'min:0', 'lte:peso_bruto'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'importe_total' => ['nullable', 'numeric', 'min:0'],
            'concepto_descuento' => ['nullable', 'string', 'max:255'],
        ], [
            'id_proveedor.required' => 'Seleccioná un proveedor de la lista.',
            'tara.lte' => 'La tara no puede superar al peso bruto.',
            'nro_ticket.regex' => 'El número de ticket solo puede contener letras, números, espacios o guiones.',
        ]);

        try {
            $ahora = Carbon::now();
            $fechaComprobante = $ahora->format('Ymd');

            do {
                $codigoAleatorio = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
                $comprobante = "RMT-{$fechaComprobante}-{$codigoAleatorio}";
            } while (Balanza::where('remito', $comprobante)->exists());

            $pesoBruto = (float) $validated['peso_bruto'];
            $tara = (float) $validated['tara'];
            $pesoNeto = max(0, round($pesoBruto - $tara, 2));

            $registro = DB::transaction(function () use ($validated, $ahora, $comprobante, $pesoBruto, $tara, $pesoNeto): Balanza {
                $idMateriaPrima = DB::table('materia_primas')->value('id');
                if (! $idMateriaPrima) {
                    $idMateriaPrima = DB::table('materia_primas')->insertGetId([
                        'nombre' => 'Hoja Verde',
                        'tipo' => 'Tradicional',
                        'precio' => 0,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                }

                $lote = Lote::first();
                if (! $lote) {
                    $lote = new Lote;
                    $lote->estado = 'En secadero';
                    $lote->peso = 0;
                    $lote->tipo = $idMateriaPrima;
                    $lote->save();
                }

                $lote->peso = (float) $lote->peso + $pesoNeto;
                $lote->save();

                $nuevoRegistro = new Balanza;
                $nuevoRegistro->fecha_hora = $ahora;
                $nuevoRegistro->id_proveedor = (int) $validated['id_proveedor'];
                $nuevoRegistro->id_operario = (int) Auth::id();
                $nuevoRegistro->id_materia = $lote->id;
                $nuevoRegistro->nro_ticket = trim($validated['nro_ticket']);
                $nuevoRegistro->remito = $comprobante;
                $nuevoRegistro->peso_bruto = $pesoBruto;
                $nuevoRegistro->peso_tara = $tara;
                $nuevoRegistro->descuento = (float) ($validated['descuento'] ?? 0);
                $nuevoRegistro->peso_neto = $pesoNeto;
                $nuevoRegistro->importe_total = (float) ($validated['importe_total'] ?? 0);
                $nuevoRegistro->concepto_descuento = $validated['concepto_descuento'] ?? null;
                $nuevoRegistro->save();

                return $nuevoRegistro->load(['proveedor', 'operario']);
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pesaje registrado.',
                    'registro' => [
                        'remito' => $registro->remito,
                        'nro_ticket' => $registro->nro_ticket,
                        'fecha_hora' => $registro->fecha_hora?->format('d/m/Y H:i:s'),
                        'proveedor' => $registro->proveedor?->nombre ?? '—',
                        'operario' => $registro->operario?->name ?? '—',
                        'peso_bruto' => number_format((float) $registro->peso_bruto, 2, ',', '.'),
                        'peso_tara' => number_format((float) $registro->peso_tara, 2, ',', '.'),
                        'peso_neto' => number_format((float) $registro->peso_neto, 2, ',', '.'),
                    ],
                ]);
            }

            return redirect()
                ->route('balanza.create')
                ->with('success', 'Pesaje registrado.');
        } catch (\Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No se pudo registrar el pesaje en el servidor.',
                ], 500);
            }

            return redirect()
                ->route('balanza.create')
                ->with('error', 'No se pudo registrar el pesaje en el servidor.');
        }
    }
}
