@extends('layouts.app')

@section('contenido')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-2xl shadow-md border border-gray-100">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Registro de Balanza (Cupones y Remitos)</h2>

    @if (session('success'))
        <div class="p-4 mb-6 text-sm text-green-800 bg-green-100 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('balanza.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Seleccionar Proveedor --}}
            <div>
                <label for="contacto_id" class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                <select name="contacto_id" id="contacto_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 p-2.5 border">
                    <option value="">-- Seleccionar Proveedor --</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->id }}">
                            {{ $provider->nombre }} 
                            @if(!empty($provider->identificador))
                                (DNI/ID: {{ $provider->identificador }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Seleccionar Materia Prima --}}
            <div>
                <label for="id_materia" class="block text-sm font-medium text-gray-700 mb-1">Materia Prima</label>
                <select name="id_materia" id="id_materia" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 p-2.5 border">
                    <option value="">-- Seleccionar Materia Prima --</option>
                    @foreach ($materiasPrimas as $materia)
                        <option value="{{ $materia->id }}">
                            {{ $materia->nombre ?? $materia->materia_prima ?? 'Materia Prima #'.$materia->id }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Peso Bruto --}}
            <div>
                <label for="gross_weight" class="block text-sm font-medium text-gray-700 mb-1">Peso Bruto (kg)</label>
                <input type="number" step="0.01" name="gross_weight" id="gross_weight" required
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 p-2.5 border"
                       placeholder="0.00">
            </div>

            {{-- Tara --}}
            <div>
                <label for="tare_weight" class="block text-sm font-medium text-gray-700 mb-1">Tara (kg)</label>
                <input type="number" step="0.01" name="tare_weight" id="tare_weight" required
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 p-2.5 border"
                       placeholder="0.00">
            </div>

            {{-- Peso Neto --}}
            <div>
                <label for="net_weight" class="block text-sm font-medium text-gray-700 mb-1">Peso Neto (kg)</label>
                <input type="number" step="0.01" id="net_weight" readonly
                       class="w-full bg-gray-100 border-gray-300 rounded-lg shadow-sm font-bold text-green-700 p-2.5 border cursor-not-allowed"
                       placeholder="0.00">
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-green-700 hover:bg-green-800 text-white font-semibold py-2.5 px-6 rounded-xl transition-all shadow-sm">
                Guardar e Imprimir Ticket
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const grossInput = document.getElementById('gross_weight');
        const tareInput = document.getElementById('tare_weight');
        const netInput = document.getElementById('net_weight');

        function calculateNetWeight() {
            const gross = parseFloat(grossInput.value) || 0;
            const tare = parseFloat(tareInput.value) || 0;
            const net = gross - tare;

            netInput.value = net >= 0 ? net.toFixed(2) : '0.00';
        }

        grossInput.addEventListener('input', calculateNetWeight);
        tareInput.addEventListener('input', calculateNetWeight);
    });
</script>
@endsection