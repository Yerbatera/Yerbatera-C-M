<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta</title>
</head>
<body>
    <h2>Registrar nueva salida comercial</h2>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <form action="{{ route('ventas.store') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 15px;">
            <label for="cliente_id">Cliente (Distribuidora):</label><br>
            <select name="cliente_id" id="cliente_id" required>
                <option value="" disabled selected>Seleccione un cliente...</option>
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="producto_id">Lote de origen:</label><br>
            <select name="producto_id" id="producto_id" required>
                <option value="" disabled selected>Seleccione un lote...</option>
                @foreach ($productos as $producto)
                    <option value="{{ $producto->id }}">
                        Lote #{{ $producto->id }} (Stock: {{ $producto->peso }} kg)
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="cantidad_kg">Cantidad a vender (KG):</label><br>
            <input type="number" name="cantidad_kg" id="cantidad_kg" step="0.1" min="0.1" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="precio_kilo">Precio por KG ($):</label><br>
            <input type="number" name="precio_kilo" id="precio_kilo" step="0.01" min="0" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="monto_total">Monto Total Calculado ($):</label><br>
            <input type="text" id="monto_total" readonly placeholder="0.00" style="background-color: #f3f4f6;">
        </div>

        <button type="submit">Confirmar Venta</button>
    </form>

    <script>
        const inputCantidad = document.getElementById('cantidad_kg');
        const inputPrecio = document.getElementById('precio_kilo');
        const inputTotal = document.getElementById('monto_total');

        function calcularTotal() {
            const cantidad = parseFloat(inputCantidad.value) || 0;
            const precioKilo = parseFloat(inputPrecio.value) || 0;
            inputTotal.value = (cantidad * precioKilo).toFixed(2);
        }

        inputCantidad.addEventListener('input', calcularTotal);
        inputPrecio.addEventListener('input', calcularTotal);
    </script>
</body>
</html>