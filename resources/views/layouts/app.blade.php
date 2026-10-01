<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yerbatera C&M</title>
    <!-- Importamos Tailwind CSS para el diseño -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Replicamos el fondo texturizado o color base de tus imágenes */
        body { background-color: #e5e7eb; background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); }
    </style>
</head>
<body class="min-h-screen text-gray-800">
    
    <!-- Barra de navegación superior compartida -->
    <nav class="bg-green-800 text-white p-4 shadow-md flex justify-between items-center">
        <div class="font-bold text-xl">Yerbatera C&M</div>
        <div class="flex items-center gap-4">
            <span>Usuario: <b class="capitalize">{{ Auth::user()->name }}</b> ({{ Auth::user()->rol }})</span>
            <a href="/" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm font-semibold transition">Salir</a>
        </div>
    </nav>

    <!-- Contenedor dinámico donde se inyectará el panel de cada rol -->
    <main class="max-w-6xl mx-auto p-6 mt-6">
        @yield('contenido')
    </main>

</body>
</html>