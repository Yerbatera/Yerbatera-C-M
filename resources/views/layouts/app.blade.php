<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yerbatera C&M</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #e5e7eb; background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); }
    </style>
</head>
<body class="min-h-screen flex flex-col text-gray-800">
    
    <nav class="bg-green-800 text-white p-4 shadow-md flex justify-between items-center">
        <div class="font-bold text-xl">Yerbatera C&M</div>
        <div class="flex items-center gap-4">
            @auth
                <span>Usuario: <b class="capitalize">{{ Auth::user()->name }}</b></span>
                @unless (request()->is('gerente', 'administracion', 'operario', 'yerbatero'))
                    <a href="{{ url('/' . Auth::user()->rol) }}" class="bg-white/10 hover:bg-white/20 px-3 py-1 rounded text-sm font-semibold transition">Volver al Menú</a>
                @endunless
                <a href="/" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm font-semibold transition">Salir</a>
            @else
                <a href="/" class="font-semibold hover:underline">Ingresar</a>
            @endauth
        </div>
    </nav>

    <main class="flex-1 flex flex-col max-w-6xl w-full mx-auto px-6 pt-5 pb-8">
        @yield('contenido')
    </main>

    <footer class="mt-auto bg-green-700 text-green-50 border-t border-green-600 px-6 py-3.5 shadow-inner">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-xs font-medium">
            <span>Yerbatera C&M · Sistema de Gestión y Control Productivo</span>
            <span class="text-green-100/90">Establecimiento Secadero · {{ date('Y') }}</span>
        </div>
    </footer>

</body>
</html>
