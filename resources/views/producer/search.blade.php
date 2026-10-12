
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yerbatera C&M - Consulta de Entregas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        
        <!-- Header Aislado -->
        <div class="bg-emerald-800 text-white px-6 py-5">
            <h1 class="text-2xl font-bold">Yerbatera C&M</h1>
            <p class="text-emerald-100 text-sm mt-1">Consulta pública de entregas para productores</p>
        </div>

        <div class="p-6">
            <!-- Formulario por DNI -->
            <form action="{{ route('producer.search') }}" method="GET" class="mb-6">
                <label for="dni" class="block text-sm font-semibold text-gray-700 mb-2">
                    Ingrese su número de DNI
                </label>
                <div class="flex gap-3">
                    <input 
                        type="text" 
                        name="dni" 
                        id="dni"
                        value="{{ request('dni') }}" 
                        placeholder="Ej: 30123456" 
                        required 
                        class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-600 focus:border-emerald-600 outline-none text-gray-800"
                    >
                    <button 
                        type="submit" 
                        class="bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-6 py-2.5 rounded-lg transition-colors shadow-sm"
                    >
                        Buscar
                    </button>
                </div>
            </form>

            <!-- Resultados -->
            @if(isset($deliveries))
                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Historial de Entregas</h2>

                    @if($deliveries->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                                        <th class="p-3">N° Ticket</th>
                                        <th class="p-3">Fecha</th>
                                        <th class="p-3 text-right">Kilos Netos</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @php $totalKilos = 0; @endphp
                                    @foreach($deliveries as $item)
                                        @php $totalKilos += $item->kilos_netos; @endphp
                                        <tr class="hover:bg-gray-50 text-gray-700">
                                            <td class="p-3 font-semibold text-emerald-800">#{{ $item->numero_ticket }}</td>
                                            <td class="p-3">{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y H:i') }}</td>
                                            <td class="p-3 text-right font-medium">{{ number_format($item->kilos_netos, 2, ',', '.') }} kg</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-emerald-50 text-emerald-900 font-bold border-t border-emerald-200">
                                        <td colspan="2" class="p-3">Total Acumulado</td>
                                        <td class="p-3 text-right">{{ number_format($totalKilos, 2, ',', '.') }} kg</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="p-4 bg-gray-50 text-gray-500 rounded-lg text-center text-sm">
                            No se encontraron entregas registradas para el DNI ingresado.
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

</body>
</html>