@extends('layouts.app')

@section('contenido')
    <div class="mb-6">
        <div>
            <h1 class="text-3xl font-bold text-green-900">Historial de ventas</h1>
            <p class="mt-1 text-gray-600">Consulta de ventas realizadas.</p>
        </div>
    </div>

    @if (session('success'))
        <p role="status" class="mb-4 text-green-700">{{ session('success') }}</p>
    @endif

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Venta</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Fecha</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Cliente</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Lote</th>
                    <th scope="col" class="px-4 py-3 text-right text-sm font-semibold">Cantidad (kg)</th>
                    <th scope="col" class="px-4 py-3 text-right text-sm font-semibold">Precio/kg</th>
                    <th scope="col" class="px-4 py-3 text-right text-sm font-semibold">Total</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Punto de venta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($ventas as $venta)
                    <tr>
                        <td class="px-4 py-3">#{{ $venta->id }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $venta->fecha }}</td>
                        <td class="px-4 py-3">{{ $venta->cliente->nombre }}</td>
                        <td class="px-4 py-3">#{{ $venta->lote->id }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format((float) $venta->cantidad, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format((float) $venta->precio_kg, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format((float) $venta->total_venta, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $venta->punto_venta }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-600">Todavía no hay ventas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ventas->links() }}
    </div>
@endsection
