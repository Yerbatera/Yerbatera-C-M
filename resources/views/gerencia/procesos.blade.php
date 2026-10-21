@extends('layouts.app')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-green-900">Registros de control productivo</h1>
        <p class="mt-1 text-gray-600">Consulta de procesos registrados por lote y operario.</p>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Registro</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Fecha</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Proceso</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Lote</th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-semibold">Operario</th>
                    <th scope="col" class="px-4 py-3 text-right text-sm font-semibold">Temperatura</th>
                    <th scope="col" class="px-4 py-3 text-right text-sm font-semibold">Vueltas de torax</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($procesos as $proceso)
                    <tr>
                        <td class="px-4 py-3">#{{ $proceso->id }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $proceso->fecha }}</td>
                        <td class="px-4 py-3">{{ $proceso->tipo_proceso }}</td>
                        <td class="px-4 py-3">#{{ $proceso->lote->id }}</td>
                        <td class="px-4 py-3">{{ $proceso->operario->name }}</td>
                        <td class="px-4 py-3 text-right">{{ $proceso->temperatura ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $proceso->vuelta_torax ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-600">Todavía no hay registros de control productivo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $procesos->links() }}
    </div>
@endsection
